<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $guarded = ['id'];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
    const TRANSACTION_TYPE_DEPOSIT = 'deposite';
    const TRANSACTION_TYPE_WITHDRAWN = 'withdrawn';
    const FIVE_THOUSANDS_TAKA = 5000;
    const FIFTY_THOUSANDS_TAKA = 50000;
}
