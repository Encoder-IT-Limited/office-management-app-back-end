<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFieldsToTasksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropColumn('start_date');
            $table->dropColumn('end_date');
//            $table->dropColumn('site');
//            $table->dropColumn('estimated_time');
//            $table->dropColumn('priority');
//            $table->dropColumn('status');
//            $table->dropColumn('given_time');
        });

        Schema::table('tasks', function (Blueprint $table) {
            $table->dateTime('start_date')->nullable();
            $table->dateTime('end_date')->nullable();
            $table->string('site')->nullable()->after('title');
            $table->string('estimated_time')->nullable()->after('site');
            $table->enum('priority', ['Low', 'Medium', 'High', 'Urgent',])
                ->nullable()->after('site');
            $table->enum('status', ['New', 'In Progress', 'On Hold', 'Completed', 'Testing', 'Issue', 'Canceled',])
                ->nullable()->default('New')->after('priority');
            $table->string('given_time')->nullable()->after('estimated_time');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropColumn('start_date');
            $table->dropColumn('end_date');
            $table->dropColumn('site');
            $table->dropColumn('estimated_time');
            $table->dropColumn('priority');
            $table->dropColumn('status');
            $table->dropColumn('given_time');
        });
    }
}
