<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Metodo di pagamento (contanti, bonifico, carta, ecc.).
 */
class PaymentMethod extends Model
{
    protected $fillable = ['name'];

    public function incassi(): HasMany
    {
        return $this->hasMany(Incasso::class, 'payment_method_id');
    }
}
