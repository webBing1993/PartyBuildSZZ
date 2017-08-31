<?php
/**
 * Created by PhpStorm.
 * User: laowang
 * Date: 2017/1/17
 * Time: 14:49
 */
namespace app\home\controller;
use app\home\model\Notice;
/*
 * 两学一做  党史 通讯录
 */

class Work extends Base{
    /*
     * 党史
     */
    public function dangshi(){
        return  $this->fetch();
    }
    /*
     * 通讯录
     */
    public function structurelist(){
        return $this->fetch();
    }
    /*
     * 通讯录详情
     */
    public function structuredetail(){
        $party = input('party');
        $this->assign('party',$party);
        return $this->fetch();
    }

    /*
     * 党员签到
     */
    public function sign()
    {
        $map1 = ['meet_time' => ['gt',time()]];
        $map2 = ['meet_time' => ['elt',time()]];
        $order = 'meet_time desc';
        $go = Notice::where($map2)->order($order)->limit(10)->select();
        $now = Notice::where($map1)->order($order)->limit(10)->select();

        $this->assign('go',$go);  // 已经结束
        $this->assign('now',$now);  // 进行中

        return $this->fetch();
    }

    /**
     * 党员签到获取更多
     */
    public function getMoreSignList()
    {
        //1为最新 2为历史
        $type = input('type');
        $len = input('length');
        $map1 = ['meet_time' => ['gt',time()]];
        $map2 = ['meet_time' => ['elt',time()]];
        $order = 'meet_time desc';

        if ($type == 1) {
            $list = Notice::where($map1)->order($order)->limit($len,5)->select();
        } else {
            $list = Notice::where($map1)->order($order)->limit($len,5)->select();
        }

        if (!empty($list)) {

            return $this->success('获取成功!',null,$list);
        } else {

            return $this->error('获取失败!');
        }
    }

    /**
     * 党员签到详情
     */
    public function main(){
        $this->jssdk();
        $id = input('id');
        $c = input('c');
        $data = Notice::where(['id' => $id])->find();
        //获取签到报名用户信息
        $Apply = Apply::where(['notice_id'=>$id])->order('id desc')->select();
        $arr = array();
        if(empty($Apply)){
            $arr[]= "<span style='color: #aaa' id='noneMan'>无人签到</span>";
        }else{
            foreach($Apply as $value){
                $Wechat = WechatUser::where(['userid'=>$value['userid']])->order('id desc')->find();
                $arr[] = "<img src='".$Wechat['avatar']."'/><span>".$Wechat['name']."</span>";
            }
        }
        $data['infoes'] = $arr;
        $this->assign('data',$data);
        $this->assign('c',$c);
        return $this->fetch();
    }

    /**
     * 扫一扫签到
     */
    public function scan(){
        $id = input('id');
        $userid = input('user_id');
        $result = Apply::where(array('notice_id'=>$id,'userid'=>$userid))->find();
        if($result){
            $Wechat = WechatUser::where(['userid'=>$userid])->find();
            return array('status'=>0,'header'=>$Wechat['avatar'],'name'=>$Wechat['name']);
        }else{
            $Wechat = WechatUser::where(['userid'=>$userid])->find();
            if($Wechat){
                $data = array(
                    'notice_id' => $id,
                    'userid' =>$userid,
                    'status' =>0
                );
                $Apply = new Apply();
                $res = $Apply->save($data);
                if($res){
                    $Wechat = WechatUser::where(['userid'=>$userid])->find();
                    return array('status'=>1,'header'=>$Wechat['avatar'],'name'=>$Wechat['name']);
                }else{
                    return array('status'=>2,'header'=>null,'name'=>null);
                }
            }else{
                return array('status'=>2,'header'=>null,'name'=>null);
            }

        }
    }

}