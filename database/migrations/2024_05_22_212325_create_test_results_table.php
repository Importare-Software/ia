<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('test_results', function (Blueprint $table) {
            $table->id();
            $table->string('scenario_id')->nullable();
            $table->text('condition')->nullable();
            $table->string('use_case')->nullable();
            $table->text('execution_detail')->nullable();
            $table->text('expected_results')->nullable();
            $table->text('locators')->nullable();
            $table->text('input_data')->nullable();
            $table->string('model_used');
            $table->longText('ai_response');
            $table->integer('ai_response_score')->nullable();
            $table->longText('ai_response_corrected')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('test_results');
    }
};
