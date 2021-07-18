<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{

    protected $dates = ['invoice_date', 'due_date'];

    public function items()
    {
        return $this->belongsToMany('App\Item')->withPivot(['id', 'item_qty', 'item_alt_qty', 'item_comp_qty', 'free_qty', 'qty_type', 'item_price', 'gst', 'gst_rate', 'cess', 'discount_type', 'discount', 'barcode', 'remark', 'item_total', 'item_tax_type', 'item_measuring_unit', 'has_lump_sump', 'gst_classification']);
    }

    public function party()
    {
        return $this->belongsTo('App\Party');
    }

    public function invoice_items()
    {
        return $this->hasMany('App\Invoice_Item');
    }

    public function transporterDetail()
    {
        return $this->hasOne('App\TransporterDetail');
    }

    public function additionalCharge()
    {
        return $this->hasOne('App\AdditionalCharge');
    }

    public function eWayBills()
    {
        return $this->hasMany('App\Ewaybill');
    }

    public function regularEWayBill()
    {
        return $this->ewaybills()->where('status', 1)->orderBy('id', 'desc')->first() ?? null;
    }
}
