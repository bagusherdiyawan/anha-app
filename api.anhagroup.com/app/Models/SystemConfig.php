<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class SystemConfig extends Model
{
    protected $fillable = ['key', 'value', 'type', 'group', 'description'];

    public static function get(string $key, mixed $default = null): mixed
    {
        $config = Cache::remember("config:{$key}", 3600, function () use ($key) {
            return static::where('key', $key)->first();
        });

        if (!$config)
            return $default;

        return match ($config->type) {
            'integer' => (int) $config->value,
            'boolean' => filter_var($config->value, FILTER_VALIDATE_BOOLEAN),
            'json' => json_decode($config->value, true),
            default => $config->value,
        };
    }

    public static function set(string $key, mixed $value): void
    {
        static::updateOrCreate(['key' => $key], ['value' => is_array($value) ? json_encode($value) : $value]);
        Cache::forget("config:{$key}");
    }

    public static function points_rate_per_point()
    {
        $value = SystemConfig::select('value')->where('key', 'points_rate_per_point')->first();
        if ($value) {
            return $value->value;
        }
        return 0;
    }
}
