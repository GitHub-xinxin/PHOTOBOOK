<?php
/**
 * 照片书模块处理程序
 *
 * @author leyao
 * @url http://bbs.we7.cc/
 */
defined('IN_IA') or exit('Access Denied');

class PhotobookModuleProcessor extends WeModuleProcessor {

	public function respond() {
		load()->func('logging');
		logging_run("====================================");
		// 已经关注回复关键字触发的
		if ($this->message['msgtype'] == 'text'||($this->message['msgtype'] == 'event'&&$this->message['event'] == 'CLICK')) {
			$openid = $this->message['from'];
			// 更新用户信息
			$mc=$this->updateUserInfo($openid);
			// 获取回复规则id
			$rid = $this->rule;
			$poster = pdo_fetch('select * from '.tablename('ly_photobook_poster')." where rule_id='{$rid}'");
			if(!empty($poster)){
				// 回复消息
				if ($poster['winfo1']){
					$this->sendText($this->message['from'],$poster['winfo1']);
				}
			}
			// 开始做海报
			
			$this->writelog('开始做海报');
			include 'tools/posterTools.php';
			$img = createMPoster($mc,$poster,'ly_photobook',0);
			$this->recordlog('img:'.$img);
			$this->writelog('做完海报');
			$media_id = $this->uploadImage($img);
			// $this->recordlog('media_id:'.$media_id);
			return $this->respImage($media_id);
			// return $this->respText('');
		}else if($this->message['msgtype']=='event' && $this->message['event']=='subscribe'){
			// 进入关注事件，触发
			$this->recordlog('进入关注...');
			$this->subscribe($this->message['scene']);

		}else if($this->message['msgtype']=='event' && $this->message['event']=='SCAN'){
			//已经关注公众号扫码,重新扫码事件
			/**
			 * step1
			 */
			$this->subscribe($this->message['scene']);
		}
	}
	private function randFloat($min=0, $max=1){
		return $min + mt_rand()/mt_getrandmax() * ($max-$min);
	}

	// 关注事件，处理上下级关系
	private function subscribe($scene_id){
		global $_W;
		// 更新用户信息
		$openid=$this->message['from'];
		$mc=$this->updateUserInfo($openid);
		// 插入到user表
		if(!pdo_get('ly_photobook_user',array('openid'=>$_W['openid']))){
			$insert=array(
				'openid'=>$_W['openid'],
				'uniacid'=>$_W['uniacid'],
				'dealer'=>-1,
				'commission'=>0,
				'agent_code'=>''
			);
			pdo_insert('ly_photobook_user',$insert);
		}
		// 获取上级海报分享记录
		$share = pdo_fetch('select * from '.tablename("ly_photobook_share")." where sceneid='{$scene_id}' and uniacid='{$_W['uniacid']}'");
		// 获取自己的分享记录
		$sql='select * from '.tablename("ly_photobook_share")." where openid='{$openid}' and uniacid={$_W['uniacid']} limit 1";
		$selfShare = pdo_fetch($sql);
		if(empty($selfShare)){
			// 还没有分享记录
			if(!empty($share)){
				// 上级分享记录存在
				if($share['openid']!=$openid){
					// 如果上级不是自己，获取活动海报
					$poster = pdo_fetch('select * from '.tablename("ly_photobook_poster")." where id='{$share['posterid']}'");
					if(!empty($poster)){
						// 给自己
						if ($poster['ftips']){
							$text = str_replace('#昵称#',$mc['nickname'],$poster['ftips']);
							$text = str_replace('#上级#',$share['nickname'],$text);		
							$this->sendText($openid,$text);//发送关注信息
						}
						if ($poster['winfo1']){
							$this->sendText($openid,$poster['winfo1']);
						}

						// 给上级发消息
						if ($poster['utips']){
							$text = str_replace('#昵称#',$mc['nickname'],$poster['utips']);
							$this->sendText($share['openid'],$text);
						}

						// 海报存在，生成自己的海报，建立上下级关系
						include 'tools/posterTools.php';
						$parentid=$share['id'];
						$img = createMPoster($mc,$poster,'ly_photobook',$parentid);
						$media_id = $this->uploadImage($img); 
						$this->send_temp_message($openid,$share['openid']);
						$parent_userid = pdo_get('ly_photobook_user',array('uniacid'=>$_W['uniacid'],'openid'=>$share['openid']))['id'];
						//检查团队中是否有人达到团长或合伙人身份
						return $this->sendImage($openid,$media_id);
					}	
				}	
			}
		}elseif(empty($selfShare['parentid'])){//有海报，但是上级为空
			// 上级分享记录存在
			if(!empty($share)){
				//如果上级不是自己，获取活动海报
				if($share['openid']!=$openid){
					//检查上下级关系，避免环形
					if(!$this->check_parent($openid,$share['openid'])){
						//将自己分享记录的上级parentid改为上级id
						$res = pdo_update('ly_photobook_share',array('parentid'=>$share['id']),array('id'=>$selfShare['id']));
						if($res){
							$this->sendText($openid,'成功与'.$share['nickname'].'建立上下级关系!');
							$this->sendText($share['openid'],'粉丝'.$mc['nickname'].'加入了您的团队！');
							$this->maxTeamCount = 0;
							$this->identity($this->sid2uid($selfShare['id']));//有代理加入，更改团队各上级人员代理数量，并检查是否有人升级为团长或合伙人
							/**
							 * 发送佣金
							 */
							$this->send_temp_message($openid,$share['openid']);
						}else
							$this->sendText($openid,'与'.$share['nickname'].'建立上下级关系失败了!');
					}else{
						$this->sendText($openid,'与'.$share['nickname'].'建立上下级关系失败了,您可能是他上级');
					}
				}
			}
		}
	}
	
