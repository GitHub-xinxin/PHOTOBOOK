<?php
global $_W;
$account_api = WeAccount::create();

$list = pdo_getall('ly_photobook_user',array('uniacid'=>$_W['uniacid'],'dealer <>'=>-1));
foreach ($list as $key => $value) {
	$list[$key]['name'] = $account_api->fansQueryInfo($value['openid'])['nickname'];
}

include $this->template('card_sharemag');
?>