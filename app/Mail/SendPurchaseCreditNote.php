<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendPurchaseCreditNote extends Mailable
{
    use Queueable, SerializesModels;
    public $note_no;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($note_no)
    {
        $this->note_no = $note_no;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $credit_notes = CreditNote::where('note_no', $note_no)->get();

        foreach($credit_notes as $note){
            $note->item_name = Item::findOrFail($note->item_id)->name ?? '';
        }

        $purchase = PurchaseRecord::findOrFail($credit_notes->first()->invoice_id);

        return $this->from('admin@myaccountant.com')
                ->view('purchase.show_credit_note', compact('note_no', 'credit_notes', 'purchase'));
    }
}
