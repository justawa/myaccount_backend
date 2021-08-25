<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SaleOrder extends Model
{
    
    protected $dates = ['date'];
    
    protected $table = 'sale_order';
    public $timestamps = false;

    public function party()
    {
        return $this->belongsTo('App\Party');
    }
}
