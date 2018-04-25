<?php
 function trimPx($data) {
	$data['left'] = intval(str_replace('px', '', $data['left'])) * 2;
	$data['top'] = intval(str_replace('px', '', $data['top'])) * 2;
	$data['width'] = intval(str_replace('px', '', $data['width'])) * 2;
	$data['height'] = intval(str_replace('px', '', $data['height'])) * 2;
	$data['size'] = intval(str_replace('px', '', $data['size'])) * 2;
	$data['src'] = tomedia($data['src']);
	return $data;
}
function imagecreates($bg) {
	$bgImg = @imagecreatefromjpeg($bg);

	// echo $bg;

	if (FALSE == $bgImg) {
		$bgImg = @imagecreatefrompng($bg);
	}
	if (FALSE == $bgImg) {
		$bgImg = @imagecreatefromgif($bg);
	}
	if (FALSE == $bgImg) {
		$bgImg = 1;
	}
	return $bgImg;
}
//暂时不用
function imagecreates3($bg) {
	$bgImg = @imagecreatefromjpeg($bg);
	
	if (FALSE == $bgImg) {
		$bgImg = @imagecreatefrompng($bg);
	}
	if (FALSE == $bgImg) {
		$bgImg = @imagecreatefromgif($bg);
	}
	// echo "循环一次 <br>";
	// echo $bg;
	return $bgImg;
}

function imagecreates2($bg) {
	switch (pathinfo($bg)['extension']) {
		case 'png':
			$bgImg = @imagecreatefrompng($bg);
			// imagesavealpha($bgImg,true);
			break;
		case 'jpg':
		case 'jpeg':
			$bgImg = @imagecreatefromjpeg($bg);
			break;
		case 'gif':
			$bgImg = @imagecreatefromgif($bg);
			break;
	}
	return $bgImg;
}


