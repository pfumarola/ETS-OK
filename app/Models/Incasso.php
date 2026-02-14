<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphOne;

/**
 * Incasso (quota associativa o donazione). Unica entità per tutti gli incassi.
 */
class Incasso extends Model
{
    public const TYPE_QUOTA = 'quota';
    public const TYPE_DONAZIONE = 'donazione';

    protected $table = 'incassi';

    protected $fillable = [
        'member_id',
        'subscription_id',
        'amount',
        'paid_at',
        'payment_method_id',
        'description',
        'receipt_issued_at',
        'genera_prima_nota',
        'type',
    ];

    protected function casts(): array
    {
        return [
            'paid_at' => 'datetime',
            'receipt_issued_at' => 'datetime',
            'amount' => 'decimal:2',
            'genera_prima_nota' => 'boolean',
            'type' => 'string',
        ];
    }

    public function scopeQuota($query)
    {
        return $query->where('type', self::TYPE_QUOTA);
    }

    public function scopeDonazione($query)
    {
        return $query->where('type', self::TYPE_DONAZIONE);
    }

    public function primaNotaEntry(): MorphOne
    {
        return $this->morphOne(PrimaNotaEntry::class, 'entryable');
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }

    public function paymentMethod(): BelongsTo
    {
        return $this->belongsTo(PaymentMethod::class, 'payment_method_id');
    }

    public function receipt(): MorphOne
    {
        return $this->morphOne(Receipt::class, 'receivable');
    }
}
