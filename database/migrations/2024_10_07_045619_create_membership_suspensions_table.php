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
        Schema::create('membership_suspensions', function (Blueprint $table) {
            $table->id();
            $table->integer('suspension_id');
            $table->integer('member_id');
            $table->string('start_date');
            $table->string('end_date');
            $table->string('status');
            $table->text('reason');
            $table->integer('parent_id');
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
        Schema::dropIfExists('membership_suspensions');
    }
};
