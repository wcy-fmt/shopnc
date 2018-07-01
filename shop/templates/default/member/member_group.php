<?php defined('InShopNC') or exit('Access Invalid!');?>
<link rel="stylesheet" type="text/css" href="<?php echo RESOURCE_SITE_URL;?>/js/jquery-ui/themes/ui-lightness/jquery.ui.css"  />

<div class="wrap">
  <div class="tabmenu">
    <?php include template('layout/submenu');?>
  </div>

  <table class="ncm-default-table">
    <thead>
      <tr>
        <th class="w200">第一层群友会员编号</th>
        <th class="tl">第一层群友会员等级</th>
      </tr>
    </thead>
    <tbody>
    <?php  if ($output['api_out']['ErrorCode']==0 && count($output['api_out']['Group'] )>0) { ?>
      <?php foreach($output['api_out']['Group']  as $val) { ?>
            <tr class="bd-line">
                <td class="goods-time"><?php echo $val['MemberSN'].'-'.$val['member_name'] ;?></td>
                <td class="tl"><?php
                    switch ($val['MemberLevel']) {
                        case '1':
                            echo '粉丝';
                            break;
                        case '2':
                            echo '小老板';
                            break;
                        case '3':
                            echo '中老板';
                            break;
                        case '4':
                            echo '大老板';
                            break;
                        default:
                            echo '未知';
                            break;
                    }
                    ?></td>
            </tr>
            <?php if($val['Downline'] && count($val['Downline'])>0){?>
            <tr class="bd-line">
                <td  colspan="2" style="margin-left: 30px;">
                    <table class="ncm-default-table">
                        <thead>
                        <tr>
                            <th class="w200">第二层群友会员编号</th>
                            <th class="tl">第二层群友会员等级</th>
                        </tr>
                        </thead>

                        <tbody>
                        <?php foreach($val['Downline']  as $val2) { ?>
                        <tr>
                            <td class="goods-time"><?php echo $val2['SubMemberSN'].'-'.$val2['member_name'] ;?></td>
                            <td class="tl"><?php
                                switch ($val2['SubMemberLevel']) {
                                    case '1':
                                        echo '粉丝';
                                        break;
                                    case '2':
                                        echo '小老板';
                                        break;
                                    case '3':
                                        echo '中老板';
                                        break;
                                    case '4':
                                        echo '大老板';
                                        break;
                                    default:
                                        echo '未知';
                                        break;
                                }
                                ?></td>
                        </tr>
                        <?php } ?>
                        </tbody>
                    </table>
                </td>
            </tr>
            <?php } ?>

      <?php } ?>
      <?php } else { ?>
      <tr>
        <td colspan="20" class="norecord"><div class="warning-option"><i>&nbsp;</i><span><?php echo $lang['no_record']; ?></span></div></td>
      </tr>
      <?php } ?>
    </tbody>
  </table>
</div>
<script type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/jquery-ui/i18n/zh-CN.js" charset="utf-8"></script> 
<script language="javascript">
$(function(){
	// $('#stime').datepicker({dateFormat: 'yy-mm-dd'});
	// $('#etime').datepicker({dateFormat: 'yy-mm-dd'});
});
</script>