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
        Schema::create('shuffle_runs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subject_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('algorithm');
            $table->string('version')->nullable();
            $table->string('seed');
            $table->integer('group_count')->nullable();
            $table->integer('members_per_group')->nullable();
            $table->string('hash'); // commitment hash of the results
            $table->json('result')->nullable(); // serialized group assignment
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shuffle_runs');
    }
};
