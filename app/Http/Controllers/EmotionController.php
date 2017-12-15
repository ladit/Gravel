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
use Storage;

class EmotionController extends Controller
{
    private $emotions = '';
    private $all = '';

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
                $emotion->coefficient = $this->all;
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
        $this->all = $result[0][1];
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
        $emotion_id = $this->emotionAnalysis($this->emotions, $this->all);
        $note_emotions_obj = $note_emotions->where('note_id', $id)->get();
        if (is_null($note_emotions_obj)) {
            # code...
            $note_emotions->note_id = $note_data['id'];
            $note_emotions->emotion_id = $emotion_id;
            $note_emotions->coefficient = $this->all;
            $note_emotions->save();    //新建
        } else {
            $note_emotions->emotion_id = $emotion_id;
            $note_emotions->coefficient = $this->all;
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
        $this->all = $result[0][1];
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
        $emotion_id = $this->emotionAnalysis($this->emotions, $this->all);
        if ($article_emotions->where('article_id', $article_data['id'])->count() == 0) {
            # code...
            $article_emotions->article_id = $article_data['id'];
            $article_emotions->emotion_id = $emotion_id;
            $article_emotions->coefficient = $this->all;
            $article_emotions->save();
            curl_close($ch);
            return 1;
        } else {
            curl_close($ch);
            return 0;
        }
    }

