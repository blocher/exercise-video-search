<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
    public $fillable = ['name', 'description'];

    public function videos()
    {
        return $this->belongsToMany('\App\Video');
    }
}
