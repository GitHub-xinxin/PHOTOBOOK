<?php
global $_W,$_GPC;
$pindex = max(1, intval($_GPC['page']));
$psize = 20;
$account_api = WeAccount::create();

if(checksubmit()){
	$buy_list_all = pdo_fetchall('select * from ims_ly_photobook_code_order where uniacid='.$_W['uniacid'].' and status =1 order by id desc');
	$buy_list = [];
	foreach ($buy_list_all as $key => $value) {
	# code...
		$buy_list_all[$key]['codeid'] = pdo_get('ly_photobook_codes',array('uniacid'=>$_W['uniacid'],'id'=>$value['codeid']))['name'];
		$buy_list_all[$key]['user_id'] = $account_api->fansQueryInfo(pdo_get('ly_photobook_user',array('uniacid'=>$_W['uniacid'],'id'=>$value['user_id']))['openid'])['nickname'];
	}

	foreach ($buy_list_all as $key => $value) {
		# code...
		if(strstr($value['user_id'], $_GPC['keyword']))
			$buy_list[] = $value;
	}
}else{
	$buy_list = pdo_fetchall('select * from ims_ly_photobook_code_order where uniacid='.$_W['uniacid'].' and status =1 order by id desc limit '.($pindex-1)*$psize.','.$psize);
	foreach ($buy_list as $key => $value) {
	# code...
		$buy_list[$key]['codeid'] = pdo_get('ly_photobook_codes',array('uniacid'=>$_W['uniacid'],'id'=>$value['codeid']))['name'];
		$buy_list[$key]['user_id'] = $account_api->fansQueryInfo(pdo_get('ly_photobook_user',array('uniacid'=>$_W['uniacid'],'id'=>$value['user_id']))['openid'])['nickname'];
	}
	$total = pdo_fetchcolumn("SELECT COUNT(*) FROM " . tablename('ly_photobook_code_order') . " where uniacid=".$_W['uniacid']." and status=1");

	$pager = pagination($total, $pindex, $psize);
}

include $this->template('code_buy');

?>