<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateprojectTasksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('project_tasks', function (Blueprint $table) {
            $table->id();
            $table->string('task');
            $table->foreignId('project_id')->constrained('id')->on('projects')->onDelete('cascade');
            $table->foreignId('developer_id')->constrained('id')->on('users')->onDelete('cascade');
            $table->date('start_date');
            $table->date('end_date');
            $table->softDeletes();
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
        Schema::dropIfExists('project_tasks');
    }
}
