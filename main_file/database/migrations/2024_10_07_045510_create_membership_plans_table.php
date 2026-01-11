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
        Schema::create('membership_plans', function (Blueprint $table) {
            $table->id();
            $table->integer('plan_id');
            $table->string('plan_name');
            $table->text('plan_description')->nullable();
            $table->string('duration');
            $table->string('price');
            $table->string('billing_frequency');
            // $table->string('benefits')->nullable();
            // $table->string('access_level')->nullable();
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
        Schema::dropIfExists('membership_plans');
    }
};
