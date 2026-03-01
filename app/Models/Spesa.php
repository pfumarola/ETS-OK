<?php

namespace App\Models;

use App\Services\RendicontoCassaSchema;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * Spesa: uscita di cassa con opzione registrazione in prima nota e voce di rendiconto.
 */
class Spesa extends Model
{
    protected $table = 'spese';

    protected $fillable = [
        'date',
        'amount',
        'description',
        'conto_id',
        'genera_prima_nota',
        'rendiconto_code',
        'gestione',
        'competenza_cassa',
    ];

    protected $casts = [
        'date' => 'date',
        'amount' => 'decimal:2',
        'competenza_cassa' => 'boolean',
        'genera_prima_nota' => 'boolean',
    ];

    protected $appends = ['rendiconto_label'];

    public function conto(): BelongsTo
    {
        return $this->belongsTo(Conto::class);
    }

    public function primaNotaEntries(): MorphMany
    {
        return $this->morphMany(PrimaNotaEntry::class, 'entryable');
    }

    public function getRendicontoLabelAttribute(): string
    {
        return $this->rendiconto_code
            ? RendicontoCassaSchema::getLabelForCode($this->rendiconto_code)
            : '';
    }
}
