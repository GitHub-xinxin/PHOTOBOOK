<?php
// 代理商的代金券
global $_W, $_GPC;
$p_title="我的代金券";
$user = pdo_get('ly_photobook_user',array('openid'=>$_W['openid'],'uniacid'=>$_W['uniacid']));
$user_id = $user['id'];
// list_price 标价格	dealer_price

$cards = pdo_fetchall('SELECT b.name,a.*,b.dealer_price,b.list_price,b.pic,b.id as cid FROM ims_ly_photobook_user_code AS a LEFT JOIN ims_ly_photobook_codes AS b ON a.code_id = b.id WHERE a.uniacid = '.$_W['uniacid'].' AND b.uniacid = '.$_W['uniacid'].' AND a.status =0 AND a.number >0 AND a.user_id ='.$user['id'].' GROUP BY a.code_id');

include $this->template('agentcardlist'); 