//根据原图生成照片书（管理后台）
function createzLeafWeb($trimarray,$data,$T_photo,$img){
	$T_size = getimagesize(tomedia($T_photo));
	// 创建一个和模板图宽高一样的画布
	$target = imagecreatetruecolor($T_size[0], $T_size[1]);
	foreach ($data as $key => $invalue) {
		if($invalue['type']=='img'){
			$imgid=$trimarray[$key]['imageId'];
			$userImg=pdo_get('ly_photobook_user_images',array('id'=>$imgid),array('original'))['original'];
			// 用户图
			$U_photo=tomedia($userImg);
			$U_size = getimagesize($U_photo);
			// 定义选框的宽高
			$inxy=array(
				(int)$T_size[0]/318*$invalue['width'],
				(int)$invalue['height']*$T_size[0]/318,
			);
			// 定义用户图在模板上的坐标
			$xy=array(
				'x'=>(int)($T_size[0]*$invalue['left']/318),
				'y'=>(int)($T_size[0]*$invalue['top']/318)
			);
			// 创建背景(以用户图为背景)
			$bg_user = imagecreates($U_photo);
			if($trimarray[$key]['roate']){
				if(abs($trimarray[$key]['roate'])%90==0){
					$t=$U_size[1];
					$U_size[1]=$U_size[0];
					$U_size[0]=$t;
				}
				$bg_user = imagerotate($bg_user, 0-$trimarray[$key]['roate'], 0);	
			}

			if(!empty($trimarray[$key]['Xleft']) && !empty($trimarray[$key]['Xtop'])){
				// 人为调整后的生成
				// 定义选框的宽高
				$inxy=array(
					(int)$T_size[0]/318*$invalue['width'],
					(int)$invalue['height']*$T_size[0]/318,
				);
				//根据选框最大值，算出另一个边的长度，得到缩放后的用户图片宽度和高度 
				if($inxy[0]/$inxy[1]>$U_size[0]/$U_size[1]){
					$w=$inxy[0]; 
					$h=$U_size[1]*($inxy[0]/$U_size[0]); 
				}else{
					$h=$inxy[1]; 
					$w=$U_size[0]*($inxy[1]/$U_size[1]); 
				}
				// 1:把用户图缩放
				// 按照缩放后的尺寸创建画布
				$resize_user_img=imagecreatetruecolor($w,$h); 
				// 将原图资源放在画布上进行缩放
				imagecopyresampled($resize_user_img, $bg_user, 0, 0,0,0,$w,$h,$U_size[0],$U_size[1]);
				imagedestroy($bg_user);
				// 导出放大缩小后的用户图
				// imagepng($resize_user_img, ATTACHMENT_ROOT."BOOKS/user_".uniqid().".png");
				// 2:按照筐裁剪
				// 创建一个和筐一样的画布
				$kuang=imagecreatetruecolor($inxy[0],$inxy[1]);
				// 相对于筐的偏移位置,都是正值
				// 居中的算法
				// $klt=array(
				// 	'x'=>($w-$inxy[0])/2,
				// 	'y'=>($h-$inxy[1])/2,
				// );
				// 用户编辑后的
				$klt=array(
					'x'=>abs(3*trim($trimarray[$key]['Xleft'],'px')),
					'y'=>abs(3*trim($trimarray[$key]['Xtop'],'px')),
				);
				imagecopyresampled($kuang, $resize_user_img, 0, 0, $klt['x'], $klt['y'], $w, $h, $w,$h); 
				imagedestroy($resize_user_img);
				// 导出裁剪后的图
				// imagepng($kuang, ATTACHMENT_ROOT."BOOKS/kuang_".uniqid().".png");
				// 筐的位置
				$xy=array(
					'x'=>(int)($T_size[0]*$invalue['left']/318),
					'y'=>(int)($T_size[0]*$invalue['top']/318)
				);
				imagecopy($target, $kuang,$xy['x'],$xy['y'], 0, 0,$inxy[0], $inxy[1]);
				imagedestroy($kuang);
			}else{
								//根据选框最大值，算出另一个边的长度，得到缩放后的用户图片宽度和高度 
				if($inxy[0]/$inxy[1]>$U_size[0]/$U_size[1]){
					$w=$inxy[0]; 
					$h=$U_size[1]*($inxy[0]/$U_size[0]); 
				}else{
					$h=$inxy[1]; 
					$w=$U_size[0]*($inxy[1]/$U_size[1]); 
				}
				
				//声明一个和筐一样的画布，用来保存缩放用户图的。
				$resize_user_image=imagecreatetruecolor($inxy[0], $inxy[1]); 
				// 关键函数，参数（目标资源，源，目标资源的开始坐标x,y, 源资源的开始坐标x,y,目标资源的宽高w,h,源资源的宽高w,h） 
				imagecopyresampled($resize_user_image, $bg_user, 0, 0, ($w-$inxy[0])/2, ($h-$inxy[1])/2, $w, $h, $U_size['0']-($w-$inxy[0])/2, $U_size['1']-($h-$inxy[1])/2); 
				// 导出放大缩小后的用户图
				// imagepng($resize_user_image, ATTACHMENT_ROOT."BOOKS/user".uniqid().".png");
				// 把缩放后的用户图放到画布上，选框位置上
				imagecopy($target, $resize_user_image, $xy['x'],$xy['y'], 0, 0,$inxy[0], $inxy[1]);
				imagedestroy($resize_user_image);
			}	
		}else if($invalue['type']=='name' && !empty($trimarray[$key]['text'])){
			// 文字
			$text=autowrap($invalue['size'], 0, $trimarray[$key]['text'], (int)$T_size[0]/318*$invalue['width']);
			$textData=array('size'=>$invalue['size'],'color'=>$invalue['color'],'left'=>(int)$T_size[0]/318*$invalue['left'],'top'=>(int)$T_size[0]/318*$invalue['top']);
		}
	}
	mergeImage($target,tomedia($T_photo),array('left'=>0,'top'=>0,'width'=>$T_size[0],'height'=>$T_size[1]));
	if(!empty($text)){
		// echo $text;
		mergeText('',$target,$text,$textData);
	}
	imagepng($target, $img);
	imagedestroy($target);
}

