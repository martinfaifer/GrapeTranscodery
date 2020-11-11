<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StreamKvality extends Model
{
    use HasFactory;

    protected $fillable = [
        'format_id',
        'kvalita',
        'minrate',
        'maxrate',
        'bitrate',
        'scale'
    ];
}
