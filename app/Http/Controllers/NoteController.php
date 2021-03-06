<?php

namespace App\Http\Controllers;

use App\User;
use App\Note;
use App\Emotion;
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
        $notes = $user->notes()->where([
            ['is_deleted', '=', '0'],
            ['is_blocked', '=', '0']
        ])->get();
        $toReturnNotes = [];
        foreach ($notes as $key => $note) {
            $toReturnNotes[$key]['id'] = $note->id;
            $toReturnNotes[$key]['url'] = $note->url;
            $toReturnNotes[$key]['create_time'] = $note->created_at->toDateTimeString();
            if ($note->is_shared) {
                $toReturnNotes[$key]['share'] = true;
            } else {
                $toReturnNotes[$key]['share'] = false;
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
        $url = $request->input('url');
        $create_time = $request->input('create_time');
        $share = $request->input('share');
        $content = $request->input('content');

        if (is_null($url) or is_null($create_time) or is_null($share) or is_null($content)) {
            return response()->json([
                'error_code' => 400,
                'error_message' => 'Require url, create time, share and content.'
            ]);
        }
        $note = new Note;
        $note->user_id = $user->id;
        $note->url = $url;
        $note->created_at = $create_time;
        if ($share) {
            $note->is_shared = 1;
        } else {
            $note->is_shared = 0;
        }
        $note->content = $content;
        $note->save();

        /*
        // 记录分析
        Emotion::NoteAnalysis($note);
        */

        return response()->json([
            'error_code' => 200,
            'note' => [
                'id' => $note->id,
                'url' => $url
            ]
        ]);
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
        $url = $request->input('url');
        $share = $request->input('share');
        $content = $request->input('content');
        $changedContent = false;
        $changedShareStatus = false;
        $toReturnNote = [];
        
        if (!is_null($url) and is_null($content)) {
            return response()->json([
                'error_code' => 400,
                'error_message' => 'Missing content.'
            ]);
        }

        if (is_null($url) and !is_null($content)) {
            return response()->json([
                'error_code' => 400,
                'error_message' => 'Missing url.'
            ]);
        }

        if (!is_null($url) and !is_null($content)) {
            $note->url = $url;
            $note->content = $content;
            $changedContent = true;
        }
        if (!is_null($share)) {
            if ($share) {
                $note->is_shared = 1;
            } else {
                $note->is_shared = 0;
            }
            $changedShareStatus = true;
        }
        
        if ($changedContent or $changedShareStatus) {
            $note->save();
            $toReturnNote['id'] = $note->id;
        }

        /*
        // 记录分析
        if ($changedContent) {
            Emotion::NoteAnalysis($note);
        }
        */
        
        if ($changedContent) {
            $toReturnNote['url'] = $url;
        }
        if ($changedShareStatus) {
            $toReturnNote['share'] = $share;
        }
        
        return response()->json([
            'error_code' => 200,
            'note' => $toReturnNote
        ]);
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
        $note->is_deleted = 1;
        $note->save();

        return response()->json([
            'error_code' => 200,
            'note' => [
                'id' => $note->id,
                'url' => $note->url
            ]
        ]);
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
        $meteors = Note::where([
            ['is_shared', '=', '1'],
            ['is_deleted', '=', '0'],
            ['is_blocked', '=', '0'],
            //['user_id', '<>', $user->id],
        ])->inRandomOrder()->take(20)->get();

        $toReturnMeteors = [];
        foreach ($meteors as $key => $meteor) {
            $toReturnMeteors[$key]['id'] = $meteor->id;
            $toReturnMeteors[$key]['url'] = $meteor->url;
            $toReturnMeteors[$key]['create_time'] = $meteor->created_at->toDateTimeString();
            $toReturnMeteors[$key]['content'] = $meteor->content;
        }

        return response()->json([
            'error_code' => 200,
            'meteors' => $toReturnMeteors
        ]);
    }
}
