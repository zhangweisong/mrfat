<?php

/**
 * 文章控制器
 */

namespace Expert\Controller;

use Think\Controller;

class ArticleController extends Controller {

    function __construct() {
        //继承父类
        parent::__construct();
//        判断登录
        $expert_id = session("expert_id");
        if ($expert_id == "") {//未登录
            header("Location:" . U('login/login'));
            exit();
        }
    }

    public function _empty() {
        $this->articlelist();
    }

    //文章列表
    public function articlelist() {
        $expert_id = session("expert_id");
        // $expert_id = 1;
        #title + start + end 
        $title = I("title") ? I("title") : "";
        $start = I("start") ? I("start") : "";
        $end = I("end") ? I("end") : "";
        $where = "ec_expert_article.expert_article_author=" . $expert_id;
        if ($title) {
            $where .= " and ec_expert_article.expert_article_title LIKE '%" . $title . "%'";
            $this->assign('title', $title);
        }
        if ($start) {
            $where .= " and ec_expert_article.expert_article_addtime >=" . strtotime($start . "00:00:00") . " ";
            $this->assign('start', $start);
        }
        if ($end) {
            $where .= " and ec_expert_article.expert_article_addtime <=" . strtotime($end . "23:59:59") . " ";
            $this->assign('end', $end);
        }
        $count = M("expert_article")
                ->where($where)
                ->join("LEFT JOIN ec_savantnewsclassify ON ec_savantnewsclassify.snclassify_id = ec_expert_article.expert_article_type")
                ->count();
        $Page = new \Think\Page($count, 5);
        $articlelist = M("expert_article")
                ->limit($Page->firstRow . ',' . $Page->listRows)
                ->where($where)
                ->join("LEFT JOIN ec_savantnewsclassify ON ec_savantnewsclassify.snclassify_id = ec_expert_article.expert_article_type")
                ->order("ec_expert_article.expert_article_addtime DESC")
                ->select();
        foreach ($articlelist AS $k => $v) {
            $articlelist[$k]['expert_article_addtime'] = date("Y-m-d H:i:s", $v['expert_article_addtime']);
        }
        if (IS_AJAX) {
            if (count($articlelist) > 0) {
                $html = '';
                foreach ($articlelist AS $k => $v) {
                    $html .= "<div class='list'> 
                <img src='" . $v['expert_article_image'] . "' onclick='detail(" . $v['expert_article_id'] . ")'>
                <p onclick='detail(" . $v['expert_article_id'] . ")' class='omit2'>" . $v['expert_article_title'] . "</p>
                <p onclick='detail(" . $v['expert_article_id'] . ")'>时间：" . $v['expert_article_addtime'] . "</p>
                <div class='footerr flexb'>
                    <div class='flexb'>
                        <img src='public/expert/images/good.png' />
                        <p>" . $v['expert_article_like'] . "</p>
                    </div>
                    <div class='flexb'>
                        <img src='public/expert/images/message.png' />
                        <p>" . $v['expert_article_comment'] . "</p>
                    </div>
                </div> 
                <button  onclick='edit(" . $v['expert_article_id'] . ")'>编辑</button>
            </div> ";
                }
                $this->ajaxReturn($html);
            } else {
                $this->ajaxReturn(null);
            }
        } else {
            $this->assign("articlelist", $articlelist);
            $this->display();
        }
    }

    //文章详情
    public function articledetail() {
        $article_id = I("article_id");
        $articleinfo = M("expert_article")
                ->where("ec_expert_article.expert_article_id=" . $article_id)
                ->join("LEFT JOIN ec_savantnewsclassify ON ec_savantnewsclassify.snclassify_id = ec_expert_article.expert_article_type")
                ->join("LEFT JOIN ec_expert ON ec_expert.expert_id = ec_expert_article.expert_article_author")
                ->find();
                 
        $articleinfo['expert_article_content'] = htmlspecialchars_decode($articleinfo['expert_article_content']);
        $articleinfo["expert_article_content"] = str_replace("font-size","font_size",$articleinfo['expert_article_content']);
        $articleinfo['expert_article_addtime'] = from_time($articleinfo['expert_article_addtime']);
        //评论的分页 总数 
        $count = M("expert_comment")
                ->where("ec_expert_comment.comment_article=" . $article_id)
                ->join("LEFT JOIN ec_user ON ec_user.user_id = ec_expert_comment.comment_user_id")
                ->count();
        $Page = new \Think\Page($count, 5);
        $commentlist = M("expert_comment")
                ->limit($Page->firstRow . ',' . $Page->listRows)
                ->where("ec_expert_comment.comment_article=" . $article_id)
                ->join("LEFT JOIN ec_user ON ec_user.user_id = ec_expert_comment.comment_user_id")
                ->select();
        foreach ($commentlist AS $k => $v) {
            //处理评论的时间 改为“几天前”格式
            $commentlist[$k]['comment_addtime'] = from_time($v['comment_addtime']);
        }
        if (IS_AJAX) {
            if (count($commentlist) > 0) {
                $html = '';
                foreach ($commentlist AS $k => $v) {
                    $html .= '<div class="da">
                    <img src="' . $v['avatar'] . '" />
                    <p>' . $v['realname'] . '</p>
                    <p>' . $v['comment_addtime'] . '</p>
                    <p>' . $v['comment_content'] . '</p>
                    <div class="foot flexb"  onclick="like_questions(' . $v['comment_id'] . ')">
                        <img src="public/expert/images/good.png" />
                        <p id="count' . $v['comment_id'] . '">' . $v['comment_like'] . '</p>
                    </div>
                </div> ';
                }
                $this->ajaxReturn($html);
            } else {
                $this->ajaxReturn(null);
            }
        } else {
            $this->assign("article_id", $article_id);
            $this->assign("commentlist", $commentlist);
            $this->assign("articleinfo", $articleinfo); 
            $this->display();
        }
    }

