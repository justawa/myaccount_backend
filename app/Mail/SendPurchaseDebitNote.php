<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendPurchaseDebitNote extends Mailable
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
        $debit_notes = DebitNote::where('note_no', $note_no)->get();

        foreach($debit_notes as $note){
            $note->item_name = Item::findOrFail($note->item_id)->name ?? '';
        }

        $purchase = PurchaseRecord::findOrFail($debit_notes->first()->bill_no);

        return $this->from('admin@myaccountant.com')
                ->view('purchase.show_debit_note', compact('note_no', 'debit_notes', 'purchase'));
    }
}
