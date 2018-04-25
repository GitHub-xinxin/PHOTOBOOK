<?php
///////////
// 返利列表页 //
///////////
global $_W,$_GPC;
$user_id=pdo_get('ly_photobook_user',array('openid'=>$_W['openid']))['id'];
$sql='select * from '.tablename('ly_photobook_user_rebate').' where uniacid=:uniacid and userid=:userid';
$rebate=pdo_fetchall($sql,array('uniacid'=>$_W['uniacid'],'userid'=>$user_id));
$sql='select sum(money) as sum from '.tablename('ly_photobook_user_rebate').' where uniacid=:uniacid and userid=:userid and status=:status';
$allmoney=pdo_fetch($sql,array('uniacid'=>$_W['uniacid'],'userid'=>$user_id,'status'=>0))['sum'];
if(empty($allmoney)){
	$allmoney=0;
}

include $this->template('myrebatelist');