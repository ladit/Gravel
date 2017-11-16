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
    public function notes()
    {
        return $this->belongsTo('App\User');
    }
}
