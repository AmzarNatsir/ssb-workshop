<?php

namespace App\Http\Controllers;

use App\Models\SystemSetting;
use Illuminate\Http\Request;

class SystemSettingController extends Controller
{
    /**
     * Display system settings
     */
    public function index()
    {
        $settings = SystemSetting::all();
        
        return view('tool-lending.settings.index', compact('settings'));
    }

    /**
     * Update system settings
     */
    public function update(Request $request)
    {
        $request->validate([
            'settings' => 'required|array',
            'settings.*.key' => 'required|string',
            'settings.*.value' => 'required',
        ]);

        try {
            foreach ($request->settings as $setting) {
                SystemSetting::set($setting['key'], $setting['value']);
            }

            return redirect()
                ->route('tool-lending.settings.index')
                ->with('success', 'Settings updated successfully');

        } catch (\Exception $e) {
            return back()
                ->with('error', 'Failed to update settings: ' . $e->getMessage());
        }
    }
}
