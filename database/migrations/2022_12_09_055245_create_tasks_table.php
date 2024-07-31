<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

use App\Models\User;
use App\Models\Project;

class CreateTasksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Project::class, 'project_id');
            $table->foreignIdFor(User::class, 'author_id');
            $table->foreignIdFor(User::class, 'assignee_id')->nullable();
            $table->string('title');
            $table->longText('description')->nullable();
            $table->string('reference')->nullable();
            $table->text('screenshot')->nullable();

            $table->dateTime('start_date')->nullable();
            $table->dateTime('end_date')->nullable();
            $table->string('site')->nullable();
            $table->enum('priority', ['Low', 'Medium', 'High', 'Urgent',])->nullable();
            $table->enum('status', ['New', 'In Progress', 'On Hold', 'Completed', 'Testing', 'Issue', 'Canceled',])
                ->nullable()
                ->default('New');
            $table->string('estimated_time')->nullable();
            $table->string('given_time')->nullable();

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
        Schema::dropIfExists('tasks');
    }
}
