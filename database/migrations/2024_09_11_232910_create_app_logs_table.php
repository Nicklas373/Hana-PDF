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
        Schema::create('appLogs', function (Blueprint $table) {
            $table->uuid('processId')->primary()->unique();
            $table->uuid('groupId');
            $table->text('errReason')->nullable();
            $table->text('errStatus')->nullable();
            $table->timestamp('created_at')->nullable()->useCurrent()->useCurrentOnUpdate();
            $table->timestamp('updated_at')->nullable()->useCurrentOnUpdate();

            // Configure foreign keys
            $table->foreign('processId')->references('processId')->on('pdfCompress')->name('pdf_compress_fk');
            $table->foreign('groupId')->references('groupId')->on('pdfCompress')->name('pdf_group_compress_fk');
            $table->foreign('processId')->references('processId')->on('pdfMerge')->name('pdf_merge_fk');
            $table->foreign('groupId')->references('groupId')->on('pdfMerge')->name('pdf_group_merge_fk');
            $table->foreign('processId')->references('processId')->on('pdfSplit')->name('pdf_split_fk');
            $table->foreign('groupId')->references('groupId')->on('pdfSplit')->name('pdf_group_split_fk');
            $table->foreign('processId')->references('processId')->on('pdfCnv')->name('pdf_cnv_fk');
            $table->foreign('groupId')->references('groupId')->on('pdfCnv')->name('pdf_group_cnv_fk');
            $table->foreign('processId')->references('processId')->on('pdfWatermark')->name('pdf_watermark_fk');
            $table->foreign('groupId')->references('groupId')->on('pdfWatermark')->name('pdf_group_watermark_fk');
            $table->foreign('processId')->references('processId')->on('pdfHtml')->name('pdf_html_fk');
            $table->foreign('groupId')->references('groupId')->on('pdfHtml')->name('pdf_group_html_fk');
            $table->foreign('processId')->references('processId')->on('jobLogs')->name('job_logs_fk');
            $table->foreign('processId')->references('processId')->on('notifyLogs')->name('notify_logs_fk');
            $table->foreign('processId')->references('processId')->on('fileModel')->name('file_model_fk');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('appLogs');
    }
};
