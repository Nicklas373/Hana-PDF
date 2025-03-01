<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
     /**
     * The database connection that should be used by the migration.
     *
     * @var string
     */
    protected $connection = 'pgsql';

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('pdfSplit', function (Blueprint $table) {
            $table->id('splitId')->primary()->unique();
            $table->text('fileName')->nullable();
            $table->char('fileSize', length: 25)->nullable();
            $table->integer('fromPage')->nullable();
            $table->integer('toPage')->nullable();
            $table->text('customSplitPage')->nullable();
            $table->text('customDeletePage')->nullable();
            $table->text('fixedRange')->nullable();
            $table->enum('mergePDF', ['true', 'false'])->nullable();
            $table->enum('action', ['delete','split'])->nullable();
            $table->boolean('result');
            $table->boolean('isBatch');
            $table->boolean('isDeleted');
            $table->boolean('isReport')->nullable()->default(false);
            $table->text('batchName')->nullable();
            $table->id('fileId');
            $table->uuid('groupId');
            $table->uuid('processId')->unique();
            $table->timestamp('deletedAt')->nullable();
            $table->timestamp('procStartAt')->nullable();
            $table->timestamp('procEndAt')->nullable();
            $table->char('procDuration', length: 25)->nullable();
            $table->timestamp('created_at')->nullable()->useCurrent()->useCurrentOnUpdate();
            $table->timestamp('updated_at')->nullable()->useCurrentOnUpdate();

            // Configure foreign key
            $table->foreign('processId')->references('processId')->on('appLogs');
            $table->foreign('fileId')->references('fileId')->on('filePool');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pdfSplit');
    }
};
