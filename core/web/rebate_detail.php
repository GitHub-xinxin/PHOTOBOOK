<?php
global $_W,$_GPC;

//申请人
$num_list = pdo_get('ly_photobook_apply_rebate',array('uniacid'=>$_W['uniacid'],'id'=>$_GPC['id']))['num_list'];
$num_list = json_decode($num_list,true);

foreach ($num_list as $key => $value) {
	$list[] = pdo_get('ly_photobook_user_rebate',array('id'=>$value));
}

include $this->template('rebate_detail'); 