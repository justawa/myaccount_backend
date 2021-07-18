<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PurchaseRecord extends Model
{
    protected $dates = [ 'bill_date'];

    public function party()
    {
        return $this->belongsTo('App\Party');
    }

    public function items()
    {
        return $this->belongsToMany( 'App\Item', 'purchases');
    }

    public function purchase_items()
    {
        return $this->hasMany('App\Purchase', 'purchase_id');
    }
}
