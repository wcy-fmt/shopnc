<?php
/**
 * 邀请返利页面
 * by wlpro
 */
defined('InShopNC') or exit('Access Invalid!');

class inviteControl extends BaseHomeControl
{
    public function indexOp()
    {

        $member_id = intval($_SESSION['member_id']);
        if (!$member_id){
            redirect('index.php?act=login&ref_url='.urlencode(request_uri()));
        }
        $myurl = "请先登录再刷新本页面查看";
        if ($member_id > 0) {

            $myurl = BASE_SITE_URL . '/shop/index.php?act=login&op=register&zmr=' . $member_id;

            Tpl::output('myurl', $myurl);

            include_once BASE_DATA_PATH . '/resource/phpqrcode/index.php';

            $PhpQRCode = new PhpQRCode();
            $PhpQRCode->set('pngTempDir', BASE_UPLOAD_PATH . DS . ATTACH_STORE . DS . $member_id . DS);

            $PhpQRCode->set('date', $myurl);
            $PhpQRCode->set('pngTempName', $member_id . '_invite.png');

            $PhpQRCode->init();

            $qrcode = BASE_SITE_URL . DS . 'data/upload/' . DS . ATTACH_STORE . DS . $member_id . DS . $member_id . '_invite.png';

            Tpl::output('qrcode', $qrcode);
        }

        Tpl::showpage('invite');
    }
}