// 创建一页（手机端）
function createzLeaf($trimarray,$data,$T_photo,$img,$id='',$window_W=''){
	$T_size = getimagesize(tomedia($T_photo));
	// 创建一个和模板图宽高一样的画布
	$target = imagecreatetruecolor($T_size[0], $T_size[1]);
	foreach ($data as $key => $invalue) {
		if($invalue['type']=='img'){
			// 用户图
			$U_photo=tomedia($trimarray[$key]['imgurl']);
			$U_size = getimagesize($U_photo);
			// 根据原图获取原图资源
			$bg_user = imagecreates($U_photo);
			if($trimarray[$key]['roate']){
				if(abs($trimarray[$key]['roate'])%90==0){
					$t=$U_size[1];
					$U_size[1]=$U_size[0];
					$U_size[0]=$t;
				}
				$bg_user = imagerotate($bg_user, 0-$trimarray[$key]['roate'], 0);	
			}
			if(!$id){
				if($trimarray[$key]['roate']){
					if(abs($trimarray[$key]['roate'])%90==0){
						$t2=$trimarray[$key]['height'];
						$trimarray[$key]['height']=$trimarray[$key]['width'];
						$trimarray[$key]['width']=$t2;
					}
				}
				// 人为调整后的生成
				// 定义选框的宽高
				$inxy=array(
					(int)$T_size[0]/318*$invalue['width'],
					(int)$invalue['height']*$T_size[0]/318,
				);
				//根据选框最大值，算出另一个边的长度，得到缩放后的用户图片宽度和高度 
				if($inxy[0]/$inxy[1]>$U_size[0]/$U_size[1]){
					$w=$inxy[0]; 
					$h=$U_size[1]*($inxy[0]/$U_size[0]); 
				}else{
					$h=$inxy[1]; 
					$w=$U_size[0]*($inxy[1]/$U_size[1]); 
				}
				// 1:把用户图缩放
				// 按照缩放后的尺寸创建画布
				$resize_user_img=imagecreatetruecolor($w,$h); 
				// 将原图资源放在画布上进行缩放
				imagecopyresampled($resize_user_img, $bg_user, 0, 0,0,0,$w,$h,$U_size[0],$U_size[1]);
				imagedestroy($bg_user);
				// 导出放大缩小后的用户图
				// imagepng($resize_user_img, ATTACHMENT_ROOT."BOOKS/user_".uniqid().".png");
				// 2:按照筐裁剪
				// 创建一个和筐一样的画布
				$kuang=imagecreatetruecolor($inxy[0],$inxy[1]);
				// 相对于筐的偏移位置,都是正值
				// 居中的算法
				// $klt=array(
				// 	'x'=>($w-$inxy[0])/2,
				// 	'y'=>($h-$inxy[1])/2,
				// );
				// 用户编辑后的
				$klt=array(
					'x'=>abs(318/$window_W*trim($trimarray[$key]['Xleft'],'px')),
					'y'=>abs(318/$window_W*trim($trimarray[$key]['Xtop'],'px')),
				);
				// 关键函数，参数（目标资源，源，目标资源的开始坐标x,y, 源资源的开始坐标x,y,目标资源的宽高w,h,源资源的宽高w,h） 
				imagecopyresampled($kuang, $resize_user_img, 0, 0, $klt['x'], $klt['y'], $w, $h, $w,$h); 
				imagedestroy($resize_user_img);
				// 导出裁剪后的图
				// imagepng($kuang, ATTACHMENT_ROOT."BOOKS/kuang_".uniqid().".png");
				// 筐的位置
				$xy=array(
					'x'=>(int)($T_size[0]*$invalue['left']/318),
					'y'=>(int)($T_size[0]*$invalue['top']/318)
				);
				imagecopy($target, $kuang,$xy['x'],$xy['y'], 0, 0,$inxy[0], $inxy[1]);
				imagedestroy($kuang);
			}else{
				// 程序图片自适应
				// 定义选框的宽高
				$inxy=array(
					(int)$T_size[0]/318*$invalue['width'],
					(int)$invalue['height']*$T_size[0]/318,
				);
				// 定义用户图在模板上的坐标
				$xy=array(
					'x'=>(int)($T_size[0]*$invalue['left']/318),
					'y'=>(int)($T_size[0]*$invalue['top']/318)
				);
				
				//根据选框最大值，算出另一个边的长度，得到缩放后的用户图片宽度和高度 
				if($inxy[0]/$inxy[1]>$U_size[0]/$U_size[1]){
					$w=$inxy[0]; 
					$h=$U_size[1]*($inxy[0]/$U_size[0]); 
				}else{
					$h=$inxy[1]; 
					$w=$U_size[0]*($inxy[1]/$U_size[1]); 
				}
				
				//声明一个和筐一样的画布，用来保存缩放用户图的。
				$resize_user_image=imagecreatetruecolor($inxy[0], $inxy[1]); 
				// 关键函数，参数（目标资源，源，目标资源的开始坐标x,y, 源资源的开始坐标x,y,目标资源的宽高w,h,源资源的宽高w,h） 
				imagecopyresampled($resize_user_image, $bg_user, 0, 0, ($w-$inxy[0])/2, ($h-$inxy[1])/2, $w, $h, $U_size['0']-($w-$inxy[0])/2, $U_size['1']-($h-$inxy[1])/2); 
				// 导出放大缩小后的用户图
				// imagepng($resize_user_image, ATTACHMENT_ROOT."BOOKS/user".uniqid().".png");
				// 把缩放后的用户图放到画布上，选框位置上
				imagecopy($target, $resize_user_image, $xy['x'],$xy['y'], 0, 0,$inxy[0], $inxy[1]);
				imagedestroy($resize_user_image);
				// echo '内层循环完成';
				$trimarray[$key]['top']=$xy['x'].'px';
				$trimarray[$key]['left']=$xy['y'].'px';
				$trimarray[$key]['width']=$w.'px';
				$trimarray[$key]['height']=$h.'px';
			}	
		}else if($invalue['type']=='name' && !empty($trimarray[$key]['text'])){
			// 文字
			$text=autowrap($invalue['size'], 0, $trimarray[$key]['text'], (int)$T_size[0]/318*$invalue['width']);
			$textData=array('size'=>$invalue['size'],'color'=>$invalue['color'],'left'=>(int)$T_size[0]/318*$invalue['left'],'top'=>(int)$T_size[0]/318*$invalue['top']);
		}
	}
	mergeImage($target,tomedia($T_photo),array('left'=>0,'top'=>0,'width'=>$T_size[0],'height'=>$T_size[1]));
	if(!empty($text)){
		// echo $text;
		mergeText('',$target,$text,$textData);
	}
	imagepng($target, $img);
	imagedestroy($target);

	if(!$id){
		// 人为调整后的生成
		// exit;
		return $trimarray;
	}else{
		// 默认方式生成
		// 更新数据库
		$trim=json_encode($trimarray);
		pdo_update('ly_photobook_order_sub',array('img_path'=>$img,'trim'=>$trim),array('id'=>$id));
	}
}

