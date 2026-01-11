<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('members', function (Blueprint $table) {
            $table->text('password')->nullable();
            $table->integer('user_id')->default(0);
            $table->text('membership_part')->nullable();
        });
        Schema::table('notifications', function (Blueprint $table) {
            $table->text('sms_message')->nullable();
            $table->integer('enabled_sms')->default(0);
        });
        Schema::table('membership_payments', function (Blueprint $table) {
            $table->dropColumn('invoice_id');
              $table->text('receipt')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {

        Schema::table('members', function (Blueprint $table) {
            $table->dropColumn('password');
            $table->dropColumn('user_id');
            $table->dropColumn('membership_part');
        });

        Schema::table('notifications', function (Blueprint $table) {
            $table->dropColumn('sms_message');
            $table->dropColumn('enabled_sms');
        });

          Schema::table('membership_payments', function (Blueprint $table) {
            $table->dropColumn('receipt');
        });
    }
};
