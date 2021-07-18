<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Purchase_Item extends Model
{
    protected $table = 'purchase_item';

    protected $fillable = ['item_id', 'item_qty'];

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
