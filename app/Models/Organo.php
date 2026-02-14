<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Organo extends Model
{
    protected $table = 'organi';

    protected $fillable = [
        'nome',
        'durata_mesi',
        'richiedi_elezioni_fine_mandato',
        'mandato_da',
    ];

    protected function casts(): array
    {
        return [
            'richiedi_elezioni_fine_mandato' => 'boolean',
            'mandato_da' => 'date',
        ];
    }

    public function caricheSociali(): HasMany
    {
        return $this->hasMany(CaricaSociale::class, 'organo_id')->orderBy('ordine');
    }

    public function elezioni(): HasMany
    {
        return $this->hasMany(Elezione::class, 'organo_id');
    }
}
