<?php
/**
 * Created by PhpStorm.
 * User: Lxx<779219930@qq.com>
 * Date: 2016/10/14
 * Time: 17:23
 */

namespace app\home\controller;

use app\home\model\Browse;
use app\home\model\Learn;
use app\home\model\News as NewsModel;
use app\home\model\Picture;
use think\Controller;
use think\Db;
use app\home\model\WechatUser;

/**
 * Class Statistics
 * @package 统计模块
 */
class Statistics extends Base {
    /**
     * 列表统计页
     */
    public function index(){
        $uid = session('userId');
        $res = Db::field('title,create_time,music_path as table_type,user_type,id,front_cover') // 利用不存在值,判断两张表
        ->table('pb_learn')
            ->union("SELECT title,create_time,table_type,user_type,id,front_cover FROM pb_news order by create_time desc limit 8")
            ->select();

        $this->assign('list',$res);

        return $this->fetch();
    }

    public function moreDataList()
    {

        $len = input('length');
        $uid = session('userId');
        $res = Db::field('title,create_time,music_path as table_type,user_type,id,front_cover') // 利用不存在值,判断两张表
        ->table('pb_learn')
            ->union("SELECT title,create_time,table_type,user_type,id,front_cover FROM pb_news  order by create_time desc limit $len,7")
            ->select();

        if (!empty($res)) {

            foreach ($res as $k=>$v) {
                $img = Picture::get($v['front_cover']);
                if (empty($img)) {
                    $img['path'] = get_defalut_cover(1); //1 Learn
                }
                $res[$k]['path'] = $img['path'];
                $res[$k]['time'] = date("Y-m-d",$v['create_time']);
            }

            return $this->success('获取成功!',null,$res);
        } else {

            return $this->error('加载失败!');
        }
    }

    /**
     * 图表统计页
     */
    public function detail(){
        $id = input('id');
        $type = input('type');
        if ($type == 1) {
            $Model = new Learn();
            $ModelName = 'learn_id';
        } else {
            $Model = new NewsModel();
            $ModelName = 'news_id';
        }

        $info = $Model->get($id);
        $starttime = $info['create_time'];
        //取时段值，每3小时一时段
        $counts = array();
        for ($i=0; $i<8; $i++) {
            $time1 = $i*3*3600 + $starttime;
            $time2 = ($i+1)*3*3600 + $starttime;
            $map1 = array(
                $ModelName => $id,
                'create_time' => array('between',[$time1,$time2])
            );
            $count = Browse::where($map1)->count();
            $counts[0] = 0;
            $counts[$i+1] = $count;
        }

        $data['count'] = json_encode($counts);     //时段数据

        //外部用户数据
        $endtime = $info['create_time'] + 24*3600;
        $map2 = array(
            $ModelName => $id,
            'create_time' => array('between',[$starttime,$endtime]),
            'user_id' => array('eq','visitor')
        );
        $outuser = Browse::where($map2)->count();

        //内部用户
        $map3 = array(
            $ModelName => $id,
            'create_time' => array('between',[$starttime,$endtime]),
            'user_id' => array('neq','visitor')
        );
        $inuser = Browse::where($map3)->count();
        $data['user'] = json_encode([$inuser,$outuser]);
        $this->assign('data',$data);


        return $this->fetch();
    }

