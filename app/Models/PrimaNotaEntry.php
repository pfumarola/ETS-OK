<?php

namespace App\Models;

use App\Services\RendicontoCassaSchema;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * Movimento in prima nota. entryable: Incasso, ExpenseRefund o null (manuale).
 * Voce contabile = rendiconto_code (schema MOD_D hardcoded).
 */
class PrimaNotaEntry extends Model
{
    protected $table = 'prima_nota_entries';

    protected $fillable = [
        'rendiconto_code',
        'entryable_type',
        'entryable_id',
        'date',
        'amount',
        'description',
        'gestione',
        'competenza_cassa',
    ];

    protected $appends = ['rendiconto_label'];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'amount' => 'decimal:2',
            'competenza_cassa' => 'boolean',
        ];
    }

    public function entryable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Label per la voce (code + name dallo schema MOD_D).
     */
    public function getRendicontoLabelAttribute(): string
    {
        return RendicontoCassaSchema::getLabelForCode($this->rendiconto_code ?? '');
    }
}
