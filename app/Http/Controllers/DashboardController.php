<?php

namespace App\Http\Controllers;

use App\Models\SensorReading;
use App\Models\DeviceSetting;
use App\Models\Alert;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Display the dashboard
     */
    public function index(): View
    {
        $latest = SensorReading::latest()->first();
        $settings = DeviceSetting::getSettings();
        $alerts = Alert::unread()->latest()->take(10)->get();
        $alertsCount = Alert::unread()->count();

        // Get data for chart (last 24 hours)
        $chartData = SensorReading::where('created_at', '>=', now()->subHours(24))
            ->orderBy('created_at', 'asc')
            ->get(['petak1_moisture', 'petak2_moisture', 'petak3_moisture', 'petak4_moisture', 'created_at']);

        return view('dashboard', compact('latest', 'settings', 'alerts', 'alertsCount', 'chartData'));
    }

    /**
     * Display history page
     */
    public function history(): View
    {
        $readings = SensorReading::latest()->paginate(50);

        return view('history', compact('readings'));
    }
}