    /**
     * 列表统计页
     */
    public function form(){

        /*党员信息*/
        $map['department'] = array('neq','[9]');
        $userNum = WechatUser::where($map)->count();     //总人数
        $concernNum = WechatUser::where('status',1)->where($map)->count(); //关注人数

        $avg = WechatUser::avg('age');  //平均年龄
        $age = substr($avg,0,2);
        $age1 = WechatUser::where('age','lt',20)->where($map)->count();  //20一下百分比
        $agepercent1 = round(($age1/$userNum)*100).'%';
        $age2 = WechatUser::where('age','between',[20,30])->where($map)->count();    //20-30
        $agepercent2 = round(($age2/$userNum)*100).'%';
        $age3 = WechatUser::where('age','between',[30,40])->where($map)->count();    //30-40
        $agepercent3 = round(($age3/$userNum)*100).'%';
        $age4 = WechatUser::where('age','between',[40,50])->where($map)->count();    //40-50
        $agepercent4 = round(($age4/$userNum)*100).'%';
        $age5 = WechatUser::where('age','gt',50)->where($map)->count();    //50以上
        $agepercent5 = round(($age5/$userNum)*100).'%';

        $m = WechatUser::where('gender','eq',1)->where($map)->count(); //男性人数
        $male = round(($m/$userNum)*100).'%';
        $w = WechatUser::where('gender','eq',2)->where($map)->count(); //女性人数
        $female = round(($w/$userNum)*100).'%';

        $edu1 = WechatUser::where('education','eq',"初中以下")->where($map)->count();    //统计学历人数
        $edu2 = WechatUser::where('education','eq',"初中")->where($map)->count();
        $edu3 = WechatUser::where('education','eq',"高中")->where($map)->count();
        $edu4 = WechatUser::where('education','eq',"中专")->where($map)->count();
        $edu5 = WechatUser::where('education','eq',"大专")->where($map)->count();
        $edu6 = WechatUser::where('education','eq',"本科")->where($map)->count();
        $edu7 = WechatUser::where('education','eq',"硕士")->where($map)->count();
        $edu8 = WechatUser::where('education','eq',"硕士以上")->where($map)->count();

        $msg = array(
            'usernum' => $userNum, //总人数
            'concernnum' => $concernNum, //关注人数
            'avgage' => $age, //平均年龄,
            'agepercent1' => $agepercent1,
            'agepercent2' => $agepercent2,
            'agepercent3' => $agepercent3,
            'agepercent4' => $agepercent4,
            'agepercent5' => $agepercent5,
            'male' => $male,
            'female' => $female,
            'edu1' => $edu1,
            'edu2' => $edu2,
            'edu3' => $edu3,
            'edu4' => $edu4,
            'edu5' => $edu5,
            'edu6' => $edu6,
            'edu7' => $edu7,
            'edu8' => $edu8,
        );
        $this->assign('msg',$msg);


        return $this->fetch();
    }

    /**
     * 图表统计页
     */
    public function chart(){

        /*党员信息*/
        $map['department'] = array('neq','[9]');
        $userNum = WechatUser::where($map)->count();     //总人数
        $concernNum = WechatUser::where('status',1)->where($map)->count(); //关注人数
        $nonNum = $userNum - $concernNum; //未关注人数

        $avg = WechatUser::avg('age');  //平均年龄
        $age = substr($avg,0,2);
        $age1 = WechatUser::where('age','lt',20)->where($map)->count();  //20一下百分比
        $age2 = WechatUser::where('age','between',[20,30])->where($map)->count();    //20-30
        $age3 = WechatUser::where('age','between',[30,40])->where($map)->count();    //30-40
        $age4 = WechatUser::where('age','between',[40,50])->where($map)->count();    //40-50
        $age5 = WechatUser::where('age','gt',50)->where($map)->count();    //50以上

        $m = WechatUser::where('gender','eq',1)->where($map)->count(); //男性人数
        $male = round(($m/$userNum)*100);
        $w = WechatUser::where('gender','eq',2)->where($map)->count(); //女性人数
        $female = round(($w/$userNum)*100);

        $edu1 = WechatUser::where('education','eq',"初中以下")->where($map)->count();    //统计学历人数
        $edu2 = WechatUser::where('education','eq',"初中")->where($map)->count();
        $edu3 = WechatUser::where('education','eq',"高中")->where($map)->count();
        $edu4 = WechatUser::where('education','eq',"中专")->where($map)->count();
        $edu5 = WechatUser::where('education','eq',"大专")->where($map)->count();
        $edu6 = WechatUser::where('education','eq',"本科")->where($map)->count();
        $edu7 = WechatUser::where('education','eq',"硕士")->where($map)->count();
        $edu8 = WechatUser::where('education','eq',"硕士以上")->where($map)->count();

        $msg = array(
            'usernum' => $userNum, //总人数
            'concernnum' => $concernNum, //关注人数
            'nonnum' => $nonNum, //未关注人数
            'avgage' => $age, //平均年龄,
            'agepercent1' => $age1,
            'agepercent2' => $age2,
            'agepercent3' => $age3,
            'agepercent4' => $age4,
            'agepercent5' => $age5,
            'male' => $male,
            'female' => $female,
            'edu1' => $edu1,
            'edu2' => $edu2,
            'edu3' => $edu3,
            'edu4' => $edu4,
            'edu5' => $edu5,
            'edu6' => $edu6,
            'edu7' => $edu7,
            'edu8' => $edu8,
        );
        $this->assign('msg',$msg);

        return $this->fetch();
    }

    /**
     * 党员信息统计
     */
    public function party()
    {

        return $this->fetch();
    }
}