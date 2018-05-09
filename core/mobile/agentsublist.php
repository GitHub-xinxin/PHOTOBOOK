<?php
// 代理商下级
global $_W,$_GPC;
$coid=$_GPC['cid'];
$conum=$_GPC['cnum'];
$my_share_id = pdo_get('ly_photobook_share',array('openid'=>$_W['openid']),array('id'))['id'];
$subs = pdo_getall('ly_photobook_share',array('parentid'=>$my_share_id),array('nickname','avatar','openid'));

include $this->template('agentsublist');