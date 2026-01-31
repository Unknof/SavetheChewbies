from __future__ import annotations

import json
import os
import uuid

import requests
from dotenv import load_dotenv


def main() -> int:
    load_dotenv(override=False)

    port = int(os.getenv("PORT", "8787"))
    token = os.getenv("KOFI_VERIFICATION_TOKEN", "")
    team_tag = os.getenv("TEAM_TAG", "[TEAM]")
    prize_tag = os.getenv("PRIZE_TAG", "[PRIZE]")

    if not token:
        raise SystemExit("KOFI_VERIFICATION_TOKEN is not set in .env")

    url = f"http://127.0.0.1:{port}/webhooks/kofi"

    base = {
        "verification_token": token,
        "type": "Donation",
        "from_name": "Local Test",
        "amount": "1",
        "currency": "USD",
        "kofi_transaction_id": str(uuid.uuid4()),
        "timestamp": "2026-01-31T00:00:00Z",
    }

    events = [
        {**base, "message_id": str(uuid.uuid4()), "message": f"{team_tag} testing team"},
        {**base, "message_id": str(uuid.uuid4()), "message": f"{prize_tag} testing prize"},
    ]

    print(f"Posting 2 test events to {url}")
    for idx, payload in enumerate(events, start=1):
        resp = requests.post(
            url,
            data={"data": json.dumps(payload)},
            headers={"Content-Type": "application/x-www-form-urlencoded"},
            timeout=10,
        )
        try:
            body = resp.json()
        except Exception:  # noqa: BLE001
            body = resp.text

        print(f"[{idx}] HTTP {resp.status_code}: {body}")

    print("\nNow check your Discord channel:")
    print("- You should see one message labeled TEAM FUND")
    print("- You should see one message labeled PRIZE POOL")

    return 0


if __name__ == "__main__":
    raise SystemExit(main())
