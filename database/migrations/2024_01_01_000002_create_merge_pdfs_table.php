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
        Schema::create('pdfMerge', function (Blueprint $table) {
            $table->id('mergeId');
            $table->text('fileName')->nullable();
            $table->string('fileSize', 25)->nullable();
            $table->boolean('result');
            $table->boolean('isBatch');
            $table->uuid('processId');
            $table->uuid('batchId')->nullable();
            $table->timestamp('procStartAt')->nullable();
            $table->timestamp('procEndAt')->nullable();
            $table->text('procDuration')->nullable();
            $table->boolean('isReport')->nullable()->default(false);
            $table->timestamp('createdAt')->nullable()->useCurrent()->useCurrentOnUpdate();
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
        Schema::dropIfExists('pdfMerge');
    }
};
