<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLedgersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ledgers', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('account')->unsigned();
            $table->integer('account_reference')->unsigned()->nullable();
            $table->integer('particular')->unsigned();
            $table->integer('particular_reference')->unsigned()->nullable();
            $table->enum('type', ['cr', 'dr']);
            $table->float('amount', 8, 2);
            $table->integer('invoice_id')->unsigned();
            $table->timestamps();

            $table->foreign('account')->references('id')->on('account_lists');
            $table->foreign('particular')->references('id')->on('account_lists');
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
        Schema::dropIfExists('ledgers');
    }
}
