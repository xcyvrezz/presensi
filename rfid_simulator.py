#!/usr/bin/env python3
"""
RFID Reader Simulator for Testing
Simulates physical RFID card reader sending card UID to API

Usage:
  python rfid_simulator.py

Then enter card UID when prompted
"""

import requests
import time
import sys

API_URL = "http://localhost/absensi-mifare/public/api/rfid/report-card"

def send_card_uid(card_uid):
    """Send card UID to API"""
    try:
        response = requests.post(API_URL, json={
            'card_uid': card_uid
        }, headers={
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        })

        if response.status_code == 200:
            data = response.json()
            print(f"✅ Card reported successfully: {data.get('card_uid')}")
            return True
        else:
            print(f"❌ Error: {response.status_code} - {response.text}")
            return False

    except Exception as e:
        print(f"❌ Connection error: {str(e)}")
        return False

def main():
    print("=" * 60)
    print("🔵 RFID Reader Simulator")
    print("=" * 60)
    print(f"API Endpoint: {API_URL}")
    print("\nInstructions:")
    print("  1. Make sure admin has started RFID Reader in admin panel")
    print("  2. Enter card UID (8-20 characters)")
    print("  3. Card will be automatically processed")
    print("\nPress Ctrl+C to exit\n")

    while True:
        try:
            # Prompt for card UID
            card_uid = input("\n💳 Enter Card UID (or 'q' to quit): ").strip()

            if card_uid.lower() == 'q':
                print("\n👋 Exiting...")
                break

            if len(card_uid) < 8:
                print("⚠️  Card UID must be at least 8 characters")
                continue

            # Send to API
            print(f"📡 Sending card UID: {card_uid}")
            send_card_uid(card_uid)

            # Small delay
            time.sleep(0.5)

        except KeyboardInterrupt:
            print("\n\n👋 Exiting...")
            break
        except Exception as e:
            print(f"❌ Error: {str(e)}")

if __name__ == "__main__":
    main()
