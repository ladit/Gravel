<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    /**
     * 关联记录
     *
     * @param
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function notes()
    {
        return $this->hasMany('App\Note');
    }

    /**
     * 关联阅读文章系数表
     *
     * @param
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function readArticles()
    {
        return $this->belongsToMany('App\Article', 'users_articles')->withPivot('coefficient')->withTimestamps();
    }

    /**
     * 关联收藏文章表
     *
     * @param
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function favoriteArticles()
    {
        return $this->belongsToMany('App\Article', 'users_favorite_articles')->withTimestamps();
    }
}
