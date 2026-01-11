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
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->integer('parent_id');
            $table->integer('expense_id')->default(0);
            $table->integer('member_id')->default(0);
            $table->string('title')->nullable();
            $table->date('date')->nullable();
            $table->string('type')->nullable();
            $table->integer('amount')->default(0);
            $table->string('receipt')->nullable();
            $table->text('notes')->nullable();
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
        Schema::dropIfExists('expenses');
    }
};
