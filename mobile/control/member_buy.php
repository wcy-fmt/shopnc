<?php
/**
 * 购买
 *
 *
 *
 *
 * by 33hao.com 好商城V3 运营版
 */


defined('InShopNC') or exit('Access Invalid!');

class member_buyControl extends mobileMemberControl {

    public function __construct() {
        parent::__construct();
    }

    /**
     * 购物车、直接购买第一步:选择收获地址和配置方式
     */
    public function buy_step1Op() {
        $cart_id = explode(',', $_POST['cart_id']);

        $logic_buy = logic('buy');

        //得到购买数据
        $result = $logic_buy->buyStep1($cart_id, $_POST['ifcart'], $this->member_info['member_id'], $this->member_info['store_id']);
        if(!$result['state']) {
            output_error($result['msg']);
        } else {
            $result = $result['data'];
        }

        //整理数据
        $store_cart_list = array();
        foreach ($result['store_cart_list'] as $key => $value) {
            $store_cart_list[$key]['goods_list'] = $value;
            $store_cart_list[$key]['store_goods_total'] = $result['store_goods_total'][$key];
            if(!empty($result['store_premiums_list'][$key])) {
                $result['store_premiums_list'][$key][0]['premiums'] = true;
                $result['store_premiums_list'][$key][0]['goods_total'] = 0.00;
                $store_cart_list[$key]['goods_list'][] = $result['store_premiums_list'][$key][0];
            }
            $store_cart_list[$key]['store_mansong_rule_list'] = $result['store_mansong_rule_list'][$key];
            $store_cart_list[$key]['store_voucher_list'] = $result['store_voucher_list'][$key];
            if(!empty($result['cancel_calc_sid_list'][$key])) {
                $store_cart_list[$key]['freight'] = '0';
                $store_cart_list[$key]['freight_message'] = $result['cancel_calc_sid_list'][$key]['desc'];
            } else {
                $store_cart_list[$key]['freight'] = '1';
            }
            $store_cart_list[$key]['store_name'] = $value[0]['store_name'];
        }

        $buy_list = array();
        $buy_list['store_cart_list'] = $store_cart_list;
        $buy_list['freight_hash'] = $result['freight_list'];
        $buy_list['address_info'] = $result['address_info'];
        $buy_list['ifshow_offpay'] = $result['ifshow_offpay'];
        $buy_list['vat_hash'] = $result['vat_hash'];
        $buy_list['inv_info'] = $result['inv_info'];
        $buy_list['available_predeposit'] = $result['available_predeposit'];
        $buy_list['available_rc_balance'] = $result['available_rc_balance'];
        output_data($buy_list);
    }

