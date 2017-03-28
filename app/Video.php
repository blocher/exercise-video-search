<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Video extends Model
{

  public $fillable = ['youtube_id', 'etag', 'video_id', 'published_at', 'title', 'description', 'position', 'duration', 'view_count', 'like_count', 'dislike_count', 'favorite_count', 'comment_count'];

  public $dates = ['published_at'];


  public function tags()
  {
      return $this->belongsToMany('\App\Tag');
  }

}
