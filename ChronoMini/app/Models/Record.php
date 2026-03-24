<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Record extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'kid_id',
        'drop_hour',
        'pick_up_hour',
        'amount_hours',
        'date',
        'annotation' 
    ];

    public function kid()
    {
        return $this->belongsTo(Kid::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function timeBreaks()
    {
        return $this->hasMany(TimeBreak::class);
    }
}
