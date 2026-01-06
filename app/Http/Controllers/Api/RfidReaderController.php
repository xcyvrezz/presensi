<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class RfidReaderController extends Controller
{
    /**
     * Get the last detected card UID from RFID reader
     * This endpoint is polled by admin interface
     */
    public function getLastCard()
    {
        // Get last card from cache (set by physical RFID reader)
        $cardUid = Cache::get('rfid_last_card_uid');
        $timestamp = Cache::get('rfid_last_card_timestamp');

        // Check if card is recent (within last 3 seconds)
        if ($cardUid && $timestamp && (time() - $timestamp) < 3) {
            return response()->json([
                'success' => true,
                'card_uid' => $cardUid,
                'timestamp' => $timestamp
            ]);
        }

        return response()->json([
            'success' => true,
            'card_uid' => null,
            'timestamp' => null
        ]);
    }

    /**
     * Report detected card from physical RFID reader device
     * This is called by the physical RFID reader hardware
     */
    public function reportCard(Request $request)
    {
        $request->validate([
            'card_uid' => 'required|string|min:8|max:20'
        ]);

        $cardUid = strtoupper($request->card_uid);

        // Store in cache for 3 seconds
        Cache::put('rfid_last_card_uid', $cardUid, 3);
        Cache::put('rfid_last_card_timestamp', time(), 3);

        return response()->json([
            'success' => true,
            'message' => 'Card UID received',
            'card_uid' => $cardUid
        ]);
    }

    /**
     * Clear last card cache
     */
    public function clearLastCard()
    {
        Cache::forget('rfid_last_card_uid');
        Cache::forget('rfid_last_card_timestamp');

        return response()->json([
            'success' => true,
            'message' => 'Cache cleared'
        ]);
    }
}
