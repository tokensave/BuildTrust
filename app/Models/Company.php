<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Company extends Model
{
    use HasFactory;

    protected $fillable = [
        'inn',
        'name',
        'email',
        'phone',
        'city',
        'address',
        'website',
        'verified',
    ];

    /**
     * Get all users belonging to this company.
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
}
