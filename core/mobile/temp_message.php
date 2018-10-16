<?php
class send_msg{
	
	public function send_momey_mess($arr){
		$_tdata =array(
			'first'=>array('value'=>$arr['first'],'color'=>'#d35400'),
			'keyword1'=>array('value'=>$arr['k1'],'color'=>'#16a085'),
			'keyword2'=>array('value'=>$arr['k2'],'color'=>'#16a085'),
			'keyword3'=>array('value'=>$arr['k3'],'color'=>'#16a085'),
			'remark'=>array('value'=>$arr['rem'],'color'=>'#95a5a6')
			);
		return $this->sendTemplate_common($arr['openid'],$arr['mid1'],$arr['url'],$_tdata);
	}
	function sendTemplate_common($touser,$template_id,$url,$data){
		global $_W; 
		$weid = $_W['acid'];  
		load()->classs('weixin.account');
		$accObj= WeixinAccount::create($weid);
		$ret=$accObj->sendTplNotice($touser, $template_id, $data, $url);
		logging_run('ret==>'.json_encode($ret));
		return $ret;
	}
}
