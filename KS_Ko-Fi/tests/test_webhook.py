import json

from app import Config, create_app


def test_accepts_valid_kofi_webhook():
    sent = []

    cfg = Config(
        port=8787,
        kofi_verification_token="token123",
        kofi_profile_url="https://ko-fi.com/example",
        discord_webhook_url=None,
        team_tag="[TEAM]",
        prize_tag="[PRIZE]",
    )

    app = create_app(cfg, send_discord=lambda content: sent.append(content))
    client = app.test_client()

    payload = {
        "verification_token": "token123",
        "type": "Donation",
        "from_name": "Tester",
        "message": "[TEAM] hello",
        "amount": "5",
        "currency": "USD",
        "message_id": "m1",
        "kofi_transaction_id": "t1",
        "timestamp": "2026-01-01T00:00:00Z",
    }

    resp = client.post(
        "/webhooks/kofi",
        data={"data": json.dumps(payload)},
        content_type="application/x-www-form-urlencoded",
    )

    assert resp.status_code == 200
    assert resp.json["ok"] is True
    assert len(sent) == 1
    assert "TEAM FUND" in sent[0]


def test_rejects_invalid_verification_token():
    cfg = Config(
        port=8787,
        kofi_verification_token="token123",
        kofi_profile_url=None,
        discord_webhook_url=None,
        team_tag="[TEAM]",
        prize_tag="[PRIZE]",
    )

    app = create_app(cfg, send_discord=lambda content: None)
    client = app.test_client()

    payload = {"verification_token": "wrong", "type": "Donation"}

    resp = client.post(
        "/webhooks/kofi",
        data={"data": json.dumps(payload)},
        content_type="application/x-www-form-urlencoded",
    )

    assert resp.status_code == 401
