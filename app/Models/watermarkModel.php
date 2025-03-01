<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class watermarkModel extends Model
{
    use HasFactory;

    protected $connection = 'pgsql';
    protected $table = 'pdfWatermark';
    protected $primaryKey = 'watermarkId';
    protected $keyType = 'string';

    protected $fillable = [
        'fileName',
        'fileSize',
        'watermarkFontFamily',
        'watermarkFontStyle',
        'watermarkFontSize',
        'watermarkFontTransparency',
        'watermarkImage',
        'watermarkLayout',
        'watermarkMosaic',
        'watermarkRotation',
        'watermarkStyle',
        'watermarkText',
        'watermarkPage',
        'result',
        'isBatch',
        'isDeleted',
        'fileId',
        'batchName',
        'groupId',
        'processId',
        'deletedAt',
        'procStartAt',
        'procEndAt',
        'procDuration'
    ];
}
