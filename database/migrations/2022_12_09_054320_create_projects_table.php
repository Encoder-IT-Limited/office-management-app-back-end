<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProjectsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->string('name', 64);
            $table->string('budget', 32);
            $table->date('start_date');
            $table->date('end_date');
            $table->enum('status', ['lead', 'pending', 'on_going', 'accepted', 'rejected', 'completed']);
            $table->foreignId('client_id')->constrained('id')->on('users')->onDelete('cascade');
            $table->boolean('is_kpi_filled')->nullable();
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
        Schema::dropIfExists('projects');
    }
}
