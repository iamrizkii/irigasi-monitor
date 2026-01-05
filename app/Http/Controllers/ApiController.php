<?php

namespace App\Http\Controllers;

use App\Models\SensorReading;
use App\Models\DeviceSetting;
use App\Models\Alert;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ApiController extends Controller
{
    /**
     * Receive sensor data from ESP32
     * POST /api/sensor-data
     */
    public function storeSensorData(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'petak1_moisture' => 'required|integer|min:0|max:100',
            'petak2_moisture' => 'required|integer|min:0|max:100',
            'petak3_moisture' => 'required|integer|min:0|max:100',
            'petak4_moisture' => 'required|integer|min:0|max:100',
            'water_main' => 'nullable|numeric',
            'water_mid' => 'nullable|numeric',
            'water_tank' => 'nullable|numeric',
            'pump_status' => 'required|boolean',
            'gate_main' => 'required|integer',
            'gate1' => 'required|integer',
            'gate2' => 'required|integer',
            'gate3' => 'required|integer',
            'gate4' => 'required|integer',
            'system_status' => 'required|string',
        ]);

        // Store sensor reading
        $reading = SensorReading::create($validated);

        // Check for drought alerts
        $this->checkDroughtAlerts($validated);

        return response()->json([
            'success' => true,
            'message' => 'Data received successfully',
            'id' => $reading->id,
        ]);
    }

    /**
     * Get device commands for ESP32
     * GET /api/device-status
     */
    public function getDeviceStatus(): JsonResponse
    {
        $settings = DeviceSetting::getSettings();

        return response()->json([
            'mode' => $settings->mode,
            'pump_command' => $settings->pump_command,
            'gate_main_command' => $settings->gate_main_command,
            'gate1_command' => $settings->gate1_command,
            'gate2_command' => $settings->gate2_command,
            'gate3_command' => $settings->gate3_command,
            'gate4_command' => $settings->gate4_command,
        ]);
    }

    /**
     * Get latest sensor reading for dashboard
     * GET /api/latest
     */
    public function getLatest(): JsonResponse
    {
        $latest = SensorReading::latest()->first();
        $settings = DeviceSetting::getSettings();
        $unreadAlerts = Alert::unread()->latest()->take(5)->get();

        return response()->json([
            'sensor' => $latest,
            'settings' => $settings,
            'alerts' => $unreadAlerts,
            'alerts_count' => Alert::unread()->count(),
        ]);
    }

    /**
     * Get history data for charts
     * GET /api/history
     */
    public function getHistory(Request $request): JsonResponse
    {
        $hours = $request->input('hours', 24);
        $limit = min($hours * 30, 1000); // Max 1000 records

        $history = SensorReading::where('created_at', '>=', now()->subHours($hours))
            ->orderBy('created_at', 'asc')
            ->limit($limit)
            ->get(['petak1_moisture', 'petak2_moisture', 'petak3_moisture', 'petak4_moisture', 'created_at']);

        return response()->json($history);
    }

    /**
     * Send control command from dashboard
     * POST /api/control
     */
    public function sendControl(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'mode' => 'sometimes|in:auto,manual',
            'pump_command' => 'sometimes|boolean',
            'gate_main_command' => 'sometimes|integer|in:0,45,90',
            'gate1_command' => 'sometimes|integer|in:0,45,90',
            'gate2_command' => 'sometimes|integer|in:0,45,90',
            'gate3_command' => 'sometimes|integer|in:0,45,90',
            'gate4_command' => 'sometimes|integer|in:0,45,90',
        ]);

        $settings = DeviceSetting::getSettings();
        $settings->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Control command sent',
            'settings' => $settings->fresh(),
        ]);
    }

    /**
     * Mark alerts as read
     * POST /api/alerts/read
     */
    public function markAlertsRead(Request $request): JsonResponse
    {
        $ids = $request->input('ids', []);

        if (empty($ids)) {
            Alert::unread()->update(['is_read' => true]);
        } else {
            Alert::whereIn('id', $ids)->update(['is_read' => true]);
        }

        return response()->json(['success' => true]);
    }

    /**
     * Check and create drought alerts
     */
    private function checkDroughtAlerts(array $data): void
    {
        $petaks = [
            'P1' => $data['petak1_moisture'],
            'P2' => $data['petak2_moisture'],
            'P3' => $data['petak3_moisture'],
            'P4' => $data['petak4_moisture'],
        ];

        foreach ($petaks as $petak => $moisture) {
            if ($moisture <= 30) {
                // Check if similar alert exists in last 30 minutes
                $recentAlert = Alert::where('type', 'drought')
                    ->where('petak', $petak)
                    ->where('created_at', '>=', now()->subMinutes(30))
                    ->first();

                if (!$recentAlert) {
                    Alert::create([
                        'type' => 'drought',
                        'message' => "Petak {$petak} mengalami kekeringan! Kelembaban: {$moisture}%",
                        'petak' => $petak,
                    ]);
                }
            }
        }

        // Check water tank level
        if (isset($data['water_tank']) && $data['water_tank'] > 12) {
            $recentAlert = Alert::where('type', 'water_low')
                ->where('created_at', '>=', now()->subMinutes(30))
                ->first();

            if (!$recentAlert) {
                Alert::create([
                    'type' => 'water_low',
                    'message' => 'Level air tandon rendah!',
                    'petak' => null,
                ]);
            }
        }
    }
}
