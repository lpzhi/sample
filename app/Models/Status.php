<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Status extends Model
{

    protected $fillable = ['content']; //指定可以更新的字段
    //
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
