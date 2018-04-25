<?php
// 代理商的代金券
global $_W, $_GPC;
$p_title="我的代金券";
$user_id=pdo_get('ly_photobook_user',array('openid'=>$_W['openid']))['id'];
// list_price 标价格	dealer_price
$sql1='select co.*,c.dealer_price,c.list_price from '.tablename('ly_photobook_code_order').' as co left join '.tablename('ly_photobook_codes').' c on c.id=co.codeid where co.user_id=:user_id and co.number>0 and co.uniacid=:uniacid';
$cards=pdo_fetchall($sql1,array('user_id'=>$user_id,'uniacid'=>$_W['uniacid']));
include $this->template('agentcardlist');