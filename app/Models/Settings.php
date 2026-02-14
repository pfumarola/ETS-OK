<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * Impostazioni applicative (key/value). Es. quota_annuale.
 */
class Settings extends Model
{
    protected $table = 'settings';

    protected $primaryKey = 'key';

    public $incrementing = false;

    public $timestamps = false;

    protected $keyType = 'string';

    protected $fillable = ['key', 'value'];

    /**
     * Restituisce il valore per la chiave. Per quota_annuale restituisce un float.
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        $row = DB::table('settings')->where('key', $key)->first();
        if (! $row) {
            return $default;
        }
        if ($key === 'quota_annuale') {
            return (float) $row->value;
        }
        if ($key === 'site_sections') {
            $decoded = json_decode($row->value, true);
            return is_array($decoded) ? $decoded : [];
        }
        return $row->value;
    }

    /**
     * Imposta il valore per la chiave (upsert).
     */
    public static function set(string $key, mixed $value): void
    {
        DB::table('settings')->updateOrInsert(
            ['key' => $key],
            ['value' => (string) $value]
        );
    }
}
