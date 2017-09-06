<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateWalletSettingsTable extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('qiwi_wallet_settings', function (Blueprint $table) {
            $table->integer('wallet_id')->unsigned()->index();
            $table->string('comments')->nullable();
            $table->boolean('is_always_online')->nullable();
            $table->integer('balance_recheck_timeout')->default(0);
            $table->double('maximum_balance')->default(floatval(1000000.0));
            $table->boolean('autoWithdrawal_active')->default(false);

            $table->integer('autoWithdrawal_type_id')->nullable()->unsigned()->index();
            $table->foreign('autoWithdrawal_type_id')->references("id")->on("autowithdraw_types")->onDelete('cascade');

            $table->boolean('using_vouchers')->default(false);
            $table->string('autoWithdrawal_card_number')->nullable();
            $table->string('autoWithdrawal_cardholder_name')->nullable();
            $table->string('autoWithdrawal_cardholder_surname')->nullable();


            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::dropIfExists("qiwi_wallet_settings");
    }
}