<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CashDeposit extends Model
{

    protected $dates = ['date'];

    protected $table = 'cash_deposit';

    // public $timestamps = false;
}
