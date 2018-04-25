<?php
// 给下级代金券
global $_GPC,$_W;

return '2222222';
if($_W['isajax']){
	$codeid=pdo_get('ly_photobook_code_order',array('id'=>$_GPC['coid']),array('codeid'));
	return $codeid;
}else{
	return '访问错误';
}