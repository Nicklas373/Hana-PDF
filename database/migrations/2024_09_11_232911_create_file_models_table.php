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
        Schema::create('filePool', function (Blueprint $table) {
            $table->id('fileId')->primary()->unique();
            $table->text('fileName')->nullable();
            $table->char('fileSize', length: 25)->nullable();
            $table->boolean('isDeleted');
            $table->uuid('processId')->unique();
            $table->timestamp('deletedAt')->nullable();
            $table->timestamp('created_at')->nullable()->useCurrent()->useCurrentOnUpdate();
            $table->timestamp('updated_at')->nullable()->useCurrentOnUpdate();

            // Configure foreign key
            $table->foreign('processId')->references('processId')->on('appLogs');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('filePool');
    }
};
