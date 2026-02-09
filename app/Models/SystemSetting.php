<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class SystemSetting extends Model
{
    use HasFactory;

    protected $table = 'system_settings';

    protected $fillable = [
        'setting_key',
        'setting_value',
        'description',
    ];

    /**
     * Get a system setting value by key
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public static function get($key, $default = null)
    {
        return Cache::remember("system_setting_{$key}", 3600, function () use ($key, $default) {
            $setting = self::where('setting_key', $key)->first();
            return $setting ? $setting->setting_value : $default;
        });
    }

    /**
     * Set a system setting value
     *
     * @param string $key
     * @param mixed $value
     * @param string|null $description
     * @return bool
     */
    public static function set($key, $value, $description = null)
    {
        $setting = self::updateOrCreate(
            ['setting_key' => $key],
            [
                'setting_value' => $value,
                'description' => $description ?? "Setting for {$key}"
            ]
        );

        // Clear cache
        Cache::forget("system_setting_{$key}");

        return $setting ? true : false;
    }

    /**
     * Get all settings as key-value array
     *
     * @return array
     */
    public static function getAll()
    {
        return self::all()->pluck('setting_value', 'setting_key')->toArray();
    }
}
