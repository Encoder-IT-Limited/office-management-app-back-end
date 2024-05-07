<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFieldsToLeaves extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('leaves', function (Blueprint $table) {
            $table->foreignId('accepted_by')->nullable()->constrained('users')->after('id');
            $table->foreignId('last_updated_by')->nullable()->constrained('users')->after('id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('leaves', function (Blueprint $table) {
            $table->dropColumn('accepted_by');
            $table->dropColumn('last_updated_by');
        });
    }
}
