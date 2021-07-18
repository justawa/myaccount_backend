<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Invoice_Item extends Model
{
    protected $table = 'invoice_item';


    public function invoice()
    {
        return $this->belongsTo('App\Invoice');
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
