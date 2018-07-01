<?php defined('InShopNC') or exit('Access Invalid!');?>
<link rel="stylesheet" type="text/css" href="<?php echo RESOURCE_SITE_URL;?>/js/jquery-ui/themes/ui-lightness/jquery.ui.css"  />

<div class="wrap">
  <div class="tabmenu">
    <?php include template('layout/submenu');?>
  </div>

  <table class="ncm-default-table">
    <thead>
      <tr>
        <th class="w200">奖金发放时间</th>
        <th class="tl">奖金来源会员</th>
        <th class="tl">订单编号</th>
        <th class="tl">订单分配金额</th>
        <th class="tl">当时会员等级</th>
        <th class="tl">螺旋奖发放比例</th>
        <th class="tl">螺旋奖发放人数</th>
        <th class="tl">个人螺旋加权比</th>
        <th class="tl">获得奖金</th>
      </tr>
    </thead>
    <tbody>
    <?php  if ($output['api_out']['ErrorCode']==0 && count($output['api_out']['Record'] )>0) { ?>
      <?php foreach($output['api_out']['Record']  as $val) { ?>
      <tr class="bd-line">
        <td class="goods-time"><?php echo $val['Date'];?></td>
        <td class="tl"><?php echo $val['Source'].'-'.$val['member_name']; ?></td>
        <td class="tl"><?php echo $val['OrderNo'];?></td>
        <td class="tl"><?php echo $val['OrderPoint'];?></td>
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
        <td class="tl"><?php echo $val['SpiralRate'].'%';?></td>
        <td class="tl"><?php echo $val['SpiralPersonal'];?></td>
        <td class="tl"><?php echo $val['Rate'].'%';?></td>
        <td class="tl"><?php echo $val['Bonus'];?></td>
      </tr>
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