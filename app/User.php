<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Lab404\Impersonate\Models\Impersonate;

class User extends Authenticatable
{
    use Notifiable, Impersonate;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'simple_password', 'remember_token',
    ];

    public function profile()
    {
        return $this->hasOne('App\UserProfile');
    }

    public function purchaseSetting()
    {
        return $this->hasOne('App\PurchaseSetting');
    }

    public function purchaseOrderSetting()
    {
        return $this->hasOne('App\PurchaseOrderSetting');
    }

    public function saleOrderSetting()
    {
        return $this->hasOne('App\SaleOrderSetting');
    }

    public function paymentSetting()
    {
        return $this->hasOne('App\PaymentSetting');
    }

    public function receiptSetting()
    {
        return $this->hasOne('App\ReceiptSetting');
    }

    // public function cashWithdrawSetting()
    // {
    //     return $this->hasOne('App\CashWithdrawSetting');
    // }

    public function cashSetting()
    {
        return $this->hasOne('App\CashDepositSetting');
    }

    public function gstPaymentSetting()
    {
        // asked to merge gstpaymentsetting and paymentsetting into one
        //return $this->hasOne('App\GstPaymentSetting');
        return $this->hasOne('App\PaymentSetting');
    }

    public function noteSetting()
    {
        return $this->hasOne('App\NoteSetting');
    }

    public function profileEdit()
    {
        return $this->hasMany('App\UserProfileEdit');
    }

    public function roundOffSetting()
    {
        return $this->hasOne('App\RoundOffSetting');
    }

    public function parties()
    {
        return $this->hasMany('App\Party');
    }

    public function getPartyIdsAttribute()
    {
        return $this->parties->pluck('id');
    }

    public function banks()
    {
        return $this->hasMany('App\Bank');
    }

    public function items()
    {
        return $this->hasMany('App\Item');
    }

    public function getItemIdsAttribute()
    {
        return $this->items->pluck('id');
    }
    
    public function creditNotes()
    {
        return $this->hasManyThrough('App\CreditNote', 'App\Item');
    }
    
    public function debitNotes()
    {
        return $this->hasManyThrough('App\DebitNote', 'App\Item');
    }
    
    public function purchases()
    {
        return $this->hasManyThrough('App\PurchaseRecord', 'App\Party');
    }
    
    public function invoices()
    {
        return $this->hasManyThrough('App\Invoice', 'App\Party');
    }

    public function purchaseRemainingAmounts()
    {
        return $this->hasManyThrough('App\PurchaseRemainingAmount', 'App\Party');
    }

    public function saleRemainingAmounts()
    {
        return $this->hasManyThrough('App\SaleRemainingAmount', 'App\Party');
    }

    public function partyRemainingAmounts()
    {
        return $this->hasManyThrough('App\PartyPendingPaymentAccount', 'App\Party');
    }

    public function purchaseOrder()
    {
        return $this->hasManyThrough('App\PurchaseOrder', 'App\Party');
    }

    public function saleOrder()
    {
        return $this->hasManyThrough('App\SaleOrder', 'App\Party');
    }

    public function cashDeposit()
    {
        return $this->hasMany('App\CashDeposit');
    }

    public function cashWithdraw()
    {
        return $this->hasMany('App\CashWithdraw');
    }

    public function bankToBankTransfers()
    {
        return $this->hasMany('App\BankToBankTransfer');
    }

    public function gstPayments()
    {
        return $this->hasMany('App\GSTSetOff');
    }

    public function transporters()
    {
        return $this->hasMany('App\Transporter');
    }

    public function managedInventories()
    {
        return $this->hasManyThrough('App\ManagedInventory', 'App\Item');
    }

    public function invoiceItems()
    {
        return $this->hasManyThrough('App\Invoice_Item', 'App\Party');
    }

    public function purchaseItems()
    {
        return $this->hasManyThrough('App\Purchase', 'App\Party');
    }

    public function selectOption()
    {
        return $this->hasOne('App\SelectOption');
    }

    public function purchaseSelectOption()
    {
        return $this->hasOne('App\PurchaseSelectOption');
    }

    public function ewaybills()
    {
        return $this->hasMany('App\Ewaybill');
    }

    public function ewaybillDetail()
    {
        return $this->hasOne('App\EwaybillDetail');
    }
}