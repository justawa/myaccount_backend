<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Ledger extends Model
{
    public function account_list()
    {
        return $this->hasOne('App\AccountList');
    }
}
