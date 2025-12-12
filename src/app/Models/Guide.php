<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Guide extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'experience_years', 'is_active'];

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }
}