////////////
// 文字自动换行 //
////////////
function autowrap($fontsize, $angle, $string, $width) {
    // 这几个变量分别是 字体大小, 角度, 字符串, 预设宽度
    $content = "";
    //以下换成您自己的字体
    $fontface = IA_ROOT . '/web/resource/fonts/msyhbd.ttf';
    // 将字符串拆分成一个个单字 保存到数组 letter 中
    for ($i=0;$i<mb_strlen($string,'utf-8');$i++) {
        $letter[] = mb_substr($string, $i, 1,'utf-8');
    }
    foreach ($letter as $l) {
        $teststr .= $l;
        $testbox = imagettfbbox($fontsize, $angle, $fontface, $teststr);
        // 判断拼接后的字符串是否超过预设的宽度
        if (($testbox[2] > $width) && ($content !== "")) {
            $content .= "\n";
            $teststr=$l;
        }
        $content .= $l;
    }
    return $content;
}

//创建一页
function createzLeaf_2($T_img,$U_img,$inxy,$xy,$newurl,$r){
	// var_dump($inxy);
	$T_size = getimagesize($T_img);
	$U_size = getimagesize($U_img);
	// 创建一个和模板图宽高一样的画布
	$target = imagecreatetruecolor($T_size[0], $T_size[1]);
	// 创建背景(以用户图为背景)
	$bg_user = imagecreates($U_img);
	// 旋转
	$bg_user = imagerotate($bg_user, 0-$r, 0);
	// var_dump($r);

	//根据选框最大值，算出另一个边的长度，得到缩放后的用户图片宽度和高度 
	if($inxy[0]/$inxy[1]>$U_size[0]/$U_size[1]){
		$w=$inxy[0]; 
		$h=$U_size[1]*($inxy[0]/$U_size[0]); 
	}else{
		$h=$inxy[1]; 
		$w=$U_size[0]*($inxy[1]/$U_size[1]); 
	}
	
	// echo '缩放后用户图片w:'.$w.' h:'.$h;
	//声明一个$w宽，$h高的画布，用来保存缩放用户图的。
	$resize_user_image=imagecreatetruecolor($w, $h); 
	//把用户的图等比缩放到和选框一样宽高
	//关键函数，参数（目标资源，源，目标资源的开始坐标x,y, 源资源的开始坐标x,y,目标资源的宽高w,h,源资源的宽高w,h） 
	// var_dump(($w-$inxy[0])/2, ($h-$inxy[1])/2);
	imagecopyresampled($resize_user_image, $bg_user, 0, 0, ($w-$inxy[0])/2, ($h-$inxy[1])/2, $w, $h, $U_size['0'], $U_size['1']); 
	// 把缩放后的用户图放到画布上，选框位置上坐标：（72,24）
	imagecopy($target, $resize_user_image, $xy['x'],$xy['y'], 0, 0,$w, $h);
	imagepng($resize_user_image, $newurl);
	// 销毁背景
	imagedestroy($bg_user);
	imagedestroy($resize_user_image);
	// 合并图片
	
	mergeImage($target,$T_img,array('left'=>0,'top'=>0,'width'=>$T_size[0],'height'=>$T_size[1]));
	// 保存新图的路径
	imagepng($target, $newurl);
	imagedestroy($target);
}


 function mergeImage($target, $imgurl , $data) {
	$img = imagecreates($imgurl);
	$w = imagesx($img);
	$h = imagesy($img);
	imagecopyresized($target, $img, $data['left'], $data['top'], 0, 0, $data['width'], $data['height'], $w, $h);
	imagedestroy($img);
	return $target;
}
 function mergeText($m,$target ,$text , $data) {
	$font = IA_ROOT . '/web/resource/fonts/msyhbd.ttf';//字体文件
	$colors = hex2rgb($data['color']);
	$color = imagecolorallocate($target, $colors['red'], $colors['green'], $colors['blue']);
	imagettftext($target, $data['size'], 0, $data['left'], $data['top'] + $data['size'], $color, $font, $text);
	return $target;
}

