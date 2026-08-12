<?php

if (! function_exists('setting')) {
    /**
     * Get a setting value by key with optional default
     */
    function setting(string $key, mixed $default = null): mixed
    {
        $setting = \App\Models\Setting::where('key', $key)->first();

        return $setting ? $setting->value : $default;
    }
}
