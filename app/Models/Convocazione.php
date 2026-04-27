<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Convocazione extends Model
{
    public const TIPO_ASSEMBLEA = 'assemblea';
    public const TIPO_CONSIGLIO = 'consiglio_direttivo';

    public const STATO_BOZZA = 'bozza';
    public const STATO_INVIATA = 'inviata';

    protected $table = 'convocazioni';

    protected $fillable = [
        'tipo',
        'titolo',
        'scheduled_at',
        'luogo',
        'ordine_del_giorno',
        'testo_email',
        'stato',
        'sent_at',
        'created_by',
        'sent_by',
    ];

    protected $appends = ['tipo_label', 'stato_label', 'in_bozza'];

    protected function casts(): array
    {
        return [
            'scheduled_at' => 'datetime',
            'sent_at' => 'datetime',
        ];
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function sentBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sent_by');
    }

    public function getTipoLabelAttribute(): string
    {
        return match ($this->tipo) {
            self::TIPO_ASSEMBLEA => 'Assemblea',
            self::TIPO_CONSIGLIO => 'Consiglio direttivo',
            default => (string) $this->tipo,
        };
    }

    public function getStatoLabelAttribute(): string
    {
        return match ($this->stato) {
            self::STATO_BOZZA => 'Bozza',
            self::STATO_INVIATA => 'Inviata',
            default => (string) $this->stato,
        };
    }

    public function isBozza(): bool
    {
        return $this->stato === self::STATO_BOZZA;
    }

    public function getInBozzaAttribute(): bool
    {
        return $this->isBozza();
    }
}
