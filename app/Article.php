<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Article extends Model
{
    /**
     * 关联用户
     *
     * @param
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function users()
    {
        return $this->belongsToMany('App\User', 'users_articles');
    }

    /**
     * 关联情绪
     *
     * @param
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function emotions()
    {
        return $this->belongsToMany('App\Emotion', 'articles_emotions');
    }
}
