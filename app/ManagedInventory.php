<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ManagedInventory extends Model
{
    protected $table = 'managed_inventory';

    public function item()
    {
        return $this->belongsTo('App\Item');
    }
}