function hex2rgb($colour) {
	if ($colour[0] == '#') {
		$colour = substr($colour, 1);
	}
	if (strlen($colour) == 6) {
		list($r, $g, $b) = array($colour[0] . $colour[1], $colour[2] . $colour[3], $colour[4] . $colour[5]);
	} elseif (strlen($colour) == 3) {
		list($r, $g, $b) = array($colour[0] . $colour[0], $colour[1] . $colour[1], $colour[2] . $colour[2]);
	} else {
		return false;
	}
	$r = hexdec($r);
	$g = hexdec($g);
	$b = hexdec($b);
	return array('red' => $r, 'green' => $g, 'blue' => $b);
}

/**合并图片
 * @param  $bg 背景图
 * @param  $qr 其他图
 * @param  $out 存放路径
 * @param $param 大小参数
 */
function mergeImage1($bgImg, $qr, $out, $param) {
	list($qrWidth, $qrHeight) = getimagesize($qr);
	$qrImg = imagecreates($qr);
	imagecopyresized($bgImg, $qrImg, $param['left'], $param['top'], 0, 0, $param['width'], $param['height'], $qrWidth, $qrHeight);
	ob_start();
	imagejpeg($bgImg, NULL, 100);
	$contents = ob_get_contents();
	ob_end_clean();
	imagedestroy($bgImg);
	imagedestroy($qrImg);
	$fh = fopen($out, "w+");
	fwrite($fh, $contents);
	fclose($fh);
}

/**创建图片
 * @param $bg 图片路径
 * @return
 */

