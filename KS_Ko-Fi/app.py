from __future__ import annotations

import json
import os
from dataclasses import dataclass
from typing import Callable, Literal

import requests
from dotenv import load_dotenv
from flask import Flask, jsonify, request

Fund = Literal["team", "prize", "unknown"]


@dataclass(frozen=True)
class Config:
    port: int
    kofi_verification_token: str | None
    kofi_profile_url: str | None
    discord_webhook_url: str | None
    team_tag: str
    prize_tag: str
    enable_ui: bool = False
    require_verification_token: bool = True
    max_request_bytes: int = 128 * 1024


def _env(name: str, default: str | None = None) -> str | None:
    value = os.getenv(name)
    if value is None or value.strip() == "":
        return default
    return value


def _env_bool(name: str, default: bool) -> bool:
    value = _env(name)
    if value is None:
        return default
    normalized = value.strip().lower()
    if normalized in ("1", "true", "yes", "on"):
        return True
    if normalized in ("0", "false", "no", "off"):
        return False
    return default


def _env_int(name: str, default: int) -> int:
    value = _env(name)
    if value is None:
        return default
    try:
        return int(value)
    except ValueError:
        return default


def _detect_fund(message: str | None, team_tag: str, prize_tag: str) -> Fund:
    normalized = (message or "").lower()
    if team_tag.lower() in normalized:
        return "team"
    if prize_tag.lower() in normalized:
        return "prize"
    return "unknown"


def _format_discord_line(data: dict, fund: Fund) -> str:
    amount = data.get("amount", "?")
    currency = data.get("currency", "")
    from_name = data.get("from_name", "Someone")
    message = data.get("message")

    fund_label = {
        "team": "TEAM FUND",
        "prize": "PRIZE POOL",
        "unknown": "UNCLASSIFIED",
    }[fund]

    parts = [
        f"Ko-fi {data.get('type', 'Event')} → {fund_label}",
        f"From: {from_name}",
        f"Amount: {amount} {currency}".rstrip(),
    ]
    if message:
        parts.append(f"Message: {message}")
    return "\n".join(parts)


def _post_discord(webhook_url: str, content: str) -> None:
    resp = requests.post(
        webhook_url,
        json={"content": content, "allowed_mentions": {"parse": []}},
        timeout=10,
    )
    resp.raise_for_status()


def load_config() -> Config:
    load_dotenv(override=False)

    port = int(_env("PORT", "8787") or "8787")
    return Config(
        port=port,
        kofi_verification_token=_env("KOFI_VERIFICATION_TOKEN"),
        kofi_profile_url=_env("KOFI_PROFILE_URL"),
        discord_webhook_url=_env("DISCORD_WEBHOOK_URL"),
        team_tag=_env("TEAM_TAG", "[TEAM]") or "[TEAM]",
        prize_tag=_env("PRIZE_TAG", "[PRIZE]") or "[PRIZE]",
        enable_ui=_env_bool("ENABLE_UI", False),
        require_verification_token=_env_bool("REQUIRE_KOFI_TOKEN", True),
        max_request_bytes=_env_int("MAX_REQUEST_BYTES", 128 * 1024),
    )


