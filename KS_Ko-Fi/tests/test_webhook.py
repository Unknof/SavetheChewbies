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


# ------------------------------------------------------------------ #
# Afdian webhook tests                                                #
# ------------------------------------------------------------------ #

def _afdian_cfg(**kwargs):
    defaults = dict(
        port=8787,
        kofi_verification_token=None,
        kofi_profile_url=None,
        discord_webhook_url=None,
        team_tag="[TEAM]",
        prize_tag="[PRIZE]",
        require_verification_token=False,
    )
    defaults.update(kwargs)
    return Config(**defaults)


def _afdian_payload(out_trade_no="order1", status=2, remark="", total_amount="30.00"):
    return {
        "ec": 200,
        "em": "ok",
        "data": {
            "type": "order",
            "order": {
                "out_trade_no": out_trade_no,
                "plan_id": "plan1",
                "user_id": "user1",
                "user_private_id": "priv1",
                "plan_title": "Monthly Supporter",
                "title": "Monthly Supporter",
                "month": 1,
                "total_amount": total_amount,
                "show_amount": total_amount,
                "status": status,
                "remark": remark,
                "product_type": 0,
                "discount": "0.00",
                "sku_detail": [],
            },
        },
    }


def test_afdian_paid_order_forwarded_to_discord():
    sent = []
    app = create_app(_afdian_cfg(), send_discord=lambda c: sent.append(c))
    client = app.test_client()

    resp = client.post("/webhooks/afdian", json=_afdian_payload(remark="[TEAM] support"))

    assert resp.status_code == 200
    assert resp.json == {"ec": 200, "em": ""}
    assert len(sent) == 1
    assert "Afdian" in sent[0]
    assert "TEAM FUND" in sent[0]
    assert "¥30.00" in sent[0]


def test_afdian_prize_fund_detected():
    sent = []
    app = create_app(_afdian_cfg(), send_discord=lambda c: sent.append(c))
    client = app.test_client()

    resp = client.post("/webhooks/afdian", json=_afdian_payload(remark="[PRIZE] good luck"))

    assert resp.status_code == 200
    assert "PRIZE POOL" in sent[0]


def test_afdian_unpaid_order_ignored():
    sent = []
    app = create_app(_afdian_cfg(), send_discord=lambda c: sent.append(c))
    client = app.test_client()

    resp = client.post("/webhooks/afdian", json=_afdian_payload(status=0))

    assert resp.status_code == 200
    assert resp.json == {"ec": 200, "em": ""}
    assert len(sent) == 0


def test_afdian_deduplication():
    sent = []
    app = create_app(_afdian_cfg(), send_discord=lambda c: sent.append(c))
    client = app.test_client()

    payload = _afdian_payload(out_trade_no="dup1")

    resp1 = client.post("/webhooks/afdian", json=payload)
    resp2 = client.post("/webhooks/afdian", json=payload)

    assert resp1.status_code == 200
    assert resp2.status_code == 200
    assert len(sent) == 1  # second call is deduped


def test_afdian_missing_payload_returns_400():
    app = create_app(_afdian_cfg(), send_discord=lambda c: None)
    client = app.test_client()

    resp = client.post("/webhooks/afdian", data="not json", content_type="text/plain")

    assert resp.status_code == 400
