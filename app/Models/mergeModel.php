<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MergeModel extends Model
{
    use HasFactory;

    protected $table = 'pdfMerge';

    protected $fillable = [
        'mergeId',
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
