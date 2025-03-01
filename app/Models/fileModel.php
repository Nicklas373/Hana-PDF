<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class fileModel extends Model
{
    use HasFactory;

    protected $connection = 'pgsql';
    protected $primaryKey = 'fileId';
    protected $table = 'filePool';
    protected $keyType = 'string';

    protected $fillable = [
        'fileId',
        'fileName',
        'fileSize',
        'isDeleted',
        'fileId',
        'processId',
        'isDeleted',
        'deletedAt'
    ];
}
