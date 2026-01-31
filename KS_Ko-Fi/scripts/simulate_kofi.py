from __future__ import annotations

import json
import sys
import uuid
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]
if str(ROOT) not in sys.path:
    sys.path.insert(0, str(ROOT))

from app import create_app, load_config


def main() -> int:
    cfg = load_config()

    if not cfg.kofi_verification_token:
        raise SystemExit("KOFI_VERIFICATION_TOKEN is not set in .env")

    if not cfg.discord_webhook_url:
        raise SystemExit("DISCORD_WEBHOOK_URL is not set in .env")

    app = create_app(cfg)
    client = app.test_client()

    base = {
        "verification_token": cfg.kofi_verification_token,
        "type": "Donation",
        "from_name": "Local Test",
        "amount": "1",
        "currency": "USD",
        "kofi_transaction_id": str(uuid.uuid4()),
        "timestamp": "2026-01-31T00:00:00Z",
    }

    events = [
        {**base, "message_id": str(uuid.uuid4()), "message": f"{cfg.team_tag} testing team"},
        {**base, "message_id": str(uuid.uuid4()), "message": f"{cfg.prize_tag} testing prize"},
    ]

    print("Simulating 2 Ko-fi webhook deliveries (TEAM + PRIZE)")
    for idx, payload in enumerate(events, start=1):
        resp = client.post(
            "/webhooks/kofi",
            data={"data": json.dumps(payload)},
            content_type="application/x-www-form-urlencoded",
        )
        print(f"[{idx}] HTTP {resp.status_code}: {resp.json}")

    print("\nCheck Discord for 2 new messages:")
    print("- one labeled TEAM FUND")
    print("- one labeled PRIZE POOL")

    return 0


if __name__ == "__main__":
    raise SystemExit(main())
