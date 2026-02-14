<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Item extends Model
{
    protected $fillable = ['name', 'code', 'unit'];

    public function warehouseStocks(): HasMany
    {
        return $this->hasMany(WarehouseStock::class, 'item_id');
    }
}
