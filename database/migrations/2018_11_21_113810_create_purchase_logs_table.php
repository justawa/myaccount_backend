<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePurchaseLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('purchase_logs', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('qty');
            $table->float('price', 8, 2);
            $table->string('bill_no');
            $table->integer('igst');
            $table->integer('cgst');
            $table->integer('gst');
            $table->date('bought_on');
            $table->float('amount_paid', 8, 2);
            $table->float('amount_remaining', 8, 2);
            $table->integer('item_id');
            $table->integer('party_id');
            $table->integer('purchase_id');
            $table->enum('note_type', ['debit', 'credit']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('purchase_logs');
    }
}
