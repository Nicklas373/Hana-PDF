<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class cnvModel extends Model
{
    use HasFactory;

    protected $connection = 'pgsql';
    protected $table = 'pdfConvert';
    protected $primaryKey = 'cnvId';
    protected $keyType = 'string';

    protected $fillable = [
        'fileName',
        'fileSize',
        'container',
        'imgExtract',
        'result',
        'isBatch',
        'isDeleted',
        'batchName',
        'fileId',
        'groupId',
        'processId',
        'deletedAt',
        'procStartAt',
        'procEndAt',
        'procDuration'
    ];
}
