<?php defined('InShopNC') or exit('Access Invalid!');?>
<link rel="stylesheet" type="text/css" href="<?php echo RESOURCE_SITE_URL;?>/js/jquery-ui/themes/ui-lightness/jquery.ui.css"  />

<div class="wrap">
  <div class="tabmenu">
    <?php include template('layout/submenu');?>
  </div>
    <table class="ncm-default-table">
    <thead>
      <tr>
        <th class="w200">会员等级</th>
        <th class="tl">等级名称</th>
        <th class="tl">升级该等级需消费金额</th>
        <th class="tl">群友升级时可分配比例</th>
        <th class="tl">群友消费时可分配比例</th>
        <th class="tl">螺旋加权分数</th>
      </tr>
    </thead>
    <tbody>
    <?php  if ( $output['api_out']['ErrorCode']==0 && count($output['api_out']['Member'] )>0) { ?>
      <?php foreach($output['api_out']['Member']  as $val) { ?>
      <tr class="bd-line">
        <td class="goods-time"><?php echo $val['Level'];?></td>
        <td class="tl"><?php echo $val['Title']; ?></td>
        <td class="tl"><?php echo $val['Price'];?></td>
        <td class="tl"><?php echo $val['UpdateRate'];?></td>
        <td class="tl"><?php echo $val['PurchaseRate'];?></td>
        <td class="tl"><?php echo $val['SpiralPoint'];?></td>
      </tr>
      <?php } ?>
      <?php } else { ?>
      <tr>
        <td colspan="20" class="norecord"><div class="warning-option"><i>&nbsp;</i><span><?php echo $lang['no_record']; ?></span></div></td>
      </tr>
      <?php } ?>
    </tbody>
  </table>

    <Br />

    <br/>

    <table class="ncm-default-table">
        <thead>
        <tr>
            <th class="w200">螺旋奖金分配比例</th>
            <th class="tl">每次分配人数</th>
            <th class="tl">最后分配到之会员编号</th>
        </tr>
        </thead>
        <tbody>
        <?php  if ( $output['api_out']['ErrorCode']==0 && count($output['api_out']['Spiral'] )>0) { ?>
            <?php foreach($output['api_out']['Spiral']  as $val) { ?>
                <tr class="bd-line">
                    <td class="goods-time"><?php echo $val['SpiralRate'];?></td>
                    <td class="tl"><?php echo $val['SpiralPerson']; ?></td>
                    <td class="tl"><?php echo $val['SpiralFlag'];?></td>
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