<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Party extends Model
{

    public function items()
    {
        return $this->hasMany('App\Item');
    }

    public function invoices()
    {
        return $this->hasMany('App\Invoice');
    }

    public function user()
    {
        return $this->belongsTo('App\User');
    }
    
}
