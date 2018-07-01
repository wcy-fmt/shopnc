<?php
/**
 * 经验值及经验值日志管理
 *

 */
defined('InShopNC') or exit('Access Invalid!');

class exppointsModel extends Model{
    /**
     * 操作经验值
     * @author ShopNC Develop Team
     * @param  string $stage 操作阶段 login(登录),comments(评论),order(下单)
     * @param  array $insertarr 该数组可能包含信息 array('exp_memberid'=>'会员编号','exp_membername'=>'会员名称','exp_points'=>'经验值','exp_desc'=>'描述','orderprice'=>'订单金额','order_sn'=>'订单编号','order_id'=>'订单序号');
     * @param  bool $if_repeat 是否可以重复记录的信息,true可以重复记录，false不可以重复记录，默认为true
     * @return bool
     */
    function saveExppointsLog($stage,$insertarr){
        if (!$insertarr['exp_memberid']){
            return false;
        }
        $now = @date('Y-m-d H:i:s',time());
        $log_file = BASE_DATA_PATH.'/log/boss_'.date('Ymd',TIMESTAMP).'log';


        $exppoints_rule = C("exppoints_rule")?unserialize(C("exppoints_rule")):array();
        //记录原因文字
        switch ($stage){
            //todo:wlpro扩展小中大老板升降级别
            case 'boss':
                if (!$insertarr['exp_desc']){
                    $insertarr['exp_desc'] = '小中大老板升降级';
                }

                $orgType=1;

                switch ($insertarr['member_exppoints']){
                    case 0:
                        $orgType=1;
                        break;
                    case 998:
                        $orgType=2;
                        break;
                    case 2998:
                        $orgType=3;
                        break;
                    case 4998:
                        $orgType=4;
                        break;
                }

                $updateType=1;

                if (!$insertarr['amount']){
                    $insertarr['amount'] = 0;
                }
                if( $insertarr['amount']>=0 &&$insertarr['amount']<998) {
                    $insertarr['exp_points'] = 0;
                    $updateType=1;
                }

                if( $insertarr['amount']>=998 &&$insertarr['amount']<2998 ) {
                    $insertarr['exp_points'] = 998;
                    $updateType=2;
                }

                if( $insertarr['amount']>=2998 &&$insertarr['amount']<4998 ) {
                    $insertarr['exp_points'] = 2998;
                    $updateType=3;
                }

                if( $insertarr['amount']>=4998   ) {
                    $insertarr['exp_points'] = 4998;
                    $updateType=4;
                }

                // 如果本次经验值还未到达现有经验值，取现有的经验值
                if($insertarr['exp_points']<$insertarr['member_exppoints']){
                    $insertarr['exp_points']=$insertarr['member_exppoints'];
                }else {
                    // 预存款
                    $model_pd = Model('predeposit');

                    // 当新等级大于现有等级的话，才升级，
                    if ($updateType > 1 && $updateType > $orgType) {// 有升级动作再发送请求
                        //TODO：wlpro 如果是正常升级，发送升级请求
                        //http://www.seanwang66.com/mdd/api/update.php
                        $postData = 'MemberSN=' . $insertarr['exp_memberid'] . '&OrderNo=' . $insertarr['pay_sn'] . '&UpdateType=' . $updateType . '&UpdatePrice=' . $insertarr['amount'] . '&Time=' . time() . '&Key=' . md5($insertarr['exp_memberid'] . 'MDD');
                        $ch = curl_init(APT_GETWAY . 'update.php');
                        curl_setopt($ch, CURLOPT_POST, 1);
                        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
                        // receive server response ...
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                        $output = curl_exec($ch);

                        file_put_contents($log_file,"api update :".$output."\r\n", FILE_APPEND);

                        curl_close($ch);
                        $json = json_decode(preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $output), true);

                        // region 处理返佣

                        if($json['ErrorCode']==0) {
                            $UpdateBonus = $json['UpdateBonus'];
                            if ($UpdateBonus && count($UpdateBonus) > 0) {
                                foreach ($UpdateBonus as $val) {
                                    $data_pd = array();
                                    $data_pd['member_id'] = $val['MemberSN'];
                                    $data_pd['member_name'] = $val['MemberSN'];
                                    $data_pd['amount'] = $val['Bonus'];
                                    $data_pd['pdr_sn'] = $insertarr['pay_sn'];
                                    $model_pd->changePd('tmmdd', $data_pd);
                                }
                            }

                            $SpiralBonus = $json['SpiralBonus'];
                            if ($SpiralBonus && count($SpiralBonus) > 0) {
                                foreach ($SpiralBonus as $val) {
                                    $data_pd = array();
                                    $data_pd['member_id'] = $val['MemberSN'];
                                    $data_pd['member_name'] = $val['MemberSN'];
                                    $data_pd['amount'] = $val['Bonus'];
                                    $data_pd['pdr_sn'] = $insertarr['pay_sn'];
                                    $model_pd->changePd('tmmdd', $data_pd);
                                }
                            }
                        }

                        // endregion

                    } else {
                        //非升级状态的话，调用消费
                        //http://www.seanwang66.com/mdd/api/purchase.php?MemberSN=1111&OrderNo=222&Commission=333&Time=1529889861&Key=8c9909021e16e47a34ff85508fbd5941
                        $postData = 'MemberSN=' . $insertarr['exp_memberid'] . '&OrderNo=' . $insertarr['pay_sn'] . '&Commission=' . floatval($insertarr['commis_amount']) . '&Time=' . time() . '&Key=' . md5($insertarr['exp_memberid'] . 'MDD');

                        $ch = curl_init(APT_GETWAY . 'purchase.php');
                        curl_setopt($ch, CURLOPT_POST, 1);
                        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
                        // receive server response ...
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                        $output = curl_exec($ch);

                        file_put_contents($log_file,"api purchase :".$output."\r\n", FILE_APPEND);

                        curl_close($ch);

                        $json = json_decode(preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $output), true);

                        // region 处理返佣
                        if($json['ErrorCode']==0) {
                            $PurchaseBonus = $json['PurchaseBonus'];
                            if ($PurchaseBonus && count($PurchaseBonus) > 0) {
                                foreach ($PurchaseBonus as $val) {
                                    $data_pd = array();
                                    $data_pd['member_id'] = $val['MemberSN'];
                                    $data_pd['member_name'] = $val['MemberSN'];
                                    $data_pd['amount'] = $val['Bonus'];
                                    $data_pd['pdr_sn'] = $insertarr['pay_sn'];
                                    $model_pd->changePd('tmmdd', $data_pd);
                                }
                            }

                            $SpiralBonus = $json['SpiralBonus'];
                            if ($SpiralBonus && count($SpiralBonus) > 0) {
                                foreach ($SpiralBonus as $val) {
                                    $data_pd = array();
                                    $data_pd['member_id'] = $val['MemberSN'];
                                    $data_pd['member_name'] = $val['MemberSN'];
                                    $data_pd['amount'] = $val['Bonus'];
                                    $data_pd['pdr_sn'] = $insertarr['pay_sn'];
                                    $model_pd->changePd('tmmdd', $data_pd);
                                }
                            }
                        }

                        // endregion
                    }
                }
                break;
            case 'login':
                if (!$insertarr['exp_desc']){
                    $insertarr['exp_desc'] = '会员登录';
                }
                $insertarr['exp_points'] = 0;
                if (intval($exppoints_rule['exp_login']) > 0){
                    $insertarr['exp_points'] = intval($exppoints_rule['exp_login']);
                }
                break;
            case 'comments':
                if (!$insertarr['exp_desc']){
                    $insertarr['exp_desc'] = '评论商品';
                }
                $insertarr['exp_points'] = 0;
                if (intval($exppoints_rule['exp_comments']) > 0){
                    $insertarr['exp_points'] = intval($exppoints_rule['exp_comments']);
                }
                break;
            case 'order':
                if (!$insertarr['exp_desc']){
                    $insertarr['exp_desc'] = '订单'.$insertarr['order_sn'].'购物消费';
                }
                $insertarr['exp_points'] = 0;
                $exppoints_rule['exp_orderrate'] = floatval($exppoints_rule['exp_orderrate']);
                if ($insertarr['orderprice'] && $exppoints_rule['exp_orderrate'] > 0){
                    $insertarr['exp_points'] = @intval($insertarr['orderprice']/$exppoints_rule['exp_orderrate']);
                    $exp_ordermax = intval($exppoints_rule['exp_ordermax']);
                    if ($exp_ordermax > 0 && $insertarr['exp_points'] > $exp_ordermax){
                        $insertarr['exp_points'] = $exp_ordermax;
                    }
                }
                break;
        }
        //新增日志
        $value_array = array();
        $value_array['exp_memberid'] = $insertarr['exp_memberid'];
        $value_array['exp_membername'] = $insertarr['exp_membername'];
        $value_array['exp_points'] = $insertarr['exp_points'];
        $value_array['exp_addtime'] = time();
        $value_array['exp_desc'] = $insertarr['exp_desc'];
        $value_array['exp_stage'] = $stage;
        $result = false;
        if($value_array['exp_points'] != '0'){
            $result = self::addExppointsLog($value_array);
        }
        if ($result){
            //更新member内容
            $obj_member = Model('member');
            $upmember_array = array();
            if($stage=='boss') {
                $upmember_array['member_exppoints'] = $insertarr['exp_points'];
            }else{
                $upmember_array['member_exppoints'] = array('exp', 'member_exppoints + ' . $insertarr['exp_points']);
            }
            $obj_member->editMember(array('member_id'=>$insertarr['exp_memberid']),$upmember_array);
            return true;
        }else {
            return false;
        }
    }
    /**
     * 添加经验值日志信息
     *
     * @param array $param 添加信息数组
     */
    public function addExppointsLog($param) {
        if(empty($param)) {
            return false;
        }
        $result = $this->table('exppoints_log')->insert($param);
        return $result;
    }

    /**
     * 经验值日志总条数
     *
     * @param array $where 条件数组
     * @param array $field   查询字段
     * @param array $group   分组
     */
    public function getExppointsLogCount($where, $field = '*', $group = '') {
        $count = $this->table('exppoints_log')->field($field)->where($where)->group($group)->count();
        return $count;
    }

    /**
     * 经验值日志列表
     *
     * @param array $where 条件数组
     * @param mixed $page   分页
     * @param string $field   查询字段
     * @param int $limit   查询条数
     * @param string $order   查询条数
     */
    public function getExppointsLogList($where, $field = '*', $page = 0, $limit = 0,$order = '', $group = '') {
        if (is_array($page)){
            if ($page[1] > 0){
                return $this->table('exppoints_log')->field($field)->where($where)->page($page[0],$page[1])->order($order)->group($group)->select();
            } else {
                return $this->table('exppoints_log')->field($field)->where($where)->page($page[0])->order($order)->group($group)->select();
            }
        } else {
            return $this->table('exppoints_log')->field($field)->where($where)->page($page)->order($order)->group($group)->select();
        }
    }
    /**
     * 获得阶段说明文字
     */
    public function getStage(){
        $stage_arr = array();
        $stage_arr['login'] = '会员登录';
        $stage_arr['comments'] = '商品评论';
        $stage_arr['order'] = '订单消费';
        return $stage_arr;
    }
}
