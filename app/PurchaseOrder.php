<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PurchaseOrder extends Model
{

    protected $dates = ['date'];

    protected $table = 'purchase_order';
    public $timestamps = false;

    public function party()
    {
        return $this->belongsTo('App\Party');
    }
}
