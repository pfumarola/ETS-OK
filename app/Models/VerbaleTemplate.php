<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VerbaleTemplate extends Model
{
    protected $table = 'verbale_templates';

    protected $fillable = ['nome', 'tipo', 'contenuto'];

    protected $appends = ['tipo_label'];

    public function getTipoLabelAttribute(): ?string
    {
        return match ($this->tipo) {
            'consiglio_direttivo' => 'Consiglio direttivo',
            'assemblea' => 'Assemblea',
            default => null,
        };
    }
}
