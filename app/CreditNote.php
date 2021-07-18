<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CreditNote extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    
     // protected $fillable = [
    //     'item_id', 'invoice_id', 'price', 'price_difference', 'reason_price_change', 'reason_price_change_other', 'gst', 'gst_percent_difference', 'reason_gst_change', 'reason_gst_change_other', 'quantity', 'quantity_difference', 'reason_quantity_change', 'reason_quantity_change_other', 'discount', 'discount_difference', 'reason_discount_change', 'reason_discount_change_other', 'reason', 'remarks', 'taxable_value', 'gst_value', 'note_value', 'discount_value', 'type'
    // ];

    /**
     * guarded properties
     * 
     * @var array
     */
    protected $guarded = [];
}
