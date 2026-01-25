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
            $table->integer('parent_member_id')->default(0)->after('parent_id')->comment('ID of parent member if this is a child');
            $table->boolean('is_parent')->default(false)->after('parent_member_id')->comment('Whether this member is a parent account');
            $table->string('relationship')->nullable()->after('is_parent')->comment('Relationship type: self, parent, child');
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
            $table->dropColumn(['parent_member_id', 'is_parent', 'relationship']);
        });
    }
};
