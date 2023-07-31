<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRemindersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('reminders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('id')->on('users')->onDelete('cascade');
            $table->foreignId('project_id')->nullable()->constrained('id')->on('projects')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('id')->on('users')->onDelete('cascade');
            $table->date('date');
            $table->time('time');
            $table->time('reminder_at');
            $table->text('description', 500);
            $table->boolean('message')->default(1);
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
        Schema::dropIfExists('reminders');
    }
}