	/**
	 * 向上遍历团队，检查是否有人升级团长或合伙人
	 * @join user表id
	 */
	public function identity($join){
		
		if($this->id2info($join)['dealer'] != -1){
			$flag = 1;//直接上级开关
			$sid = pdo_get('ly_photobook_share',array('id'=>$this->uid2sid($join)))['parentid'];
			while($sid){//一直向上遍历，直到上级为平台 更改团队teamcount人数
				$uid = $this->sid2uid($sid);
				if($flag){//直接上级需更改  self_count(直接上级)与team_count(团队人数)
					pdo_update('ly_photobook_user',array('team_count +='=>1,'self_count +='=>1),array('id'=>$uid));
					$flag = 0;
				}else{//直接上级以上人员 需更改team_count
					pdo_update('ly_photobook_user',array('team_count +='=>1),array('id'=>$uid));
				}
				if(date('Y-m-d',$this->id2info($uid)['day']) == date('Y-m-d',time()) || empty($this->id2info($uid)['day']))
					pdo_update('ly_photobook_user',array('new_add +='=>1,'day'=>time()),array('id'=>$uid));
				else
					pdo_update('ly_photobook_user',array('new_add'=>1,'day'=>time()),array('id'=>$uid));
				$this->isUpgrade($uid);//检查是否升级为团长或合伙人 
				$sid = pdo_get('ly_photobook_share',array('id'=>$sid))['parentid'];
			}
		}
	}

	/**
	 * 发送佣金模板消息
	 */
	private function send_temp_message($openid,$parent){
		global $_W;
		include 'TemplateMessage.php';
		load()->model('mc');
		$mc = mc_fetch($openid);
		$num = $this->randFloat()/10;
		$num = round($num,2);
		while($num < 0.01){
			$num = $this->randFloat()/10;
			$num = round($num,2);
		}
		$send_mess = new templatemessage();
		$send_arr = [
			'first'=>'恭喜您有新的粉丝加入，获得了'.$num.'元的佣金',
			'k1'=>$mc['nickname'],
			'k2'=>$num.'元',
			'k3'=>date('Y-m-d H:i:s',time()),
			'rem'=>'你可以到【发现】-【照片书总代】-拉粉奖励 中查看更多信息',
			'openid'=>$parent,
			'mid1'=>'2D5D0-Pq7WE7ngtUID7HsMXAM5u3GbFBdHYo8cw6eMY',
			'url'=>''
		];
		$send_mess->send_momey_mess($send_arr);
		$userid = pdo_get('ly_photobook_user',array('openid'=>$parent,'uniacid'=>$_W['uniacid']))['id'];
		pdo_insert('ly_photobook_user_rebate',array('from_user'=>$openid,'userid'=>$userid,'money'=>$num,'remark'=>'新增粉丝奖励','type'=>1,'uniacid'=>$_W['uniacid'],'createtime'=>time()));
	}
	// 更新用户信息
	private function updateUserInfo($openid){
		load()->model('mc');
		$mc = mc_fetch($openid);
		if (empty($mc['nickname']) || empty($mc['avatar']) || empty($mc['resideprovince']) || empty($mc['residecity'])){
			$ACCESS_TOKEN = $this->getAccessToken();
			$url = "https://api.weixin.qq.com/cgi-bin/user/info?access_token={$ACCESS_TOKEN}&openid={$openid}&lang=zh_CN";
			load()->func('communication');
			$json = ihttp_get($url);
			$userInfo = @json_decode($json['content'], true);
			if ($userInfo['nickname']) $mc['nickname'] = $userInfo['nickname'];
			if ($userInfo['headimgurl']) $mc['avatar'] = $userInfo['headimgurl'];
			$mc['resideprovince'] = $userInfo['province'];
			$mc['residecity'] = $userInfo['city'];
			mc_update($openid,array('nickname'=>$mc['nickname'],'avatar'=>$mc['avatar'],'resideprovince'=>$mc['resideprovince'],'residecity'=>$mc['residecity']));
		}
		return $mc;
	}
	private function writelog($data){
		file_put_contents(IA_ROOT."/addons/photobook/log1.txt","\n".date('Y-m-d H:i:s',time())." : ".$data,FILE_APPEND);
	}


