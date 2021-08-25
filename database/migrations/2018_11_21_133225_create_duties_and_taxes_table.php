<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDutiesAndTaxesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('duties_and_taxes', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('igst');
            $table->integer('cgst');
            $table->string('gst');
            $table->integer('purchase_id')->unsigned()->nullable();
            $table->integer('invoice_id')->unsigned()->nullable();
            $table->enum('type', ['sale', 'purchase']);
            $table->timestamps();

            $table->foreign('purchase_id')->references('id')->on('purchases');
            $table->foreign('invoice_id')->references('id')->on('invoices');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('duties_and_taxes');
    }
}
