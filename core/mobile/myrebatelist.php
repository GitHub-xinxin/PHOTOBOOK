<?php
///////////
// 返利列表页 //
///////////
global $_W,$_GPC;

$p_title="可提现余额";
$user_id=pdo_get('ly_photobook_user',array('openid'=>$_W['openid']))['id'];
//返利明细
$sql='select * from '.tablename('ly_photobook_user_rebate').' where uniacid=:uniacid and userid=:userid and status=:status order by id desc';
$rebate=pdo_fetchall($sql,array('uniacid'=>$_W['uniacid'],'userid'=>$user_id,'status'=>0));
//可提现金额
$sql='select sum(money) as sum from '.tablename('ly_photobook_user_rebate').' where uniacid=:uniacid and userid=:userid and status=:status';
$allmoney=pdo_fetch($sql,array('uniacid'=>$_W['uniacid'],'userid'=>$user_id,'status'=>0))['sum'];
/**
 * 允许提现金额
 */
$take_money = pdo_get('ly_photobook_setting',array('uniacid'=>$_W['uniacid']))['take_price'];

if(empty($allmoney)){
	$allmoney=0;
}

include $this->template('myrebatelist');