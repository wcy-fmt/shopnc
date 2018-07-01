<?php
/**
 * 支付回调
 *
 *
 *
 *
 * @copyright  Copyright (c) 2007-2013 ShopNC Inc. (http://www.shopnc.net)
 * @license    http://www.shopnc.net
 * @link       http://www.shopnc.net
 * @since      File available since Release v1.1
 */

use Shopnc\Tpl;

defined('InShopNC') or exit('Access Invalid!');

class paymentControl extends mobileHomeControl{

    private $payment_code;

	public function __construct() {
		parent::__construct();

        $this->payment_code = $_GET['payment_code'];
	}

    public function returnopenidOp(){
        $payment_api = $this->_get_payment_api();
        if($this->payment_code != 'wxpay'){
            output_error('支付参数异常');
            die;
        }

        $payment_api->getopenid();

    }

    /**
     * 支付回调
     */
    public function returnOp() {
        unset($_GET['act']);
        unset($_GET['op']);
        unset($_GET['payment_code']);

        $payment_api = $this->_get_payment_api();

        $payment_config = $this->_get_payment_config();

        $callback_info = $payment_api->getReturnInfo($payment_config);

        if($callback_info) {
            //验证成功
            $result = $this->_update_order($callback_info['out_trade_no'], $callback_info['trade_no']);
            if($result['state']) {
                Tpl::output('result', 'success');
                Tpl::output('message', '支付成功');
            } else {
                Tpl::output('result', 'fail');
                Tpl::output('message', '支付失败');
			}
        } else {
			//验证失败
            Tpl::output('result', 'fail');
            Tpl::output('message', '支付失败');
		}

        Tpl::showpage('payment_message');
    }

    /**
     * 支付提醒
     */
    public function notifyOp() {
        // 恢复框架编码的post值
        $_POST['notify_data'] = html_entity_decode($_POST['notify_data']);

        $payment_api = $this->_get_payment_api();

        $payment_config = $this->_get_payment_config();

        $callback_info = $payment_api->getNotifyInfo($payment_config);

        if($callback_info) {
            //验证成功
            $result = $this->_update_order($callback_info['out_trade_no'], $callback_info['trade_no']);
            if($result['state']) {
                if($this->payment_code == 'wxpay'){
                    echo $callback_info['returnXml'];
                    die;
                }else{
                    echo 'success';die;
                }

            }
		}

        //验证失败

        if($this->payment_code == 'wxpay'){
            echo '<xml><return_code><!--[CDATA[FAIL]]--></return_code></xml>';
            die;
        }else{
            echo "fail";die;
        }
    }

    /**
     * 获取支付接口实例
     */
    private function _get_payment_api() {
        $inc_file = BASE_PATH.DS.'api'.DS.'payment'.DS.$this->payment_code.DS.$this->payment_code.'.php';

        if(is_file($inc_file)) {
            require($inc_file);
        }

        $payment_api = new $this->payment_code();

        return $payment_api;
    }

    /**
     * 获取支付接口信息
     */
    private function _get_payment_config() {
        $model_mb_payment = Model('mb_payment');

        //读取接口配置信息
        $condition = array();
        $condition['payment_code'] = $this->payment_code;
        $payment_info = $model_mb_payment->getMbPaymentOpenInfo($condition);
        
        return $payment_info['payment_config'];
    }

    /**
     * 更新订单状态
     */
    private function _update_order($out_trade_no, $trade_no) {
        $model_order = Model('order');
        $logic_payment = Logic('payment');

        $tmp = explode('|', $out_trade_no);
        if(count($tmp)!=2){
            $tmp = explode('-', $out_trade_no);
        }
        $out_trade_no = $tmp[0];
        if (!empty($tmp[1])) {
            $order_type = $tmp[1];
        } else {
            $order_pay_info = Model('order')->getOrderPayInfo(array('pay_sn'=> $out_trade_no));
            if(empty($order_pay_info)){
                $order_type = 'v';
            } else {
                $order_type = 'r';
            }
        }

        if ($order_type == 'r') {
            $result = $logic_payment->getRealOrderInfo($out_trade_no);
            if (intval($result['data']['api_pay_state'])) {
                return array('state'=>true);
            }
            $order_list = $result['data']['order_list'];
            $result = $logic_payment->updateRealOrder($out_trade_no, $this->payment_code, $order_list, $trade_no);

        } elseif ($order_type == 'v') {
        	$result = $logic_payment->getVrOrderInfo($out_trade_no);
	        if ($result['data']['order_state'] != ORDER_STATE_NEW) {
	            return array('state'=>true);
	        }
	        $result = $logic_payment->updateVrOrder($out_trade_no, $this->payment_code, $result['data'], $trade_no);
        }

        //todo:返佣


        //todo: wlpro 取子订单列表,发送消费请求
        $condition = array();
        $condition['pay_sn'] = $out_trade_no;
        $order_list = $model_order->getOrderList($condition, '', 'order_id,order_state,payment_code,order_amount,order_commis_amount,rcb_amount,pd_amount,order_sn', '', array(), true);


        $member_info = Model('member')->getMemberInfoByID($_SESSION['member_id']);

        //订单总支付金额(不包含货到付款)
        $pay_amount = 0;
        $pay_commis_amount = 0;
        // 是否使用购物点
        $use_points = 0;

        foreach ($order_list as $key => $order_info) {

            $pay_amount += floatval($order_info['order_amount']);
            $pay_commis_amount += floatval($order_info['order_commis_amount']);
            $per_pay_amount = floatval($order_info['order_amount']);

            if ($order_info['use_points']) {
                $use_points = 1;

                // TODO：wlpro 每个订单扣减购物点
                //扣除会员购物点
                $member_info =  Model('member')->getMemberInfoByID($_SESSION['member_id']);
                $insert_arr = array();
                $insert_arr['pl_memberid'] = $member_info['member_id'];
                $insert_arr['pl_membername'] = $member_info['member_name'];
                $insert_arr['pl_points'] =  0 - @intval($per_pay_amount/C('points_orderrate'));
                $insert_arr['point_ordersn'] = $order_info['order_sn'];
                $insert_arr['member_info'] = $member_info;
                Model('points')->savePointsLog('pointorder_ext',$insert_arr,true);

                //
                // TODO：wlpro 购物点换购完成，返还购物点
                //扣除会员购物点
                $member_info =  Model('member')->getMemberInfoByID($_SESSION['member_id']);
                $insert_arr = array();
                $insert_arr['pl_memberid'] = $member_info['member_id'];
                $insert_arr['pl_membername'] = $member_info['member_name'];
                $insert_arr['pl_points'] =   @intval($order_info['order_amount']/C('points_orderrate')* 1.3) ;
                $insert_arr['point_ordersn'] = $order_info['order_sn'];
                Model('points')->savePointsLog('return_points',$insert_arr,true);
            }
        }

        // TODO:升级会员等级

        $exppoints_model = Model('exppoints');
        $exppoints_model->saveExppointsLog('boss', array('exp_memberid' => $member_info['member_id'], 'exp_membername' => $member_info['member_name'],'member_exppoints' => $member_info['member_exppoints'],'amount'=>$pay_amount,'commis_amount'=>$pay_commis_amount,'pay_sn'=>$out_trade_no), true);




        return $result;
    }

}
