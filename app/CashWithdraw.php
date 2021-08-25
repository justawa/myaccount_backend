<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CashWithdraw extends Model
{

    protected $dates = ['date'];

    protected $table = 'cash_withdraw';

    // public $timestamps = false;
}
