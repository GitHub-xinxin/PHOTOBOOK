<?php
global $_W;

$account_api = WeAccount::create();
//代金券总购买数量
$buy_total = pdo_fetch('SELECT sum(number) as count FROM '.tablename('ly_photobook_code_order').' WHERE uniacid='.$_W['uniacid'].' AND status = 1 AND user_id='.$_GPC['aid'])['count'];
//分享代金券数
$share_total = pdo_fetch('SELECT sum(number) as count FROM '.tablename('ly_photobook_share_code_log').' WHERE uniacid='.$_W['uniacid'].' AND parent='.$_GPC['aid'])['count'];
if(empty($share_total))
	$share_total = 0;
//分享明细
$share_list = pdo_getall('ly_photobook_share_code_log',array('parent'=>$_GPC['aid'],'uniacid'=>$_W['uniacid']),array(),'','insert_time desc');
foreach ($share_list as $key => $value) {
	$share_list[$key]['parent'] =  $account_api->fansQueryInfo($this->id2openid($value['parent']))['nickname'];
	$share_list[$key]['children'] =  $account_api->fansQueryInfo($this->id2openid($value['children']))['nickname'];
	$share_list[$key]['code_kind'] = pdo_get('ly_photobook_codes',array('id'=>$value['code_kind']))['name'];
}
include $this->template('code_sharelog');
?>