def create_app(
    config: Config | None = None,
    send_discord: Callable[[str], None] | None = None,
) -> Flask:
    cfg = config or load_config()

    if cfg.require_verification_token and not cfg.kofi_verification_token:
        raise RuntimeError(
            "KOFI_VERIFICATION_TOKEN is required (or set REQUIRE_KOFI_TOKEN=0 to disable)"
        )

    app = Flask(__name__)

    seen_message_ids: set[str] = set()
    max_seen = 5000

    def _send(content: str) -> None:
        if send_discord is not None:
            send_discord(content)
            return
        if not cfg.discord_webhook_url:
            app.logger.info("[discord] DISCORD_WEBHOOK_URL not set; would send: %s", content)
            return
        _post_discord(cfg.discord_webhook_url, content)

    if cfg.enable_ui:

        @app.get("/")
        def index():
            return (
                "<!doctype html><html><head><meta charset='utf-8'/>"
                "<meta name='viewport' content='width=device-width, initial-scale=1'/>"
                "<title>KS Ko-fi Links</title>"
                "<style>body{font-family:system-ui,Segoe UI,Roboto,Arial,sans-serif;max-width:900px;"
                "margin:40px auto;padding:0 16px;line-height:1.4}"
                ".card{border:1px solid #ddd;border-radius:12px;padding:16px;margin:12px 0}"
                "a{color:#0b57d0}code{background:#f6f8fa;padding:2px 6px;border-radius:6px}</style>"
                "</head><body>"
                "<h1>KS Ko-fi Links</h1>"
                "<div class='card'><h2>Team fund link</h2><a href='/tip/team'>/tip/team</a></div>"
                "<div class='card'><h2>Prize pool link</h2><a href='/tip/prize'>/tip/prize</a></div>"
                "<div class='card'><h2>Webhook endpoint</h2><code>POST /webhooks/kofi</code></div>"
                "</body></html>",
                200,
                {"Content-Type": "text/html; charset=utf-8"},
            )

        @app.get("/tip/<fund>")
        def tip(fund: str):
            if fund not in ("team", "prize"):
                return ("Not found", 404)

            tag = cfg.team_tag if fund == "team" else cfg.prize_tag
            title = "Team Fund" if fund == "team" else "Tournament Prize Pool"
            profile = cfg.kofi_profile_url or "https://ko-fi.com/"

            # Reliable approach: copy the message, then open Ko-fi.
            return (
                f"<!doctype html><html><head><meta charset='utf-8'/>"
                f"<meta name='viewport' content='width=device-width, initial-scale=1'/>"
                f"<title>{title} - Ko-fi</title>"
                "<style>body{font-family:system-ui,Segoe UI,Roboto,Arial,sans-serif;max-width:900px;"
                "margin:40px auto;padding:0 16px;line-height:1.5}"
                ".card{border:1px solid #ddd;border-radius:12px;padding:16px;margin:12px 0}"
                "button{padding:10px 14px;border-radius:10px;border:1px solid #ccc;background:#fff;cursor:pointer}"
                "button:hover{background:#f6f6f6}.tag{font-weight:700}"
                "input{width:100%;padding:10px;border:1px solid #ddd;border-radius:10px;font-family:ui-monospace,Consolas,monospace}"
                "a{color:#0b57d0}</style></head><body>"
                f"<h1>{title}</h1>"
                "<div class='card'>"
                "<p>To help us track what this goes toward, please include this in your Ko-fi message:</p>"
                f"<p class='tag'>{tag}</p>"
                f"<input id='msg' readonly value='{tag}' />"
                "<p><button id='copy'>Copy message</button> "
                f"<a href='{profile}' target='_blank' rel='noreferrer'>Open Ko-fi</a></p>"
                "<p style='color:#555'>If Ko-fi adds true “prefill message” URL params later, we can swap this to one-click prefill."
                " This copy+open method is reliable today.</p>"
                "</div><div class='card'><p><a href='/'>Back</a></p></div>"
                "<script>"
                "const btn=document.getElementById('copy');"
                "btn.addEventListener('click',async()=>{const input=document.getElementById('msg');"
                "input.select();input.setSelectionRange(0,99999);"
                "try{await navigator.clipboard.writeText(input.value);}catch{document.execCommand('copy');}"
                "btn.textContent='Copied!';setTimeout(()=>btn.textContent='Copy message',1500);});"
                "</script></body></html>",
                200,
                {"Content-Type": "text/html; charset=utf-8"},
            )

    @app.post("/webhooks/kofi")
    def kofi_webhook():
        if request.content_length is not None and request.content_length > cfg.max_request_bytes:
            return jsonify({"ok": False, "error": "Request too large"}), 413

        raw = request.form.get("data")
        if not raw:
            return jsonify({"ok": False, "error": "Missing form field: data"}), 400

        if len(raw.encode("utf-8")) > cfg.max_request_bytes:
            return jsonify({"ok": False, "error": "Request too large"}), 413

        try:
            data = json.loads(raw)
        except json.JSONDecodeError:
            return jsonify({"ok": False, "error": "Invalid JSON in data"}), 400

        if cfg.kofi_verification_token and data.get("verification_token") != cfg.kofi_verification_token:
            return jsonify({"ok": False, "error": "Unauthorized"}), 401

        message_id = data.get("message_id")
        if isinstance(message_id, str) and message_id in seen_message_ids:
            return jsonify({"ok": True, "deduped": True}), 200

        fund = _detect_fund(data.get("message"), cfg.team_tag, cfg.prize_tag)
        content = _format_discord_line(data, fund)

        app.logger.info(
            "Ko-fi webhook received: type=%s message_id=%s fund=%s from=%s amount=%s %s",
            data.get("type"),
            data.get("message_id"),
            fund,
            data.get("from_name"),
            data.get("amount"),
            data.get("currency"),
        )

        try:
            _send(content)
        except Exception as exc:  # noqa: BLE001
            app.logger.exception("Discord forwarding failed")
            return jsonify({"ok": False, "error": "Discord forwarding failed"}), 502

        if isinstance(message_id, str):
            seen_message_ids.add(message_id)
            if len(seen_message_ids) > max_seen:
                seen_message_ids.clear()

        return jsonify({"ok": True}), 200

    @app.get("/health")
    def health():
        return jsonify({"ok": True})

    return app


if __name__ == "__main__":
    cfg = load_config()
    app = create_app(cfg)
    app.run(host="0.0.0.0", port=cfg.port)
