<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class conversations extends Model
{
    protected $table = 'conversations';
    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function messages()
    {
        return $this->hasMany(messages::class);
    }
}
