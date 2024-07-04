<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLeavesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('leaves', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('title');
            $table->text('description');
            $table->date('start_date');
            $table->date('end_date');
            $table->enum('status', ['new', 'pending', 'accepted', 'rejected'])->default('pending');
            $table->string('reason')->nullable();
            $table->date('accepted_start_date')->nullable();
            $table->date('accepted_end_date')->nullable();
            $table->foreignId('accepted_by')->nullable()->constrained('users')->after('id');
            $table->foreignId('last_updated_by')->nullable()->constrained('users')->after('id');
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
        Schema::dropIfExists('leaves');
    }
}