function MobileSaveImage($url,$filename = '') {
	$url=ATTACHMENT_ROOT.$url;
	$urlfile = fopen($url,"r");
	$return_content = fread($urlfile,filesize($url));
	// echo "<br>";
	// echo $urlfile;
	// echo "<br>";

	$filename = ATTACHMENT_ROOT."BOOKS/temp{$filename}.jpeg";
	$forre="/www/web/huilife/public_html/attachment/BOOKS/tempbook_2.jpeg";
	// echo " =======================<br>";
	// echo $filename;
	// echo " =======================<br>";
	$fp= @fopen($filename,"w"); //将文件绑定到流 
	// var_dump($return_content);
	fwrite($fp,$return_content); //写入文件
	// exit();
	return $filename;
}


function saveImage($url,$tag = '') {
	$ch = curl_init ();
	curl_setopt ( $ch, CURLOPT_CUSTOMREQUEST, 'GET' );
	curl_setopt ( $ch, CURLOPT_SSL_VERIFYPEER, false );
	curl_setopt ( $ch, CURLOPT_URL, $url );
	ob_start ();
	curl_exec ( $ch );
	$return_content = ob_get_contents ();
	ob_end_clean ();
	$return_code = curl_getinfo ( $ch, CURLINFO_HTTP_CODE );
	$filename = IA_ROOT."/addons/photobook/poster/temp{$tag}.jpg";
	$fp= @fopen($filename,"a"); //将文件绑定到流 
	fwrite($fp,$return_content); //写入文件
	return $filename;
}

function Uid2Openid($uid){
	return pdo_fetchcolumn('select openid from '.tablename('mc_mapping_fans')." where uid='{$uid}'");
}

function createMPoster($fans,$poster,$modulename,$parentid){
	// modulename=ly_photobook
	recordlog('进入createMPoster');
	global $_W;
	$bg = $poster['bg'];
	$posterid = $poster['id'];
	$openid=Uid2Openid($fans['uid']);
	//第一批生成海报时，是没记录的
	if (empty($fans['uid'])) return '';
	$sql='select * from '.tablename($modulename."_share")." where posterid='{$posterid}' and openid='{$openid}' limit 1";
	recordlog($sql);
	$share = pdo_fetch($sql);
	recordlog('createMPoster: share===>'.json_encode($share));
	if (empty($share)){//新建分享人记录
		$idata=array(
						'openid'=>$openid,
						'nickname'=>$fans['nickname'],
						'avatar'=>$fans['avatar'],
						'posterid'=>$posterid,
						'createtime'=>time(),
						'parentid'=>$parentid,
						'image_url'=>'',
						'ticketid'=>'',
						'uniacid'=>$_W['uniacid']
				);
		pdo_insert($modulename."_share",$idata);
		$share['id'] = pdo_insertid();
		$share = pdo_fetch('select * from '.tablename($modulename."_share")." where id='{$share['id']}'");
	}else{
		recordlog('有这个人的share记录了');
		// exit();
	} 

	$qrcode = str_replace('#sid#',$share['id'],IA_ROOT ."/addons/photobook/poster/mposter#sid#.jpg");//海报的存放位置
	if(file_exists($qrcode)){
		// 如果海报存在，不再生，直接返回
		// 注意：====> 海报二维码可能过期了
		recordlog('没有生成海报，返回以前生成的');
		return $qrcode;	
	}
	
	//data是三个位置（头像，二维码等）
	$data = json_decode(str_replace('&quot;', "'", $poster['data']), true);
	set_time_limit(0);
	@ini_set('memory_limit', '256M');
	$size = getimagesize(tomedia($bg));
	$target = imagecreatetruecolor($size[0], $size[1]);
	$bg = imagecreates(tomedia($bg));
	imagecopy($target, $bg, 0, 0, 0, 0,$size[0], $size[1]);
	imagedestroy($bg);

	foreach ($data as $value) {
		$value = trimPx($value);
		if ($value['type'] == 'qr'){
			$url = getQR($fans,$poster,$share['id'],$modulename);
			recordlog($url);
			//如果有二维码
			if (!empty($url)){
				$img = IA_ROOT ."/addons/photobook/poster/temp_qrcode.png";
				$errorCorrectionLevel = "L";
				$matrixPointSize = "4";
				include 'phpqrcode.php';

				QRcode::png($url, $img, $errorCorrectionLevel, $matrixPointSize);
				mergeImage($target,$img,array('left'=>$value['left'],'top'=>$value['top'],'width'=>$value['width'],'height'=>$value['height']));
				@unlink($img);
			}
		}elseif ($value['type'] == 'img'){
			$img = saveImage($fans['avatar']);
			mergeImage($target,$img,array('left'=>$value['left'],'top'=>$value['top'],'width'=>$value['width'],'height'=>$value['height']));
			@unlink($img);
		}elseif ($value['type'] == 'name') mergeText($modulename,$target,$fans['nickname'],array('size'=>$value['size'],'color'=>$value['color'],'left'=>$value['left'],'top'=>$value['top']));
	}
	imagejpeg($target, $qrcode);
	imagedestroy($target);
	recordlog($qrcode);
	return $qrcode;
}

