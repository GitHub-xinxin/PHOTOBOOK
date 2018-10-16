<?php
////////////
// 奖励提现申请 //
////////////
global $_W,$_GPC;
$pindex = max(1, intval($_GPC['page']));
$psize = 10;

$sql="SELECT ar.*,u.openid FROM ".tablename('ly_photobook_apply_rebate')." as ar left join ".tablename('ly_photobook_user')." u on u.id=ar.userid WHERE ar.uniacid=:uniacid AND ar.status=:status ORDER BY ar.id LIMIT ".($pindex-1)*$psize.",".$psize;
if($_GPC['status']){
	$where=array('uniacid'=>$_W['uniacid'],'status'=>1);
}else{
	$where=array('uniacid'=>$_W['uniacid'],'status'=>0);
}
$list=pdo_fetchall($sql,$where);
$total = pdo_fetchcolumn("SELECT COUNT(*) FROM " . tablename('ly_photobook_apply_rebate') . " where uniacid=:uniacid AND status=:status",$where);
$account_api = WeAccount::create();
foreach ($list as $key => $li) {
	$list[$key]['userinfo']=$account_api->fansQueryInfo($li['openid']);
}
$pager = pagination($total, $pindex, $psize);
include $this->template('rebatelist'); 