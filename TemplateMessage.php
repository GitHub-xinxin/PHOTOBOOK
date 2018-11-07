<?php
class templatemessage{
	
	function  __construct(){

	}
	 /**
     * 接收新工单消息
     */
    public function receive_work_order($arr){
		$_tdata =array(
			'first'=>array('value'=>$arr['first'],'color'=>'#d35400'),
			'keyword1'=>array('value'=>$arr['k1'],'color'=>'#16a085'),
            'keyword2'=>array('value'=>$arr['k2'],'color'=>'#16a085'),
            'keyword3'=>array('value'=>$arr['k3'],'color'=>'#16a085'),
			'remark'=>array('value'=>$arr['rem'],'color'=>'#95a5a6')
			);
		return $this->sendTemplate_common($arr['openid'],$arr['mid1'],$arr['url'],$_tdata);
	}
	/*
	订单发货提醒
	 */
	public function order_send($arr){
		$_tdata =array(
			'first'=>array('value'=>$arr['first'],'color'=>'#1E9FFF'),
			'keyword1'=>array('value'=>$arr['k1'],'color'=>'#2F4056'),
            'keyword2'=>array('value'=>$arr['k2'],'color'=>'#2F4056'),
            'keyword3'=>array('value'=>$arr['k3'],'color'=>'#FF5722'),
			'remark'=>array('value'=>$arr['rem'],'color'=>'#5FB878')
			);
		return $this->sendTemplate_common($arr['openid'],$arr['mid1'],$arr['url'],$_tdata);
	}
    /**
     * 工单处理发送模板消息
     */
    public function work_order_done($arr){
		$_tdata =array(
			'first'=>array('value'=>$arr['first'],'color'=>'#d35400'),
			'keyword1'=>array('value'=>$arr['k1'],'color'=>'#16a085'),
            'keyword2'=>array('value'=>$arr['k2'],'color'=>'#16a085'),
            'keyword3'=>array('value'=>$arr['k3'],'color'=>'#16a085'),
			'remark'=>array('value'=>$arr['rem'],'color'=>'#95a5a6')
			);
		return $this->sendTemplate_common($arr['openid'],$arr['mid1'],$arr['url'],$_tdata);
	}
	/**
	 * 拉粉奖励
	 * @param  [type] $arr [description]
	 * @return [type]      [description]
	 */
	public function send_momey_mess($arr){
		$_tdata =array(
			'first'=>array('value'=>$arr['first'],'color'=>'#1E9FFF'),
			'keyword1'=>array('value'=>$arr['k1'],'color'=>'#FF5722'),
			'keyword2'=>array('value'=>$arr['k2'],'color'=>'#FF5722'),
			'keyword3'=>array('value'=>$arr['k3'],'color'=>'#FF5722'),
			'remark'=>array('value'=>$arr['rem'],'color'=>'#5FB878')
			);
		return $this->sendTemplate_common($arr['openid'],$arr['mid1'],$arr['url'],$_tdata);
	}
	function sendTemplate_common($touser,$template_id,$url,$data){
		global $_W; 
		$weid = $_W['acid'];  
		load()->classs('weixin.account');
		$accObj= WeixinAccount::create($weid);
		$ret=$accObj->sendTplNotice($touser, $template_id, $data, $url);
		return $ret;
	}
}
