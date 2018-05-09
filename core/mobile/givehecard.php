<?php
// 给下级代金券
global $_GPC,$_W;

if($_W['isajax']){
	$codeid=pdo_get('ly_photobook_code_order',array('id'=>$_GPC['coid']),array('codeid'));
	/**
	 * 将受到的卡卷录入到表中
	 */
	
	return $codeid;
}else{
	return '访问错误';
}