<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Stream extends Model
{
    use HasFactory;


    protected $fillable = [
        'pid',
        'nazev',
        'src',
        'dst',
        'dst1_resolution',
        'dst2',
        'dst2_resolution',
        'dst3',
        'dst3_resolution',
        'dst4',
        'dst4_resolution',
        'format',
        'script',
        'transcoder',
        'status'
    ];
}
