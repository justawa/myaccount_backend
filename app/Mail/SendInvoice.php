<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

use Auth;

use App\Invoice;
use App\Invoice_Item;
use App\Item;
use App\UserProfile;

class SendInvoice extends Mailable
{
    use Queueable, SerializesModels;
    public $invoice_id;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($invoice_id)
    {
        $this->invoice_id = $invoice_id;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $user_profile = UserProfile::where('user_id', Auth::user()->id)->first();

        $invoice = Invoice::findOrFail($this->invoice_id);
        $invoice_items = Invoice_Item::where('invoice_id', $this->invoice_id)->get();
        $party = Invoice::findorFail($this->invoice_id)->party;

        foreach ($invoice_items as $data) {
            $item = Item::find($data->item_id);

            $data->info = $item;
        }

        // return $party;

        //$pdf = PDF::loadView('pdf.invoice', compact('invoice', 'party', 'user_profile', 'invoice_items'));

        return $this->from('admin@myaccountant.com')
                ->view('pdf.invoice', compact('invoice', 'party', 'user_profile', 'invoice_items'));
    }
}
