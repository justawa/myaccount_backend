<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

use App\PurchaseOrder;
use App\Party;
use App\Item;

class SendSaleOrder extends Mailable
{
    use Queueable, SerializesModels;
    
    public $sale_order_no;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($sale_order_no)
    {
        $this->sale_order_no = $sale_order_no;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $records = SaleOrder::where('token', $this->sale_order_no)->get();
        $user_profile = auth()->user()->profile;
        $party_name = '';
        $order_token = '';

        foreach ($records as $record) {
            $party = Party::find($record->party_id);
            $item = Item::find($record->item_id);

            $party_name = $party->name;
            $record->item_name = $item->name;

            $order_token = $record->token;
        }

        // return $party;

        //$pdf = PDF::loadView('pdf.invoice', compact('invoice', 'party', 'user_profile', 'invoice_items'));

        return $this->from('admin@myaccountant.com')
                ->view('pdf.sale_order', compact('records', 'party_name', 'order_token', 'user_profile'));
    }
}
