<?php
global $_W,$_GPC;
$pindex = max(1, intval($_GPC['page']));
$psize = 20;
if($_W['isajax']){
	$openid = $_GPC['openid'];
	if(!empty($openid)){
		//step 1:将自己的parentid改为0
		$selfid = pdo_get('ly_photobook_share',array('openid'=>$openid,'uniacid'=>$_W['uniacid']));
		if($selfid){
			pdo_update('ly_photobook_share',array('parentid'=>0),array('openid'=>$openid,'uniacid'=>$_W['uniacid']));
			//step 2:将自己下级的parentid改为0
			pdo_update('ly_photobook_share',array('parentid'=>0),array('parentid'=>$selfid['id']));
			$res['code'] = 0;
			//step 3:重新检查团长或合伙人身份
			$this->check_identity_agin($this->sid2uid($selfid));
		}
	}else
		$res['code'] = 2;
	echo json_encode($res);exit;
}
if(checksubmit()){
	$clients = pdo_fetchall('SELECT u.*,f.nickname FROM ims_mc_mapping_fans AS f LEFT JOIN ims_ly_photobook_user AS u ON f.openid = u.openid WHERE u.uniacid = '.$_W['uniacid'].' and f.nickname like "%'.$_GPC['keyword'].'%"');
}else{
	$clients = pdo_fetchall('SELECT * FROM '.tablename('ly_photobook_user').' WHERE uniacid ='.$_W['uniacid'].' LIMIT '.($pindex-1)*$psize.','.$psize);
	$total = pdo_fetchcolumn("SELECT COUNT(*) FROM " . tablename('ly_photobook_user') . " where uniacid=".$_W['uniacid']);
	$pager = pagination($total, $pindex, $psize);
}

include $this->template('client_mag');
?>