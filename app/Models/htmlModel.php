<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class htmlModel extends Model
{
    use HasFactory;

    protected $connection = 'pgsql';
    protected $table = 'pdfHtml';
    protected $primaryKey = 'htmlId';
    protected $keyType = 'string';

    protected $fillable = [
        'urlName',
        'urlMargin',
        'urlOrientation',
        'urlSinglePage',
        'urlSize',
        'result',
        'fileId',
        'groupId',
        'processId',
        'isDeleted',
        'deletedAt',
        'procStartAt',
        'procEndAt',
        'procDuration'
    ];
}
