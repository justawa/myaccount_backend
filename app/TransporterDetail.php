<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TransporterDetail extends Model
{
    public function invoice()
    {
        return $this->belongsTo('App\Invoice');
    }

    public function transporter()
    {
        return $this->belongsTo('App\Transporter');
    }
}
