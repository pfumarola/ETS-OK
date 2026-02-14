<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Property extends Model
{
    protected $fillable = ['name', 'address', 'notes'];

    public function assets(): HasMany
    {
        return $this->hasMany(Asset::class);
    }
}
