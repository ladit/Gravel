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

        if (!$this->check('string', $url)
            or !$this->check('timestamp', $create_time)
            or !$this->check('string', $content)
            or $this->check('bool', $share) !== true) {
            return response()->json([
                'error_code' => 400,
                'error_message' => 'Require url, create time, share and content.'
            ]);
        }
        $note = new Note;
        $note->user_id = $user->id;
        $note->url = $url;
        $note->created_at = $create_time;
        if ($share === true) {
            $note->is_shared = 1;
        } elseif ($share === false) {
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

        if ($this->check('string', $url) and !$this->check('string', $content)) {
            return response()->json([
                'error_code' => 400,
                'error_message' => 'Missing content.'
            ]);
        }

        if (!$this->check('string', $url) and $this->check('string', $content)) {
            return response()->json([
                'error_code' => 400,
                'error_message' => 'Missing url.'
            ]);
        }

        if ($this->check('bool', $share) === 'NotNullNotBool') {
            return response()->json([
                'error_code' => 400,
                'error_message' => 'If exists, share should be bool.'
            ]);
        }

        if ($this->check('string', $url) and $this->check('string', $content)) {
            $note->url = $url;
            $note->content = $content;
            $changedContent = true;
        }

        if ($this->check('bool', $share) === true) {
            if ($share === true) {
                $note->is_shared = 1;
            } elseif ($share === false) {
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

    /**
     * 格式检查
     *
     * @param string $action
     * @param mixed $data
     * @return bool
     */
    public function check($action, $data)
    {
        switch ($action) {
            case 'string':
                // 字符串为 null 或 ""
                if (is_null($data) or strlen($data) === 0) {
                    return false;
                }
                return true;
                break;

            case 'bool':
                // 检查是否 bool 类型
                if(is_null($data)) {
                    return 'null';
                }
                if(!is_bool($data)) {
                    return 'NotNullNotBool';
                }
                return true;
                break;

            case 'timestamp':
                // 字符串为 timestamp
                if(strtotime(date('m-d-Y H:i:s',$data)) === $data) {
                    return true;
                }
                return false;
                break;

            default:

                break;
        }
    }
}
