<?php

namespace App\Http\Controllers;

use App\User;
use App\Note;
use App\Article;
use App\Emotion;
use App\UserEmotion;
use App\NoteEmotion;
use App\ArticleEmotion;
use Illuminate\Http\Request;

class EmotionController extends Controller
{
    private $emotions = '';
    private $key = 0.0;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return Emotion::all(); //bad
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Emotion $emotion
     * @return \Illuminate\Http\Response
     */
    public function show(Emotion $emotion)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Emotion $emotion
     * @return \Illuminate\Http\Response
     */
    public function edit(Emotion $emotion)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \App\Emotion $emotion
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Emotion $emotion)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Emotion $emotion
     * @return \Illuminate\Http\Response
     */
    public function destroy(Emotion $emotion)
    {
        //
    }

    /**
     * 情绪分析
     * 情绪表只要根据刚更新article_emotions 或者 note_emotions 来判断要不要插入删除
     * @param  [type] $note_emotions    [description]
     * @param  [type] $article_emotions [description]
     * @return [type]                   [description]
     */
    public function emotionAnalysis()
    {
        if ($this->emotions == '') {
            return 0;
        } else {
            $emotion = new Emotion();
            $result = $emotion->where('content', $this->emotions)->count();
            if ($result != 0) {
                $new_result = $emotion->where('content', $this->emotions)->first();
                return $new_result->id;
            } else {
                $emotion->content = $this->emotions;
                $emotion->coefficient = $this->key;
                $emotion->save();
            }
            $new_result = $emotion->where('content', $this->emotions)->first();
            return $new_result->id;
        }
    }

    /**
     *note的分析，实时更新，就是每次插入一条数据的时候就直接分析该条note的情绪，并更新note的情绪表。
     *然后更新情绪表后就去添加到emotions总表中去  ,这里要数组形式传入$note_data  user_id
     * @param  [type]  $note_data [description]
     * @param  integer $id [description]
     * @return [type]             [description]
     */
    public function noteAnalysis($id)
    {
        $note = new Note();    //记录
        $note_data = $note->where('id', $id)->first();
        $note_content = $note_data['content'];
        $note_emotions = new Notes_emotions();  //记录的情绪表
        $API_TOKEN = "1DwUEeoy.19320.SIh8ADsPbOAT";  //key
        $data = $note_content;
        $SENTIMENT_URL = 'http://api.bosonnlp.com/sentiment/analysis';   //情绪分析  只能分析到负面概率 和非负面概率
        $ch = curl_init();
        curl_setopt_array($ch, array(
            CURLOPT_URL => $SENTIMENT_URL,
            CURLOPT_HTTPHEADER => array(
                "Accept:application/json",
                "Content-Type: application/json",
                "X-Token: $API_TOKEN",
            ),
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_RETURNTRANSFER => true,
        ));
        $result = json_decode(curl_exec($ch));
        $this->key = $result[0][1];
        curl_close($ch);
        /**
         *关键提取
         */
        $SENTIMENT_URL = 'http://api.bosonnlp.com/keywords/analysis';   //提取关键词  按权重去排序
        $ch = curl_init();
        curl_setopt_array($ch, array(
            CURLOPT_URL => $SENTIMENT_URL,
            CURLOPT_HTTPHEADER => array(
                "Accept:application/json",
                "Content-Type: application/json",
                "X-Token: $API_TOKEN",
            ),
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($data, JSON_UNESCAPED_UNICODE),
            CURLOPT_RETURNTRANSFER => true,
        ));
        $result = json_decode(curl_exec($ch));   //提取前5个关键词 弄成字符串然后加入到数据库中去
        $this->emotions = '';
        for ($i = 0; $i < 5; $i++) {
            # code...[]
            if ($i == 0) {
                $this->emotions = $result[$i][1];
            } else {
                $this->emotions = $this->emotions . ',' . $result[$i][1];
            }
        }
        $emotion_id = $this->emotionAnalysis($this->emotions, $this->key);
        $note_emotions_obj = $note_emotions->where('note_id', $id)->get();
        if (is_null($note_emotions_obj)) {
            # code...
            $note_emotions->note_id = $note_data['id'];
            $note_emotions->emotion_id = $emotion_id;
            $note_emotions->coefficient = $this->key;
            $note_emotions->save();    //新建
        } else {
            $note_emotions->emotion_id = $emotion_id;
            $note_emotions->coefficient = $this->key;
            $note_emotions->save();  //更新
        }
        curl_close($ch);
    }

    /**
     * 文章情绪分析函数
     * @param  [type] $article_data [description]
     * @return [type]               [description]
     */
    public function aritcleAnalysis($article_data)
    {
        $article = new Article();  //文章
        $article_title = $article_data['title'];
        $article_content = $article_data['content'];
        $article_emotions = new Articles_emotions();  //文章情绪
        $API_TOKEN = "1DwUEeoy.19320.SIh8ADsPbOAT";  //key
        $data = $article_content;
        $SENTIMENT_URL = 'http://api.bosonnlp.com/sentiment/analysis';   //情绪分析  只能分析到负面概率 和非负面概率
        $ch = curl_init();
        curl_setopt_array($ch, array(
            CURLOPT_URL => $SENTIMENT_URL,
            CURLOPT_HTTPHEADER => array(
                "Accept:application/json",
                "Content-Type: application/json",
                "X-Token: $API_TOKEN",
            ),
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_RETURNTRANSFER => true,
        ));
        $result = json_decode(curl_exec($ch));//提取前5个关键词 弄成字符串然后加入到数据库中去
        $this->key = $result[0][1];
        curl_close($ch);
        /**
         * 关键提取
         **/
        $SENTIMENT_URL = 'http://api.bosonnlp.com/keywords/analysis';   //提取关键词  按权重去排序
        $ch = curl_init();
        curl_setopt_array($ch, array(
            CURLOPT_URL => $SENTIMENT_URL,
            CURLOPT_HTTPHEADER => array(
                "Accept:application/json",
                "Content-Type: application/json",
                "X-Token: $API_TOKEN",
            ),
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($data, JSON_UNESCAPED_UNICODE),
            CURLOPT_RETURNTRANSFER => true,
        ));
        $result = json_decode(curl_exec($ch));//提取前5个关键词 弄成字符串然后加入到数据库中去
        $this->emotions = '';
        for ($i = 0; $i < 5; $i++) {
            # code...[]
            if ($i == 0) {
                $this->emotions = $result[$i][1];
            } else {
                $this->emotions = $this->emotions . ',' . $result[$i][1];
            }
        }
        $emotion_id = $this->emotionAnalysis($this->emotions, $this->key);
        if ($article_emotions->where('article_id', $article_data['id'])->count() == 0) {
            # code...
            $article_emotions->article_id = $article_data['id'];
            $article_emotions->emotion_id = $emotion_id;
            $article_emotions->coefficient = $this->key;
            $article_emotions->save();
            curl_close($ch);
            return 1;
        } else {
            curl_close($ch);
            return 0;
        }
    }

    /**
     * 用户情绪更新  一天分析一次
     * @param  [type] $id [description]
     * @return [type]     [description]
     */
    public function updateEmotion($id)
    {
        $note_emotions = new Notes_emotions();  //记录的情绪表  用户的情绪表可以根据用户的记录体现出来
        $user_emotions = new Users_emotions();  //用户情绪表
        $note = new Note();    //note表

    }

    /**
     * 用户情绪与文章的匹配   beta阶段
     * @param  [type] $id [description]
     * @return [type]     [description]
     */
    public function matchArticle($id)
    {
        $user_emotions = new Users_emotions();    //该用户的情绪表
        $today_emotions = $user_emotions->where('user_id', $id)->orderBy('modify_at', 'DESC')->first();   //获取该用户最新的情绪
        if ($today_emotions == null) {
            return "<script>alert('用户不存在');location.href='http://111.231.18.37/learnlaravel5/public';</script>";
        }
        $article_emotions = new Articles_emotions();   //文章情绪表
        $article = new Article();    //文章   文章情绪表映射到文章
        $result = $article_emotions->where('emotion_id', $today_emotions->emotion_id)->orderBy(\DB::raw('RAND()'))->take(10)->get(['article_id']);   //随机匹配数据
        if ($result == null) {
            $article_list = $article->orderBy(\DB::raw('RAND()'))->take(10)->get(['id', 'title', 'author', 'publish_time', 'url', 'content']);
        } else {
            $article_list = $article->whereIn('id', $result)->get(['id', 'title', 'author', 'publish_time', 'url', 'content']);
        }
        return json_encode($article_list);
    }
}