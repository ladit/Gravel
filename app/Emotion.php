<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Emotion extends Model
{
    /**
     * 关联文章
     *
     * @param
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function articles()
    {
        return $this->belongsToMany('App\Article', 'articles_emotions');
    }
}
