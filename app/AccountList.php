<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AccountList extends Model
{
    public function ledger()
    {
        return $this->belongsTo('App\Ledger');
    }
}
