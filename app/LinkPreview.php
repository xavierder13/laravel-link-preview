<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class LinkPreview extends Model
{
    protected $fillable = [
        'title', 'url', 'description', 'file_name', 'file_type', 'file_path',
    ];
}
