<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Item extends Model
{

    public function party()
    {
        return $this->belongsTo('App\Party');
    }

    public function invoices()
    {
        return $this->belongsToMany('App\Invoice');
    }

    public function purchases()
    {
        return $this->hasMany('App\Purchase');
    }

    public function managedInventories()
    {
        return $this->hasMany('App\ManagedInventory');
    }

}
