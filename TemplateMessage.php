<?php
class TemplateMessage{
	//-----------------------------------------------------
	//订单支付成功
	public function OrderOK($openid,$data,$_url){
		$template_id='7vgofnUGeNPscpzjCGi71NnQW7YfOh4da4qRzEq4u5U';
	    $_tdata = array(
	      'first'=>array('value'=>$data['first'],'color'=>'#173177'),
	       'orderMoneySum'=>array('value'=>$data['orderMoneySum'],'color'=>'#173177'),//支付金额
	       'orderProductName'=>array('value'=>$data['orderProductName'],'color'=>'#173177'),//商品信息
	       'remark'=>array('value'=>$data['remark'],'color'=>'#173177')
	    );
	    return $this->sendTemplate_common($openid,$template_id,$_url,$_tdata);
	}
	/*↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑
		我们已收到您的货款，开始为您打包商品，请耐心等待: )
		支付金额：30.00元
		商品信息：我是商品名字
		如有问题请致电400-828-1878或直接在微信留言，小易将第一时间为您服务！
	*/
	//-----------------------------------------------------
	//商品已发出通知
	public function MerchandiseIssue($openid,$data,$_url){
		$template_id='tb19bXuttEYkWRk4e5pmSGPR-9XZSnEoZlDca5djmqQ';
	    $_tdata = array(
	      'first'=>array('value'=>$data['first'],'color'=>'#173177'),
	       'delivername'=>array('value'=>'潍坊市第七医院','color'=>'#173177'),//快递公司
	       'ordername'=>array('value'=>'唐筛检查结果','color'=>'#173177'),//快递单号
	       'remark'=>array('value'=>$data['remark'],'color'=>'#173177')
	    );
	    return $this->sendTemplate_common($openid,$template_id,$_url,$_tdata);
	}
	/*↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑
		亲，宝贝已经启程了，好想快点来到你身边
		快递公司：顺丰快递
		快递单号：3291987391
		商品信息：韩版修身中长款风衣外套
		商品数量：共10件
		备注：如果疑问，请在微信服务号中输入“KF”，**将在第一时间为您服务！
	*/	
	function sendTemplate_common($touser,$template_id,$url,$data){
     	global $_W; 
     	$weid = $_W['acid'];  
        load()->classs('weixin.account');
        $accObj= WeixinAccount::create($weid);
        $ret=$accObj->sendTplNotice($touser, $template_id, $data, $url);
        return $ret;
	}
	public function text(){
		echo "测试成功！";
	}
}