<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class messages extends Model
{
    protected $table = 'messages';
    protected $guarded = [];

    public function conversations()
    {
        return $this->belongsTo(conversations::class);
    }
}