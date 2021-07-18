<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AdditionalCharge extends Model
{
    public function invoice()
    {
        $this->belongsTo('App\Invoice');
    }
}
