<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateApiKeysTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('api_keys', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('id')->on('users')->onDelete('cascade');

            $table->string('name');
            $table->string('value');

            $table->string('icon')->default('material-symbols:api');
            $table->string('type')->default('api_key');
            $table->string('provider')->default('github');

            $table->string('url')->nullable();
            $table->integer('reference')->nullable();

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
        Schema::dropIfExists('api_keys');
    }
}
