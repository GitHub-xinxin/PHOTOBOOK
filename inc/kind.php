<?php
global $_W,$_GPC;
empty($_GPC['op'])? $operation = 'list' : $operation =$_GPC['op'];

if($operation == 'list'){
    $list = pdo_getall('ly_photobook_kind',array('uniacid'=>$_W['uniacid']));
}elseif($operation == 'add'){
    if(checksubmit()){
        $data =array(
            'uniacid'=>$_W['uniacid'],
            'name'=>$_GPC['name']
        );
        if(empty($_GPC['id'])){
            $res = pdo_insert('ly_photobook_kind',$data);
        }else{
            $res = pdo_update('ly_photobook_kind',$data,array('id'=>$_GPC['id'])); 
        }
        if(!empty($res))
            message('操作成功',$this->createWebUrl('kind'),'success');
        else
            message('操作失败',$this->createWebUrl('kind'),'error');
    }else{
        $kind =  pdo_get('ly_photobook_kind',array('id'=>$_GPC['id']));
    }
}elseif($operation == 'del'){
    if(pdo_delete('ly_photobook_kind',array('id'=>$_GPC['id'])))
        message('操作成功',$this->createWebUrl('kind'),'success');
    else
        message('操作失败',$this->createWebUrl('kind'),'error');
}   
include $this->template('kind');
?>