<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Emotion extends Model
{
    /**
     * 关联用户
     *
     * @param
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function users()
    {
        return $this->belongsToMany('App\User', 'users_emotions');
    }

    /**
     * 关联记录
     *
     * @param
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function notes()
    {
        return $this->belongsToMany('App\Note', 'notes_emotions');
    }

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