	/**
	 * 检查上下级关系 是否树形结构
	 */
	private function check_parent($self,$scan){
		global $_W;

		$selfid = pdo_get('ly_photobook_share',array('openid'=>$self,'uniacid'=>$_W['uniacid']))['id'];
		$scanid = pdo_get('ly_photobook_share',array('openid'=>$scan,'uniacid'=>$_W['uniacid']))['parentid'];
		while (!empty($selfid) && !empty($scanid) && $selfid != $scanid) {
			$scanid = pdo_get('ly_photobook_share',array('id'=>$scanid,'uniacid'=>$_W['uniacid']))['parentid'];
		}
		return $selfid == $scanid ? true : false;
	}

	// 发送文字消息
	public function sendText($openid, $text) {
		$post = '{"touser":"' . $openid . '","msgtype":"text","text":{"content":"' . $text . '"}}';
		$ret = $this->sendRes($this->getAccessToken(), $post);
		return $ret;
	}

	// 真实发送并返回结果
	private function sendRes($access_token, $data) {
		$url = "https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token={$access_token}";
		load()->func('communication');
		$ret = ihttp_request($url, $data);
		$content = @json_decode($ret['content'], true);
		return $content['errcode'];
	}

	// 获取accesstoken
	private function getAccessToken() {
		global $_W;
		load()->model('account');
		$acid = $_W['acid'];
		if (empty($acid)) {
			$acid = $_W['uniacid'];
		}
		$account = WeAccount::create($acid);
		$token = $account->fetch_available_token();
		return $token;
	}

	// 上传图片到微信,获取media_id
	private function uploadImage($img) {
		$url = "http://file.api.weixin.qq.com/cgi-bin/media/upload?access_token=".$this->getAccessToken()."&type=image";
		$post = array('media' => '@' . $img);
		load()->func('communication');
		$ret = ihttp_request($url, $post);
		$content = @json_decode($ret['content'], true);
		// $this->recordlog('upload:'.$ret['content']);
		return $content['media_id'];
	}

	// 发送图片
	public function sendImage($openid, $media_id) {
		$data = array(
			"touser"=>$openid,
			"msgtype"=>"image",
			"image"=>array("media_id"=>$media_id));
		$ret = $this->sendRes($this->getAccessToken(), json_encode($data));
		return $ret;
	}

	private function recordlog($data){
		file_put_contents(IA_ROOT."/addons/photobook/log.txt","\n".date('Y-m-d H:i:s',time())." : ".$data,FILE_APPEND);
	}
	/**
	 * id查用户user信息
	 */
	public function id2info($join){
		return pdo_get('ly_photobook_user',array('id'=>$join));
	}
	/**
	 * shareid 转 userid
	 */
	public function sid2uid($sid){
		$openid = pdo_get('ly_photobook_share',array('id'=>$sid))['openid'];
		return pdo_get('ly_photobook_user',array('openid'=>$openid))['id'];
	}
	public function uid2sid($uid){
		global $_GPC,$_W; 
		$openid = pdo_get('ly_photobook_user',array('id'=>$uid))['openid'];
		return pdo_get('ly_photobook_share',array('uniacid'=>$_W['uniacid'],'openid'=>$openid))['id'];
	}
	/**
	 *  获取设置信息
	 */
	public function getSetting(){
		global $_W;
		return pdo_get('ly_photobook_setting',array('uniacid'=>$_W['uniacid']));
	}
	/**
	 * 查找团队中团长人数最多的数量
	 * @join  share表id
	 * 团长人数最多的值存入 属性值maxTeamCount值中
	 */
	public function get_maxTeamCount($join){

		$sublist = pdo_getall('ly_photobook_share',array('parentid'=>$join));
		if(empty($sublist)){
			return ;
		}else{
			foreach ($sublist as $key => $value) {
				$uid = $this->sid2uid($value['id']);
				$user = pdo_get('ly_photobook_user',array('id'=>$uid));
				if($user['dealer'] != -1 && $user['identity'] == 1){//团长身份
					if($this->maxTeamCount > $value['team_count'])
						$this->maxTeamCount = $value['team_count'];
				}
				$this->get_maxTeamCount($value['id']);
			}
		}
	}
	private $maxTeamCount;
	/**
	 * 判断是否成为合伙人或团长
	 * @join  user表id
	 */
	public function isUpgrade($join){
		$setVal = $this->getSetting();
		$info = $this->id2info($join);
		if($info['team_count'] >= $setVal['partner_partner_cout'] && $info['self_count'] >= $setVal['partner_direct_cout'] && $info['dealer'] != -1){//合伙人身份 
			$shareid = $this->uid2sid($join);
			$this->maxTeamCount = 0;//清空团队人数最多计数器
			$this->get_maxTeamCount($shareid);
			if($info['team_count'] - $this->maxTeamCount >= $setVal['team_max_count']){//减去团队团长小团队最多人数
				pdo_update('ly_photobook_user',array('identity'=>2),array('id'=>$join));
			}
		}elseif($info['team_count'] >= $setVal['team_team_count'] && $info['self_count'] >= $setVal['team_direct_cout'] && $info['dealer'] != -1){//团长身份
			pdo_update('ly_photobook_user',array('identity'=>1),array('id'=>$join));
		}else{
			pdo_update('ly_photobook_user',array('identity'=>0),array('id'=>$join));//取消代理或上下级关系时，会用到
		}
	}
}