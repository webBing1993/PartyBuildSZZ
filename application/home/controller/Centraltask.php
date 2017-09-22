<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/7/3 0003
 * Time: 下午 3:02
 */
/**
 * Created by PhpStorm.
 * User: stiff <1570004138@163.com>
 * Date: 2017/6/5
 * Time: 13:53
 */
namespace app\home\controller;
use app\home\model\Centraltask as CentraltaskModel;
use app\home\model\Picture;
use app\home\model\Like;
use app\home\model\Comment;

/**
 * 中心工作
 */
class Centraltask extends Base{
    /**
     * 主页
     */
    public function index(){
        $Model = new CentraltaskModel();
        if(IS_POST) {
            $data = input('post.');
            $list = $Model->getMoreWork($data);
            if($list) {
                return $this->success("加载成功","",$list);
            }else {
                return $this->error("加载失败");
            }
        }else {
            $note = $Model->getNote();
            $this->assign('note',$note);

            $work = $Model->getWork();
            $this->assign('work',$work);

            return $this->fetch();

        }
    }

    /**
     * 获取标签选择
     */
    public function getSelectDetail() {
        $pid = input('pid');
        $Model = new CentraltaskModel();
        $data = $Model->getClickDetail($pid);
        if($data) {
            return $this->success("查询成功","",$data);
        }else {
            return $this->error("查询失败");
        }
    }

    /**
     * 最新通知
     */
    public function note() {
        $this->anonymous();
        $this->jssdk();

        $id = input('id');
        $userId = session('userId');
        //浏览加一
        $info['views'] = array('exp','`views`+1');
        CentraltaskModel::where('id',$id)->update($info);

        if($userId != "visitor"){
            //浏览不存在则存入pb_browse表
//            $this->browser(2,$userId,$id);
        }
        //详细信息
        $info = CentraltaskModel::get($id);
        if(!empty($info['images'])) {
            $info['images'] = json_decode($info['images']);
        }
        //分享图片及链接及描述
//        $image = Picture::where('id',$info['front_cover'])->find();
//        $info['share_image'] = "http://".$_SERVER['SERVER_NAME'].$image['path'];
//        $info['link'] = "http://".$_SERVER['SERVER_NAME'].$_SERVER['REDIRECT_URL'];
//        $info['desc'] = str_replace('&nbsp;','',strip_tags($info['content']));

        //获取 文章点赞
        $likeModel = new Like();
        $like = $likeModel->getLike(14,$id,$userId);
        $info['is_like'] = $like;
        $this->assign('detail',$info);
        //获取 评论
        $commentModel = new Comment();
        $comment = $commentModel->getComment(14,$id,$userId);
        $this->assign('comment',$comment);
        return $this->fetch();
    }

    /**
     * 通知列表
     */
    public function notelist() {
        $Model = new CentraltaskModel();
        if(IS_POST) {
            $data = input('post.');
            $res = $Model->getMoreNote($data);
            if($res) {
                return $this->success("加载成功","",$res);
            }else {
                return $this->error("加载失败");
            }
        }else {
            $list = $Model->getNoteList();
            $this->assign('list',$list);

            $pid = input('pid');
            $this->assign('pid',$pid);
            return $this->fetch();
        }
    }

    /**
     * 工作动态
     */
    public function work() {
        $this->anonymous();
        $this->jssdk();

        $id = input('id');
        $userId = session('userId');
        //浏览加一
        $info['views'] = array('exp','`views`+1');
        CentraltaskModel::where('id',$id)->update($info);

        if($userId != "visitor"){
            //浏览不存在则存入pb_browse表
//            $this->browser(2,$userId,$id);
        }
        //详细信息
        $info = CentraltaskModel::get($id);
        if(!empty($info['images'])) {
            $info['images'] = json_decode($info['images']);
        }
        //分享图片及链接及描述
//        $image = Picture::where('id',$info['front_cover'])->find();
//        $info['share_image'] = "http://".$_SERVER['SERVER_NAME'].$image['path'];
//        $info['link'] = "http://".$_SERVER['SERVER_NAME'].$_SERVER['REDIRECT_URL'];
//        $info['desc'] = str_replace('&nbsp;','',strip_tags($info['content']));

        //获取 文章点赞
        $likeModel = new Like();
        $like = $likeModel->getLike(14,$id,$userId);
        $info['is_like'] = $like;
        $this->assign('detail',$info);
        //获取 评论
        $commentModel = new Comment();
        $comment = $commentModel->getComment(14,$id,$userId);
        $this->assign('comment',$comment);
        return $this->fetch();
    }
}