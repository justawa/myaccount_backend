<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendBill extends Mailable
{
    use Queueable, SerializesModels;
    public $bill_id;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($bill_id)
    {
        $this->bill_id = $bill_id;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $user_profile = UserProfile::where('user_id', Auth::user()->id)->first();

        $bill = PurchaseRecord::findOrFail($id);
        $bill_items = Purchase::where('purchase_id', $id)->get();
        $party = $bill->party;

        foreach ($bill_items as $data) {
            $item = Item::find($data->item_id);
            
            $data->info = $item;
        }

        return $this->from('admin@myaccountant.com')
                ->view('pdf.bill', compact('bill', 'party', 'user_profile', 'bill_items'));
    }
}