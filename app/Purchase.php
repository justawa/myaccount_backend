<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Purchase extends Model
{
    public function bills()
    {
        return $this->belongsToMany('App\PurchaseRecord');
    }

    public function purchase()
    {
        return $this->belongsTo('App\PurchaseRecord');
    }

    public function item()
    {
        return $this->belongsTo('App\Item');
    }

    public function party()
    {
        return $this->belongsTo('App\Party');
    }
}
