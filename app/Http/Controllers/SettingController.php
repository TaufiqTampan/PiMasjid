<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Services\CloudinaryService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SettingController extends Controller
{
    public function index()
    {
        $settings = Setting::all()->groupBy('group');

        return Inertia::render('Settings/Index', [
            'settings' => $settings,
        ]);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'settings' => 'required|array',
            'settings.*.key' => 'required|exists:settings,key',
            'settings.*.value' => 'nullable',
        ]);

        foreach ($request->settings as $item) {
            $setting = Setting::where('key', $item['key'])->first();

            // Handle Image Upload to Cloudinary
            if ($setting->type === 'image' && isset($item['file']) && $item['file'] instanceof \Illuminate\Http\UploadedFile) {
                // Delete old image from Cloudinary if exists
                if ($setting->cloudinary_public_id) {
                    CloudinaryService::delete($setting->cloudinary_public_id);
                }

                // Upload to Cloudinary
                $result = CloudinaryService::upload($item['file'], 'settings');
                $setting->value = $result['url'];
                $setting->cloudinary_public_id = $result['public_id'];
            } elseif ($setting->type !== 'image') {
                $setting->value = $item['value'];
            }

            $setting->save();
        }

        // Clear caches
        \Illuminate\Support\Facades\Cache::forget('global_settings');
        \Illuminate\Support\Facades\Cache::forget('global_favicon');

        return redirect()->back()->with('success', 'Pengaturan berhasil diperbarui.');
    }
}
