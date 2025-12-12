<?php

// app/Models/Booking.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    use HasFactory;

    protected $fillable = ['tour_name', 'hunter_name', 'guide_id', 'date', 'participants_count'];

    public function guide()
    {
        return $this->belongsTo(Guide::class);
    }
}
