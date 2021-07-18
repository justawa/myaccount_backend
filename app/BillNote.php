<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class BillNote extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'bill_no', 'taxable_value_difference', 'gst_value_difference', 'reason', 'type'
    ];
}