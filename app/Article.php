<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Article extends Model
{
    /**
     * 关联阅读文章用户
     *
     * @param
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function readUsers()
    {
        return $this->belongsToMany('App\User', 'users_articles');
    }

    /**
     * 关联收藏文章用户
     *
     * @param
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function favoritedUsers()
    {
        return $this->belongsToMany('App\User', 'users_favorite_articles');
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
