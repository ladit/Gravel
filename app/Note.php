<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Note extends Model
{
    /**
     * 关联用户
     *
     * @param
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo('App\User');
    }

    /**
     * 关联情绪
     *
     * @param
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function emotions()
    {
        return $this->belongsToMany('App\Emotion', 'notes_emotions');
    }
}
