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
        Schema::create('pdfWatermark', function (Blueprint $table) {
            $table->id('watermarkId')->primary()->unique();
            $table->text('fileName')->nullable();
            $table->char('fileSize', length: 25)->nullable();
            $table->enum('watermarkFontFamily', ['Arial','Arial Unicode MS','Comic Sans MS','Courier','Times New Roman','Verdana'])->nullable();
            $table->enum('watermarkFontStyle', ['Regular','Bold','Italic'])->nullable();
            $table->integer('watermarkFontSize')->nullable();
            $table->integer('watermarkFontTransparency')->nullable();
            $table->text('watermarkImage')->nullable();
            $table->enum('watermarkLayout', ['above','below'])->nullable();
            $table->boolean('watermarkMosaic')->nullable();
            $table->integer('watermarkRotation')->nullable();
            $table->enum('watermarkStyle', ['img','txt'])->nullable();
            $table->text('watermarkText')->nullable();
            $table->char('watermarkPage', length: 25)->nullable();
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
            $table->text('procDuration')->nullable();
            $table->timestamp('created_at')->nullable()->useCurrent()->useCurrentOnUpdate();
            $table->timestamp('updated_at')->nullable()->useCurrentOnUpdate();

            // Configure foreign key
            $table->foreign('processId')->references('processId')->on('appLogs');
            $table->foreign('groupId')->references('groupId')->on('appLogs');
            $table->foreign('fileId')->references('fileId')->on('filePool');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pdfWatermark');
    }
};
