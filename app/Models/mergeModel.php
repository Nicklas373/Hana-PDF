<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class mergeModel extends Model
{
    use HasFactory;

    protected $table = 'pdfMerge';

    protected $fillable = [
        'fileName',
        'fileSize',
        'result',
        'isBatch',
        'batchId',
        'procStartAt',
        'procEndAt',
        'procDuration',
        'isReport'
    ];
}
