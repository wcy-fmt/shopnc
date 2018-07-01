<?php
/**
 * 我的商城
 *
 *
 *
 *
 * by 33hao.com 好商城V3 运营版
 */

//use Shopnc\Tpl;

defined('InShopNC') or exit('Access Invalid!');

class member_indexControl extends mobileMemberControl {

	public function __construct(){
		parent::__construct();
	}

    /**
     * 我的商城
     */
	public function indexOp() {
        $member_info = array();
        $member_info['user_name'] = $this->member_info['member_name'];
        $member_info['avator'] = getMemberAvatarForID($this->member_info['member_id']);
        $member_info['point'] = $this->member_info['member_points'];
        $member_info['predepoit'] = $this->member_info['available_predeposit'];
	//v3-b11 显示充值卡
		$member_info['available_rc_balance'] = $this->member_info['available_rc_balance'];

		$member_id=$this->member_info['member_id'];

        $myurl = BASE_SITE_URL . '/shop/index.php?act=login&op=register&zmr=' . $member_id;


        include_once BASE_DATA_PATH . '/resource/phpqrcode/index.php';

        $PhpQRCode = new PhpQRCode();
        $PhpQRCode->set('pngTempDir', BASE_UPLOAD_PATH . DS . ATTACH_STORE . DS . $member_id . DS);

        $PhpQRCode->set('date', $myurl);
        $PhpQRCode->set('pngTempName', $member_id . '_invite.png');

        $PhpQRCode->init();

		$member_info['invite'] = BASE_SITE_URL . DS
            . 'data/upload/' . DS . ATTACH_STORE . DS . $member_id . DS . $member_id . '_invite.png';

        output_data(array('member_info' => $member_info));
	}

}
