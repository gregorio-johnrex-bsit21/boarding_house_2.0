<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    public $timestamps = false;
    public $incrementing = false;
    protected $primaryKey = 'key';
    protected $keyType = 'string';
    protected $fillable = ['key', 'value'];

    public static function get(string $key, $default = null)
    {
        return static::find($key)?->value ?? $default;
    }
    public static function put(string $key, $value): void
    {
        static::updateOrCreate(['key' => $key], ['value' => $value]);
    }

    public static function getJson(string $key, array $default = []): array
    {
        $value = static::get($key);
        if (is_array($value)) return $value;
        if (!is_string($value) || trim($value) === '') return $default;
        $decoded = json_decode($value, true);
        return is_array($decoded) ? $decoded : $default;
    }

    public static function putJson(string $key, array $value): void
    {
        static::put($key, json_encode($value, JSON_UNESCAPED_UNICODE));
    }
}
