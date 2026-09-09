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
        Schema::table('assignments', function (Blueprint $table) {
            $table->string('submission_mode')->default('none')->after('type'); // none | file
            $table->unsignedSmallInteger('max_files')->default(1)->after('submission_mode');
            $table->text('allowed_extensions')->nullable()->after('max_files');
            $table->unsignedInteger('max_file_size_kb')->default(10240)->after('allowed_extensions');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('assignments', function (Blueprint $table) {
            $table->dropColumn(['submission_mode', 'max_files', 'allowed_extensions', 'max_file_size_kb']);
        });
    }
};