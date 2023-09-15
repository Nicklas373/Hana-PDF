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
        Schema::create('html_pdfs', function (Blueprint $table) {
            $table->id();
            $table->string('urlName');
            $table->string('hostName');
            $table->boolean('result');
            $table->string('err_reason')->nullable();
            $table->string('err_api_reason')->nullable();
            $table->string('uuid');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('html_pdfs');
    }
};
