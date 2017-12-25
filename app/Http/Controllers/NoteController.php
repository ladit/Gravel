<?php

namespace App\Http\Controllers;

use App\User;
use App\Note;
use App\Message;
use Illuminate\Http\Request;

class NoteController extends Controller
{
    /**
     * 恢复记录
     * /users/:id/notes?all=0&year=2017&month=12&day=05
     * 若 all=1，返回所有记录
     * 若 all=0，返回 year、month、day 指定的记录，年4位，月日2位
     * all 默认值为 0
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \App\User $user
     * @return \Illuminate\Http\Response
     */
    public function restore(Request $request, User $user)
    {
        $toReturnNotes = [];
        $getAll = $request->query('all', '1');
        if ($getAll == 0) {
            $year = $request->query('year');
            $month = $request->query('month');
            $day = $request->query('day');
            $dateString = $year.'-'.$month.'-'.$day;
            if($dateString !== date('Y-m-d', strtotime($dateString))) {
                return response()->json([
                    'error_code' => 400,
                    'error_message' => 'Wrong date format.'
                ]);
            }
            $notes = $user->notes()->where([
                ['is_deleted', '=', '0'],
                ['is_blocked', '=', '0']
            ])->whereDate('created_at', $dateString)->get();
        }
        else {
            $notes = $user->notes()->where([
                ['is_deleted', '=', '0'],
                ['is_blocked', '=', '0']
            ])->get();
        }
        foreach ($notes as $key => $note) {
            $toReturnNotes[$key]['id'] = $note->id;
            $toReturnNotes[$key]['url'] = $note->url;
            $toReturnNotes[$key]['create_time'] = $note->created_at->toDateTimeString();
            $toReturnNotes[$key]['upvote_quantity'] = $note->upvote_quantity;
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
     * @param  \Illuminate\Http\Request $request
     * @param  \App\User $user
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
     * @param  \Illuminate\Http\Request $request
     * @param  \App\User $user
     * @param  \App\Note $note
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
     * @param  \Illuminate\Http\Request $request
     * @param  \App\User $user
     * @param  \App\Note $note
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
     * @param  \Illuminate\Http\Request $request
     * @param  \App\User $user
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
            $toReturnMeteors[$key]['upvote_quantity'] = $meteor->upvote_quantity;
            $toReturnMeteors[$key]['content'] = $meteor->content;
        }

        return response()->json([
            'error_code' => 200,
            'meteors' => $toReturnMeteors
        ]);
    }

    /**
     * 点亮流星
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \App\User $user
     * @param  \App\Note $note
     * @return \Illuminate\Http\Response
     */
    public function upvoteMeteor(Request $request, User $user, Note $note)
    {
        $upvoteQuantity = $note->upvote_quantity + 1;
        $note->upvote_quantity = $upvoteQuantity;
        $note->save();

        // 新增 Message
        $message = new Message();
        $message->note_id = $note->id;
        $message->user_id = $note->user->id;
        $message->is_upvoted = 1;
        $message->save();

        return response()->json([
            'error_code' => 200,
            'meteor' => [
                'id' => $note->id,
                'upvote_quantity' => $note->upvote_quantity,
                'url' => $note->url
            ]
        ]);
    }

    /**
     * 取消点亮流星
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \App\User $user
     * @param  \App\Note $note
     * @return \Illuminate\Http\Response
     */
    public function cancelUpvoteMeteor(Request $request, User $user, Note $note)
    {
        $upvoteQuantity = $note->upvote_quantity - 1;
        $note->upvote_quantity = $upvoteQuantity;
        $note->save();

        return response()->json([
            'error_code' => 200,
            'meteor' => [
                'id' => $note->id,
                'upvote_quantity' => $note->upvote_quantity,
                'url' => $note->url
            ]
        ]);
    }

    /**
     * 举报流星
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \App\User $user
     * @param  \App\Note $note
     * @return \Illuminate\Http\Response
     */
    public function reportMeteor(Request $request, User $user, Note $note)
    {
        $note->is_reported = 1;
        // 管理员处理后取消此标记
        $note->save();

        // 新增 Message
        $message = new Message();
        $message->note_id = $note->id;
        $message->user_id = $note->user->id;
        $message->is_reported = 1;
        $message->save();

        return response()->json([
            'error_code' => 200,
            'meteor' => [
                'id' => $note->id,
                'url' => $note->url
            ]
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
                if (is_null($data)) {
                    return 'null';
                }
                if (!is_bool($data)) {
                    return 'NotNullNotBool';
                }
                return true;
                break;

            case 'timestamp':
                // 字符串为 timestamp
                if (date('Y-m-d H:i:s', strtotime($data)) === $data) {
                    return true;
                }
                return false;
                break;

            default:

                break;
        }
    }
}
