<?php

use App\Models\LabelStatus;
use App\Models\Project;
use App\Models\ProjectTask;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLabelStatusesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('label_statuses', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Project::class, 'project_id')->nullable();
            $table->string('title');
            $table->string('color')->nullable();
            $table->integer('list_order')->default(1);
            $table->enum('type', ['label', 'status']);
            $table->enum('franchise', ['task', 'project']);
            $table->timestamps();
        });

        Schema::create('statusables', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(LabelStatus::class, 'label_status_id')->nullable()->constrained();
            $table->string('color');
            $table->integer('list_order')->default(1);
            $table->morphs('statusable');
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
        Schema::dropIfExists('statusable');
        Schema::dropIfExists('label_statuses');
    }
}