function getQR($fans,$poster,$share_id,$modulename){//shid：分享表中的主键
	recordlog('进入getQR');
	global $_W;
	$posterid = $poster['id'];
	//看看是否已有记录
	$share = pdo_fetch('select * from '.tablename($modulename."_share")." where id='{$share_id}'");
	recordlog(json_encode($share));
	if (!empty($share['image_url'])){
		// 已经生产了
		$out = false;
		if ($poster['rtype']){
			//若是临时二维码 需要查看时间
			$qrcode = pdo_fetch('select * from '.tablename('qrcode')
					." where uniacid='{$_W['uniacid']}' and qrcid='{$share['sceneid']}' "
					." and name='{$poster['title']}' and ticket='{$share['ticketid']}' and url='{$share['image_url']}'");
			if($qrcode['createtime'] + $qrcode['expire'] < time()){//过期
				pdo_delete('qrcode',array('id'=>$qrcode['id']));
				$out = true;
			}
		}
		if (!$out){
			// qr
			return $share['image_url'];
		}
	}

	//找出已经有的最大的场景id
	$sqlscene='select qrcid from '.tablename("qrcode")." where uniacid='{$_W['uniacid']}' order by qrcid desc limit 1";
	$sceneid = pdo_fetchcolumn($sqlscene);
	recordlog('在func内，sqlscene字符串'.$sqlscene);
	recordlog('在func内，获取的最大sceneid为'.$sceneid);
	if (empty($sceneid)){
		$sceneid = 20001;//20000是起步sceneid
	}else{
		$sceneid++;
	}
	recordlog('在func内，最后sceneid为:'.$sceneid);
	$barcode['action_info']['scene']['scene_id'] = $sceneid;

	load()->model('account');
	$acid = pdo_fetchcolumn('select acid from '.tablename('account')." where uniacid={$_W['uniacid']}");
	$uniacccount = WeAccount::create($acid);
	$time = 0;
	if ($poster['rtype']){//一个月临时二维码
		$barcode['action_name'] = 'QR_SCENE';
		$barcode['expire_seconds'] = 30*24*3600;
		$res = $uniacccount->barCodeCreateDisposable($barcode);
		$time = $barcode['expire_seconds'];
	}else{
		$barcode['action_name'] = 'QR_LIMIT_SCENE';
		$res = $uniacccount->barCodeCreateFixed($barcode);
	}
	/*
		res:
			{"ticket":"gQEu8jwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyaE1GbjhTalllYTExTzJtZTFwMTkAAgSCiaZZAwQAjScA","expire_seconds":2592000,"url":"http:\/\/weixin.qq.com\/q\/02hMFn8SjYea11O2me1p19"}
	 */

	//将二维码存于官方二维码表
	pdo_insert('qrcode',
			array('uniacid'=>$_W['uniacid'],'acid'=>$acid,'qrcid'=>$sceneid,'name'=>$poster['title'],'keyword'=>$poster['kword']
					,'model'=>1,'ticket'=>$res['ticket'],'expire'=>$time,'createtime'=>time(),'status'=>1,'url'=>$res['url']
			)
	);
	pdo_update($modulename."_share",array('sceneid'=>$sceneid,'ticketid'=>$res['ticket'],'image_url'=>$res['url']),array('id'=>$share_id));
	return $res['url'];
}

function recordlog($data){
	file_put_contents(IA_ROOT."/addons/photobook/log.txt","\n".date('Y-m-d H:i:s',time())." : ".$data,FILE_APPEND);
}
