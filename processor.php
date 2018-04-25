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
		logging_run($this->message);
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
			// $this->recordlog('img:'.$img);
			$this->writelog('做完海报');
			$media_id = $this->uploadImage($img);
			// $this->recordlog('media_id:'.$media_id);
			return $this->respImage($media_id);
			// return $this->respText('');
		}else if($this->message['msgtype']=='event' && $this->message['event']=='subscribe'){
			// 进入关注事件，触发
			$this->recordlog('进入关注...');
			$this->subscribe($this->message['scene']);

		}
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
						}

						// 海报存在，生成自己的海报，建立上下级关系
						include 'tools/posterTools.php';
						$parentid=$share['id'];
						$img = createMPoster($mc,$poster,'ly_photobook',$parentid);
						$media_id = $this->uploadImage($img); 
						
						return $this->sendImage($openid,$media_id);
					}	
				}
				
			}
		}
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
}