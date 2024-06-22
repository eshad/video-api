<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Video extends Model
{
    protected $table = 'cmf_video';
    public $timestamps = false;

    protected $fillable = [
        'id', 'title', 'href', 'thumb', 'classid', 'updated_at'
    ];
}
