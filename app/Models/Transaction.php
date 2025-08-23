<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'transaction_id',
        'user_id',
        'amount',
        'type',
        'description',
    ];

    // Relation to User
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