    public function edit_article() {
        $ar_id = I('get.article_id');
        if (!$ar_id) {
            $this->error('获取文章失败！', U('article/articlelist'), 3);
            exit();
        }
        $art = M('expert_article')->where("expert_article_id={$ar_id}")->find();
        $art['expert_article_content'] = htmlspecialchars_decode($art['expert_article_content']);
        $this->assign('ca', D('category')->get_article_ca_Name());
        $this->assign('info', $art);
        $this->display();
    }

    public function add_edit_article() {
        if (IS_AJAX) {
            //文章表修改
            $expert_article_id = I('post.expert_article_id');
            $art['expert_article_type'] = I('post.expert_article_type');
            $art['expert_article_title'] = I('post.expert_article_title');
            $art['expert_article_content'] = I('post.expert_article_content');
            $art['expert_article_image'] = I('post.expert_article_image');
            $art['expert_article_author'] = session('expert_id');
            $art['expert_article_addtime'] = time();
            //过滤表情
            $art['expert_article_title'] = filterEmoji($art['expert_article_title'], 2);
            $art['expert_article_content'] = filterEmoji($art['expert_article_content'], 2);
            if (in_array('', $art)) {
                $this->ajaxReturn(array("data" => 1, 'msg' => '某一项数据为空！'));
                exit();
            }
            $art_info = M('expert_article')->where("expert_article_id={$expert_article_id}")->save($art);
            if (!$art_info) {
                $this->ajaxReturn(array("data" => 2, 'msg' => '文章修改失败！'));
                exit();
            }
            $this->ajaxReturn(array("data" => 3, 'msg' => '文章修改成功！'));
        }
    }

    //发布文章
    public function add_article() {
        if (IS_AJAX) {
            //文章表添加
            $art['expert_article_type'] = I('post.expert_article_type');
            $art['expert_article_title'] = I('post.expert_article_title');
            $art['expert_article_content'] = I('post.expert_article_content');
            $art['expert_article_image'] = I('post.expert_article_image');
            $art['expert_article_author'] = session('expert_id');
            $art['expert_article_addtime'] = time();
            //过滤表情 
            $art['expert_article_title'] = filterEmoji($art['expert_article_title'], 2);
            $art['expert_article_content'] = filterEmoji($art['expert_article_content'], 2);
            if (in_array('', $art)) {
                $this->ajaxReturn(array("data" => 1, 'msg' => '某一项数据为空！'));
                print_r($art);
                exit();
            }
            $model = new \Think\Model();
            $model->startTrans();
            $art_info = M('expert_article')->add($art);
            //专家增加活跃值
            $add_f = M('setinfo')->where("set_key='active_value'")->field('set_value')->find()['set_value'];
            $expert_id = session('expert_id');
            $add_info = M('expert')->where("expert_id=$expert_id")->setInc('expert_activevalue', (int) $add_f);
            if ($art_info && $add_info) {
                $model->commit();
                $this->ajaxReturn(array("data" => 2, 'msg' => '文章发布成功！'));
            } else {
                $model->rollback();
                $this->ajaxReturn(array("data" => 3, 'msg' => '文章发布失败！'));
            }
        }
        $this->assign('ca', D('category')->get_article_ca_Name());
        $this->display();
    }

    //专家文章评论点赞
    public function comment_dianzan() {
        $comment_id = I("comment_id");
        M("expert_comment")->where("comment_id=" . $comment_id)->setInc("comment_like");
    }

}
