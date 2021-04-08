<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StreamLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'stream_id',
        'status',
        'payload'
    ];



    public function stream()
    {
        return $this->belongsTo(Stream::class);
    }
}
