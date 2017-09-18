<?php
/**
 * Created by PhpStorm.
 * User: Lxx<779219930@qq.com>
 * Date: 2017/2/8
 * Time: 13:52
 */

namespace app\admin\model;


class VolunteerRecruitReceive extends Base {
    /**
     * 获取微信用户信息
     */
    public function user() {
        return $this->hasOne('WechatUser','userid','userid');
    }

    /**
     * 获取订单信息
     */
    public function order() {
        return $this->hasOne('VolunteerRecruit','id','rid');
    }
}