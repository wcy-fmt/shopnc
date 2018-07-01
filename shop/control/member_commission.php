<?php
/**
 * 购物点管理
 ***/


defined('InShopNC') or exit('Access Invalid!');
class member_commissionControl extends BaseMemberControl {
	public function indexOp(){
		$this->commissionOp();
		exit;
	}
	public function __construct() {
		parent::__construct();
		/**
		 * 读取语言包
		 */
		Language::read('member_member_points,member_pointorder');
//		/**
//		 * 判断系统是否开启购物点功能
//		 */
//		if (C('points_isuse') != 1){
//			showMessage(Language::get('points_unavailable'),urlShop('member', 'home'),'html','error');
//		}
	}

	/**
	 * 群组
	 */
    public function groupOp()
    {
        //信息输出
        self::profile_menu('group');

        $postData = 'MemberSN=' . $_SESSION['member_id'] . '&Time=' . time() . '&Key=' . md5( $_SESSION['member_id'] . 'MDD');
//        $postData = 'MemberSN=1&Time=' . time() . '&Key=' . md5('1MDD');
        $ch = curl_init(APT_GETWAY . 'group.php');
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        // receive server response ...
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $output = curl_exec($ch);
        curl_close($ch);
        $json = json_decode(preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $output), true);

        if ($json['ErrorCode'] == 0) {
            foreach ($json['Group'] as $k => $val) {
                $member_info = Model('member')->getMemberInfoByID($val['MemberSN']);
                $json['Group'][$k]['member_name'] = $member_info['member_name'];
                $json['Group'][$k]['member_info'] = $member_info;
                if($val['Downline'] && count($val['Downline'])>0) {
                    foreach ($val['Downline'] as $k2 => $val2) {
                        $member_info2 = Model('member')->getMemberInfoByID($val2['SubMemberSN']);
                        $json['Group'][$k]['Downline'][$k2]['member_name'] = $member_info2['member_name'];
                        $json['Group'][$k]['Downline'][$k2]['member_info'] = $member_info2;
                    }
                }
            }
        }
//        var_dump($json);
//        echo $errorinfo = json_last_error();   //输出4 语法错误

        Tpl::output('api_out', $json);

        Tpl::showpage('member_group');
    }

	/**
	 * 升级奖金
	 */
    public function commissionOp()
    {
        //信息输出
        self::profile_menu('commission');


        $postData = 'MemberSN=' . $_SESSION['member_id'] . '&Time=' . time() . '&Key=' . md5( $_SESSION['member_id'] . 'MDD');
//        $postData = 'MemberSN=1&Time=' . time() . '&Key=' . md5('1MDD');
        $ch = curl_init(APT_GETWAY . 'record_update.php');
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        // receive server response ...
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $output = curl_exec($ch);
        curl_close($ch);
        $json = json_decode(preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $output), true);

        if ($json['ErrorCode'] == 0) {
            foreach ($json['Record'] as $k => $val) {
                $member_info = Model('member')->getMemberInfoByID($val['Source']);
                $json['Record'][$k]['member_name'] = $member_info['member_name'];
                $json['Record'][$k]['member_info'] = $member_info;
            }
        }
//        var_dump($json);
//        echo $errorinfo = json_last_error();   //输出4 语法错误

        Tpl::output('api_out', $json);

        Tpl::showpage('member_commission');
    }

    /**
     * 消费奖金
     */
    public function purchaseOp()
    {
        //信息输出
        self::profile_menu('purchase');


        $postData = 'MemberSN=' . $_SESSION['member_id'] . '&Time=' . time() . '&Key=' . md5($_SESSION['member_id'] . 'MDD');
//        $postData = 'MemberSN=1&Time=' . time() . '&Key=' . md5('1MDD');
        $ch = curl_init(APT_GETWAY . 'record_purchase.php');
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        // receive server response ...
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $output = curl_exec($ch);
        curl_close($ch);
        $json = json_decode(preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $output), true);

        if ($json['ErrorCode'] == 0) {
            foreach ($json['Record'] as $k => $val) {
                $member_info = Model('member')->getMemberInfoByID($val['Source']);
                $json['Record'][$k]['member_name'] = $member_info['member_name'];
                $json['Record'][$k]['member_info'] = $member_info;
            }

        }
//        var_dump($json);
//        echo $errorinfo = json_last_error();   //输出4 语法错误

        Tpl::output('api_out', $json);

        Tpl::showpage('member_purchase');
    }

    /**
     * 螺旋奖
     */
    public function spiralOp()
    {
        //信息输出
        self::profile_menu('spiral');

        $postData = 'MemberSN=' . $_SESSION['member_id'] . '&Time=' . time() . '&Key=' . md5( $_SESSION['member_id'] . 'MDD');
//        $postData = 'MemberSN=1&Time=' . time() . '&Key=' . md5('1MDD');
        $ch = curl_init(APT_GETWAY . 'record_spiral.php');
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        // receive server response ...
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $output = curl_exec($ch);
        curl_close($ch);
        $json = json_decode(preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $output), true);

        if ($json['ErrorCode'] == 0) {
            foreach ($json['Record'] as $k => $val) {
                $member_info = Model('member')->getMemberInfoByID($val['Source']);
                $json['Record'][$k]['member_name'] = $member_info['member_name'];
                $json['Record'][$k]['member_info'] = $member_info;
            }
        }
//        var_dump($json);
//        echo $errorinfo = json_last_error();   //输出4 语法错误

        Tpl::output('api_out', $json);

        Tpl::showpage('member_spiral');
    }

    /**
     * 参数
     */
    public function parameterOp()
    {
        //信息输出
        self::profile_menu('parameter');

        $time = time();
        $postData = 'Time=' . $time . '&Key=' . md5( $time . 'MDD');
//        $postData = 'MemberSN=1&Time=' . time() . '&Key=' . md5('1MDD');
        $ch = curl_init(APT_GETWAY . 'parameter.php');
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        // receive server response ...
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $output = curl_exec($ch);
        curl_close($ch);
        $json = json_decode(preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $output), true);

//        var_dump($json);
//        echo $errorinfo = json_last_error();   //输出4 语法错误

        Tpl::output('api_out', $json);

        Tpl::showpage('member_parameter');
    }

    /**
	 * 用户中心右边，小导航
	 *
	 * @param string	$menu_type	导航类型
	 * @param string 	$menu_key	当前导航的menu_key
	 * @param array 	$array		附加菜单
	 * @return
	 */
	private function profile_menu($menu_key='',$array=array()) {
		Language::read('member_layout');
		$lang	= Language::getLangContent();
		$menu_array		= array();
		$menu_array = array(
			1=>array('menu_key'=>'commission',	'menu_name'=>'升级奖金',	'menu_url'=>'index.php?act=member_commission'),
			2=>array('menu_key'=>'purchase',	'menu_name'=>'消费奖金额',	'menu_url'=>'index.php?act=member_commission&op=purchase'),
			3=>array('menu_key'=>'spiral','menu_name'=>'螺旋奖',	'menu_url'=>'index.php?act=member_commission&op=spiral'),
//			4=>array('menu_key'=>'parameter','menu_name'=>'参数',	'menu_url'=>'index.php?act=member_commission&op=parameter'),
            5=>array('menu_key'=>'group',	'menu_name'=>'群组结构',	'menu_url'=>'index.php?act=member_commission&op=group')
		);
		if(!empty($array)) {
			$menu_array[] = $array;
		}
		Tpl::output('member_menu',$menu_array);
		Tpl::output('menu_key',$menu_key);
	}
}