    /**
     * 提取每一篇关键词,并提取前100个比较关键的关键词  beta阶段
     * @param  [type]  [description]
     * @return [type]     [description]
     */
    public function getKeywords()
    {
        $article = Article::all();  //所有的文章

        $k = 0;
        $cnt = 0;
        $keywords = array();    //保存文章关键词和id
        $API_TOKEN = "vHs8jUdL.21435.-rzExKceLCCM";  //key 1DwUEeoy.19320.SIh8ADsPbOAT   _Y4fJqQe.21418.nlZL0Vunn9tY  vHs8jUdL.21435.-rzExKceLCCM
       //对每一篇文章进行分析
        foreach ($article as $key => $content) {
            # code...
            if ( $content != null) {
                # code...
                 $data =  $content;
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

                $result = curl_exec($ch);
                // var_dump(json_decode($result));
                curl_close($ch);
                $keywords[$cnt] = array();
                $keywords[$cnt]['id'] = $article_id[$k];
                $keywords[$cnt]['key'] = $result;
                $cnt++;
            }
            if ($k == 250) {
                # code...
                $API_TOKEN = "1DwUEeoy.19320.SIh8ADsPbOAT";    //文章篇数过多 
            } elseif ($k == 500) {
                # code...
                $API_TOKEN = "_Y4fJqQe.21418.nlZL0Vunn9tY";
            }
            $k++;
        }
        //写入到allkeyword.txt文件中去
        Storage::disk('local')->put('allkeyword.txt',json_encode($keywords));

        $str = [];
        $k = 0;
        //将所有的关键词处理 处理掉英文 还有奇奇怪怪的词汇  
        foreach ($keywords as $key => $value) {
            # code...
            $content = json_decode($value->key);
            for ($i=0; $i < sizeof($content) ; $i++) { 
                # code....
                //echo $t[$i][1].'<br>';
                if (strlen($content[$i][1])!=3 && !preg_match ("/^[A-Za-z]/", $content[$i][1]) && !strstr("二维码", $content[$i][1])) {
                    # code...
                    $str[$k++] = $content[$i][1];
                    // echo strlen($content[$i][1]);
                    // exit;
                }
            }
        }

        $data = implode(',', $str);  //将数组转化为字符串
        $API_TOKEN = "_Y4fJqQe.21418.nlZL0Vunn9tY";  //key  1DwUEeoy.19320.SIh8ADsPbOAT 
        /**
         *关键提取
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

        $result = curl_exec($ch);
        curl_close($ch);
        //将100个关键词存入到keywords.txt中去
        Storage::disk('local')->put('keywords.txt',$result);
    }

    /**
     * 个性化推送文章  beta阶段
     * @param  [type] $input_article  [description]  传入一篇所阅读到的文章，对其进行分析 然后推送
     * @return [type]  json   [description] 返回给用户所推送文章
     */
    public function pushArticle(Request $request,Article $input_article)
    {
        header('content-type:text/html;charset=utf-8');
        $myfile = Storage::get('allkeyword.txt');
        //var_dump(json_decode($cnt));
        $every_article_key = json_decode($myfile);   //从文件读取到的东西 我们转化为数组形式  
        //var_dump($every_article_key);
        // echo sizeof($every_article_key);
        //主要的100个标签
        $myfile = Storage::get('keywords.txt');   
        $keywords = json_decode($myfile);
        //var_dump($keywords);
        $key_content = array();

        foreach ($keywords as $key => $value) {
            # code...
            $key_content[$key] = $value[1];
        }
        // print_r($key_content);
        // exit;
        $matrix = array();  //01矩阵
        $article = array();  //文章
        //构建01矩阵
        foreach ($every_article_key as $key => $value) {
            # code...
            $k = 0;
            $str = array();
            $content = json_decode($value->key);
            $article[$value->id] =  $value->id;
            // 获取整篇文章的关键字
            for ($i=0; $i < sizeof($content); $i++) { 
                # code...
                            # code....
                //echo $t[$i][1].'<br>';
                if (strlen($content[$i][1]) !=3 && !preg_match ("/^[A-Za-z]/", $content[$i][1]) && !strstr("二维码", $content[$i][1])) {
                    # code...
                    $str[$k++] = $content[$i][1];
                }
            }
            $article_key = implode(',', $str);
            // echo $article_key;
            // exit;
            $matrix[$value->id] = array();   
            foreach ($key_content as $k_id => $k_content) {
                # code...   
                if ( strstr($article_key,$k_content) ) {
                    # code...
                    $matrix[$value->id][$k_id] = 1;
                } else {
                    # code...
                    $matrix[$value->id][$k_id] = 0;
                }
            }
        }

        //计算相似度  简单的算法部分
        $cos = array();
        $article_id = $input_article->id; 
        $current_arc = array();
        $current_arc = $matrix[$article_id];
        for ($i= 1; $i < sizeof($article) ; $i++) { 
            # code...
            if ($i == $article_id ) {
                # code...
                $cos[$i] = 1.00;
                continue;
            }
            if ($i == 122) {
                # code...
                $cos[$i] = 0.00;
                continue;
            }
            $vec_numerator = 0;                     //分子
            $vec_denominator = 0;                    //分母
            for ($j=0; $j < sizeof($matrix[$i]); $j++) { 
                # code...
                $vec_numerator = $vec_numerator + $current_arc[$j]*$matrix[$i][$j];
                $vec_denominator = $vec_denominator +  ($current_arc[$j]-$matrix[$i][$j])*($current_arc[$j]-$matrix[$i][$j]);
            }
            if ($vec_denominator == 0) {
                # code...
                $cos[$i] = 0.0;
                continue;
            }
            $cos[$i] = abs($vec_numerator) / sqrt($vec_denominator) >= 1 ? abs($vec_numerator) / sqrt($vec_denominator) -1:abs($vec_numerator) / sqrt($vec_denominator);   //相似度
        }
        $min = 1.1;
        $return_id = array();
        for ($i=1; $i < sizeof($cos); $i++) { 
            # code...
            if ($i <= 10) {
                # code...
                 $return_id[$i] = $i;
                 if ($cos[$i] < $min) {
                    # code...
                    $min = $cos[$i];
                 }
            } else {
                if($cos[$i] > $min) {
                    // var_dump($return_id);
                    $flag = array();
                    $tmp = $min;
                    $min = $cos[$i];
                    for ($j=1 ; $j <= 10; $j++) { 
                        # code..
                        if ($cos[$return_id[$j]] < $min) {
                            # code...
                            $min = $cos[$return_id[$j]];
                        }
                        if($cos[$return_id[$j]] == $tmp) {
                            if (in_array($i, $return_id) == true) {
                                # code...
                                continue;
                            }
                            $return_id[$j] = $i;
                        }
                    }
                }
            }
        }

        //查找前10篇与当前文章相似的文章 并返回给用户
        $toReturn = array();
        $select_article  = Article::whereIn('id',$return_id)->get(['id','url','content']);
        $key = 0;
        foreach ($select_article as $key => $value) {
            # code...
            $toReturn[$key]['id'] = $value->id;
            $toReturn[$key]['url'] = $value->url;
            if ($value->content) {
                    $toReturn[$key]['need_dedication'] = false;
                } else {
                    $toReturn[$key]['need_dedication'] = true;
            }
            $key++;
        }  
        return response()->json([
            'error_code' => 200,
            'meteors' => $toReturn
        ]);
    }
}