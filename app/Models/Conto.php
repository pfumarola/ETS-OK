<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Conto extends Model
{
    protected $table = 'conti';

    protected $fillable = [
        'name',
        'code',
        'type',
        'ordine',
        'attivo',
    ];

    protected function casts(): array
    {
        return [
            'attivo' => 'boolean',
        ];
    }

    public function movimenti(): HasMany
    {
        return $this->hasMany(PrimaNotaEntry::class, 'conto_id');
    }

    public function incassi(): HasMany
    {
        return $this->hasMany(Incasso::class, 'conto_id');
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('ordine')->orderBy('name');
    }

    public function scopeAttivi($query)
    {
        return $query->where('attivo', true);
    }
}
