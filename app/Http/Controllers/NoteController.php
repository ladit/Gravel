<?php

namespace App\Http\Controllers;

use App\User;
use App\Note;
use Illuminate\Http\Request;

class NoteController extends Controller
{
    /**
     * 恢复记录
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\User  $user
     * @return \Illuminate\Http\Response
     */
    public function restore(Request $request, User $user)
    {
        $notes = $user->notes;
        $toReturnNotes = [];
        foreach ($notes as $key => $note) {
            if (!$note->is_deleted and !$note->is_blocked) {
                $toReturnNotes[$key]['id'] = $note->id;
                $toReturnNotes[$key]['url'] = $note->url;
                $toReturnNotes[$key]['create_time'] = $note->created_at;
                if ($note->is_shared) {
                    $toReturnNotes[$key]['share'] = true;
                } else {
                    $toReturnNotes[$key]['share'] = false;
                }
            }
        }
        return response()->json([
            'error_code' => 200,
            'notes' => $toReturnNotes
        ]);
    }

    /**
     * 存储记录
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\User  $user
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, User $user)
    {
        return $user;
    }

    /**
     * 修改记录
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\User  $user
     * @param  \App\Note  $note
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, User $user, Note $note)
    {
        //
    }

    /**
     * 删除记录
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\User  $user
     * @param  \App\Note  $note
     * @return \Illuminate\Http\Response
     */
    public function delete(Request $request, User $user, Note $note)
    {
        //
    }

    /**
     * 获取流星
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\User  $user
     * @return \Illuminate\Http\Response
     */
    public function getMeteors(Request $request, User $user)
    {
        //
    }
}
