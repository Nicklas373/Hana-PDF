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
            $table->id('splitId');
            $table->text('fileName');
            $table->string('fileSize', 25);
            $table->string('fromPage', 5)->nullable();
            $table->string('toPage', 5)->nullable();
            $table->text('customPage')->nullable();
            $table->text('fixedPage')->nullable();
            $table->text('fixedPageRange')->nullable();
            $table->string('mergePDF', 25)->nullable();
            $table->boolean('result');
            $table->uuid('processId');
            $table->timestamp('procStartAt')->nullable();
            $table->timestamp('procEndAt')->nullable();
            $table->text('procDuration')->nullable();

            // Configure foreign key
            $table->foreign('processId')->references('processId')->on('appLogs');
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