<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

/** Paramètre modifiable depuis l'application (clé → valeur), mis en cache. */
class Setting extends Model
{
    protected $fillable = ['key', 'value', 'updated_by'];

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public static function get(string $key, $default = null)
    {
        $value = Cache::rememberForever("setting.$key", fn () => static::where('key', $key)->value('value'));

        return $value ?? $default;
    }

    public static function set(string $key, $value): void
    {
        static::updateOrCreate(['key' => $key], ['value' => $value, 'updated_by' => auth()->id()]);
        Cache::forget("setting.$key");
    }
}