    /**
     * 购物车、直接购买第二步:保存订单入库，产生订单号，开始选择支付方式
     *
     */
    public function buy_step2Op()
    {
        $param = array();
        $param['ifcart'] = $_POST['ifcart'];
        $param['cart_id'] = explode(',', $_POST['cart_id']);
        $param['address_id'] = $_POST['address_id'];
        $param['vat_hash'] = $_POST['vat_hash'];
        $param['offpay_hash'] = $_POST['offpay_hash'];
        $param['offpay_hash_batch'] = $_POST['offpay_hash_batch'];
        $param['pay_name'] = $_POST['pay_name'];
        $param['invoice_id'] = $_POST['invoice_id'];

        //处理代金券
        $voucher = array();
        $post_voucher = explode(',', $_POST['voucher']);
        if (!empty($post_voucher)) {
            foreach ($post_voucher as $value) {
                list($voucher_t_id, $store_id, $voucher_price) = explode('|', $value);
                $voucher[$store_id] = $value;
            }
        }
        $param['voucher'] = $voucher;

        //手机端暂时不做支付留言，页面内容太多了
        //$param['pay_message'] = json_decode($_POST['pay_message']);
        $param['pd_pay'] = $_POST['pd_pay'];
        $param['rcb_pay'] = $_POST['rcb_pay'];
        $param['password'] = $_POST['password'];
        $param['fcode'] = $_POST['fcode'];
        $param['order_from'] = 2;
        $logic_buy = logic('buy');

        $result = $logic_buy->buyStep2($param, $this->member_info['member_id'], $this->member_info['member_name'], $this->member_info['member_email']);
        if (!$result['state']) {
            output_error($result['msg']);
        }

        $out_trade_no = $result['data']['pay_sn'];

        LOG::record("DSAFSAFFDSFDSAFDSAF",lOG::SQL);

        //todo: wlpro 取子订单列表,发送消费请求
        $model_order = Model('order');
        $condition = array();
        $condition['pay_sn'] = $out_trade_no;
        $order_list = $model_order->getOrderList($condition, '', 'payment_code,order_state,order_id,order_state,payment_code,order_amount,order_commis_amount,rcb_amount,pd_amount,order_sn', '', array(), true);

        $order_finished = 0;
        foreach ($order_list as $key => $order_info) {
            if ($order_info['payment_code'] == 'predeposit' && $order_info['order_state'] == 20) {
                $order_finished = 1;
            }
        }

        if ($order_finished == 1) {
            $member_info = Model('member')->getMemberInfoByID($this->member_info['member_id']);

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
                    $member_info = Model('member')->getMemberInfoByID($this->member_info['member_id']);
                    $insert_arr = array();
                    $insert_arr['pl_memberid'] = $member_info['member_id'];
                    $insert_arr['pl_membername'] = $member_info['member_name'];
                    $insert_arr['pl_points'] = 0 - @intval($per_pay_amount / C('points_orderrate'));
                    $insert_arr['point_ordersn'] = $order_info['order_sn'];
                    $insert_arr['member_info'] = $member_info;
                    Model('points')->savePointsLog('pointorder_ext', $insert_arr, true);

                    //
                    // TODO：wlpro 购物点换购完成，返还购物点
                    //扣除会员购物点
                    $member_info = Model('member')->getMemberInfoByID($this->member_info['member_id']);
                    $insert_arr = array();
                    $insert_arr['pl_memberid'] = $member_info['member_id'];
                    $insert_arr['pl_membername'] = $member_info['member_name'];
                    $insert_arr['pl_points'] = @intval($order_info['order_amount'] / C('points_orderrate') * 1.3);
                    $insert_arr['point_ordersn'] = $order_info['order_sn'];
                    Model('points')->savePointsLog('return_points', $insert_arr, true);
                }
            }

            // TODO:升级会员等级
            $exppoints_model = Model('exppoints');
            $exppoints_model->saveExppointsLog('boss', array('exp_memberid' => $member_info['member_id'], 'exp_membername' => $member_info['member_name'], 'member_exppoints' => $member_info['member_exppoints'], 'amount' => $pay_amount, 'commis_amount' => $pay_commis_amount, 'pay_sn' => $out_trade_no), true);
//            var_dump($member_info);
        }

        output_data(array('pay_sn' => $result['data']['pay_sn']));
    }

    /**
     * 验证密码
     */
    public function check_passwordOp() {
        if(empty($_POST['password'])) {
            output_error('参数错误');
        }

        $model_member = Model('member');

        $member_info = $model_member->getMemberInfoByID($this->member_info['member_id']);
        if($member_info['member_paypwd'] == md5($_POST['password'])) {
            output_data('1');
        } else {
            output_error('密码错误');
        }
    }

    /**
     * 更换收货地址
     */
    public function change_addressOp() {
        $logic_buy = Logic('buy');

        $data = $logic_buy->changeAddr($_POST['freight_hash'], $_POST['city_id'], $_POST['area_id'], $this->member_info['member_id']);
        if(!empty($data) && $data['state'] == 'success' ) {
            output_data($data);
        } else {
            output_error('地址修改失败');
        }
    }


}

