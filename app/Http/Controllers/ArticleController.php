<?php

namespace App\Http\Controllers;

use App\User;
use App\Article;
use App\Emotion;
use Illuminate\Http\Request;

class ArticleController extends Controller
{
    /**
     * 获取文章，若 all_random=1，返回的都是随机文章，默认值为 1
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function get(Request $request, User $user)
    {
        $toReturnArticles = [];
        $allRandom = $request->query('all_random', '1');
        if ($allRandom) {
            $articles = Article::inRandomOrder()->take(10)->get();
            foreach ($articles as $key => $article) {
                $toReturnArticles[$key]['id'] = $article->id;
                $toReturnArticles[$key]['url'] = $article->url;
                if ($article->content) {
                    $toReturnArticles[$key]['need_dedication'] = false;
                } else {
                    $toReturnArticles[$key]['need_dedication'] = true;
                }
            }
        } else {
            // 向匹配数据库中取 8 个，随机取 2 个
            $key = 0;
            /*
            $suggestedArticles = UserArticle::take(8)->get();
            foreach ($suggestedArticles as $suggestedArticle) {
                $toReturnArticles[$key]['id'] = $suggestedArticle->id;
                $toReturnArticles[$key]['url'] = $suggestedArticle->url;
                $toReturnArticles[$key]['need_dedication'] = false;
                $key++;
            }
            */
            $randomArticles = Article::inRandomOrder()->take(2)->get();
            foreach ($randomArticles as $randomArticle) {
                $toReturnArticles[$key]['id'] = $randomArticle->id;
                $toReturnArticles[$key]['url'] = $randomArticle->url;
                if ($randomArticle->content) {
                    $toReturnArticles[$key]['need_dedication'] = false;
                } else {
                    $toReturnArticles[$key]['need_dedication'] = true;
                }
                $key++;
            }
        }

        return response()->json([
            'error_code' => 200,
            'meteors' => $toReturnArticles
        ]);
    }

    /**
     * 更新文章
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, User $user, Article $article)
    {
        $url = $request->input('url');
        $publish_time = $request->input('publish_time');
        $author = $request->input('author');
        $title = $request->input('title');
        $content = $request->input('content');

        if (is_null($url) or is_null($publish_time) or is_null($author)
            or is_null($title) or is_null($content)) {
            return response()->json([
                'error_code' => 400,
                'error_message' => 'Require url, publish time, author, title and content.'
            ]);
        }

        $article->url = $url;
        $article->publish_time = $publish_time;
        $article->author = $author;
        $article->title = $title;
        $article->content = $content;
        $article->save();

        /*
        //这还有一个算法  作为预测出文章的情感 然后存入到articles_emotion
        $result_code = Emotion::ArticleAnalysis($article);

        //判断文章是否被分析过
        if ($result_code == 1) {
            $return_code['error_code'] = 200;
            $return_code['id'] = $get_data_from_android['id'];
            $return_code['url'] = $get_data_from_android['url'];
            return json_encode($return_code);
        } else {
            $return_code['error_code'] = 403;
            return json_encode($return_code);
        }
        */

        return response()->json([
            'error_code' => 200,
            'article' => [
                'id' => $article->id,
                'url' => $url
            ]
        ]);
    }
}
