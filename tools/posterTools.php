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
 function writelog($data){
	file_put_contents(IA_ROOT."/addons/photobook/log.txt","\n".date('Y-m-d H:i:s',time())." : ".$data,FILE_APPEND);
}

// 创建一页（手机端）--实际用的是这个--sun
//trimarray:修剪信息；$data：模板的框图信息；$T_photo:模板图 $img:合成图存放位置 $ordersub_id:订单页ID，$window_W：手机屏幕宽度
function createzLeaf($trimarray,$data,$T_photo,$img,$ordersub_id='',$newPage){
	//$trim_origin=clone $trimarray;
	load()->func('logging');

	if(!$newPage){
		logging_run($xy,'1-准备更新照片页的Trim值--老页更新:',"photobook");
		$trim=json_encode($trimarray);
		pdo_update('ly_photobook_order_sub',array('trim'=>$trim),array('id'=>$ordersub_id));
	}
	$ctr_ratio=3;//这个用来控制图片清晰度的
	logging_run('2-开始调用createzLeaf','info','photobook');
	logging_run($trimarray,'3-trimarray数组','photobook');
	logging_run($data,'4-data数组','photobook');
	logging_run($ordersub_id,'5-订单页id','photobook');

	logging_run('6-合成图的位置：'.$img,'info','photobook');
	$T_size = getimagesize(tomedia($T_photo));
	$T_size318_height=318/$T_size[0]*$T_size[1];
	logging_run($T_size318_height,'7-模板背景图处理后的高度','photobook');
	logging_run($T_size,'8-模板图尺寸-T_size','photobook');
	// 创建一个和模板图宽高一样的画布
	// writelog('进入函数'." empty share");
	// writelog('初始值$trimarray'.$trimarray);
	$target = imagecreatetruecolor(318*$ctr_ratio, $T_size318_height*$ctr_ratio);//按照318*2来处理，否则太模糊
	

	
	/* $testUrl_ti='/www/web/photobook/public_html/attachment/BOOKS/test/temp_book'.$ordersub_id.'_ti.png';
	imagepng($target,$testUrl_ti); */
	$flag=1;
	foreach ($data as $key => $invalue) {
		if($flag==0){
			$flag=1;
			continue;
		}
			
		logging_run('9-进入createzLeaf循环','info','photobook');
		
		if($invalue['type']=='img'){
			logging_run('10-进入createzLeaf循环的img类型','info','photobook');
			// 用户图
			$U_photo=tomedia($trimarray[$key]['imgurl']);
			logging_run($U_photo,'10-01：用户图地址','photobook');

			if(empty($U_photo)){
				logging_run('11-此处不应该出现：订单页id：'.$ordersub_id.'  key为：'.$key.' invalue'.json_encode($invalue),'error','photobook');
			}			
			
			logging_run('11-00-0-定位用','info','photobook');

			$bg_user = imagecreates($U_photo);
			logging_run('11-00-1：使用getimagesize前','info','photobook');
			//$U_size = getimagesize($U_photo);
			$U_size=array(imagesx($bg_user),imagesy($bg_user));
			logging_run($U_size,'11-00-2：使用getimagesize后，用户图尺寸','photobook');

			// 遍历步骤1：根据原用户图获取原图资源，如果有旋转，则对调长宽尺寸

			logging_run('11-02-定位用','info','photobook');

			if($trimarray[$key]['roate']){//旋转方向
				logging_run('11-03-定位用','info','photobook');
				if(abs($trimarray[$key]['roate'])==90||abs($trimarray[$key]['roate'])==270){
					$t=$U_size[1];
					$U_size[1]=$U_size[0];
					$U_size[0]=$t;
				}
				logging_run('11-04-定位用','info','photobook');
				$bg_user = imagerotate($bg_user, 0-$trimarray[$key]['roate'], 0);	
				logging_run('11-05-定位用','info','photobook');
			}
			

			logging_run('12-id为空','info','photobook');

			// //遍历步骤2：按照模板背景图为318的标准，缩放当前框的尺寸
			// 定义选框的宽高
			logging_run('13-开始步骤2','info','photobook');
			
			//之所以没有缩放，是因为发现在web端编辑时，模板页所在div宽度就是318，不知为何。注意框的宽度和模板背景实际大小无关，而是显示大小有关
			$inxy=array((float)$invalue['width'],(float)$invalue['height']);
			logging_run((float)$invalue['width'],'14-测试转换'.$key.':','photobook');
			logging_run($invalue,'15-缩放前，框图尺寸-invalue'.$key.':','photobook');
			logging_run($inxy,'16-缩放后，框图尺寸-inxy'.$key.':','photobook');
			
			//遍历步骤3：根据当前框的尺寸，按照用户图比小边填满框的原则，缩放用户图的尺寸，主要原则是用户图弱边填满框。由于框是基于318的，所以缩放后用户图也是基于318的
			//注意，在生成图片时，ordersub里的json中的图片宽度，实际上没有用到，而是取的实际值。这个json中的宽高，在前端判断宽高比时有用而已
			//w和h：用户图片匹配框大小后，经过处理后的宽高
			if($inxy[0]/$inxy[1]>$U_size[0]/$U_size[1]){
				$w=$inxy[0]; 
				$h=$U_size[1]*($inxy[0]/$U_size[0]); 
			}else{
				$h=$inxy[1]; 
				$w=$U_size[0]*($inxy[1]/$U_size[1]); 
			}				


			
			// 遍历步骤4:按照缩放后用户图的尺寸，缩放用户图，得到新图，原用户图摧毁
			// 按照缩放后的尺寸创建画布
			$resize_user_img=imagecreatetruecolor($w*$ctr_ratio,$h*$ctr_ratio); 
			// 将原用户图缩放到处理后的尺寸上，得到缩放后的图resize_user_img
			imagecopyresampled($resize_user_img, $bg_user, 0, 0,0,0,$w*$ctr_ratio,$h*$ctr_ratio,$U_size[0],$U_size[1]);//用户图放大了2倍，避免模糊
			imagedestroy($bg_user);
			
			
			// 遍历步骤5:把缩放后的用户图，放到框里去，并且位置要按照用户图和框的相对位置，实际上是把框外的用户图截掉了
			// 5-1：创建一个和框一样的画布（框已经处理过尺寸，比照模板图缩放到318后的尺寸）
			$kuang=imagecreatetruecolor($inxy[0]*$ctr_ratio,$inxy[1]*$ctr_ratio);//缩放了两倍，避免太模糊

			// 5-2：根据用户对图位置的调整，计算位置，将来用于裁剪
			//注意：前端保存时，xleft等数据未经缩放到318就传过来了，这是一个实际的相对位置，故在此要进行缩放，统一到屏幕是318这个尺寸上
			
			if(!$newPage){//如果是新建页，则计算默认值
				logging_run($ordersub_id,'17-此时为旧页，从数据库取Xleft和XTop值:',"photobook");
				$klt=array(
				'x'=>trim($trimarray[$key]['Xleft'],'px'),
				'y'=>trim($trimarray[$key]['Xtop'],'px'),
				);
			}else{
				logging_run($ordersub_id,'18-此时为新页，系统按默认值计算位置，id为:',"photobook");
				$klt=array(
				'x'=>-abs($inxy[0]-$w)/2,
				'y'=>-abs($inxy[1]-$h)/2,
				);
			}

			logging_run($klt,'19-缩放后图片相对于框的位置XY-klt:',"photobook");

			//5-3：把缩放后的用户图，结合与框的相对位置，两两合并获得合并图，然后销毁原用户图
			// 关键函数，参数（目标资源，源，目标资源的开始坐标x,y, 源资源的开始坐标x,y,目标资源的宽高w,h,源资源的宽高w,h） 
			//注意参数要加符号处理，因为PHP的坐标起点和方向虽然和H5的一样，但是destined的坐标，都是针对自身的，不是针对source左上角的
			imagecopyresampled($kuang, $resize_user_img, 0, 0, -$klt['x']*$ctr_ratio, -$klt['y']*$ctr_ratio, $w*$ctr_ratio, $h*$ctr_ratio, $w*$ctr_ratio,$h*$ctr_ratio);//放大两倍后，位置也得放大 
			imagedestroy($resize_user_img);
			
			
			//根据框的位置，把框和模板页的背景图融合在一起，当然要把位置尺寸缩放到318这个统一值上
			// 框在背景图上的相对位置，进行缩放

			$xy=array('x'=>$invalue['left'],'y'=>$invalue['top']);
			/* logging_run($xy,'缩放后框在模板页上的位置:',"photobook");
			//这个copy函数，和imagecopyresampled相比，缺少两个参数（目标图的拷贝区域大小未指定，只指定了开始点，这样就不能缩放了）
			//此处kuang这个变量，实际上是用户图和框缩放好尺寸，并且拼接到了一起的综合图像
			$testUrl='/www/web/photobook/public_html/attachment/BOOKS/test/temp_book'.$ordersub_id.'_k'.$key.'.png';
			imagepng($kuang,$testUrl);
			$testUrl_t1='/www/web/photobook/public_html/attachment/BOOKS/test/temp_book'.$ordersub_id.'_tb'.$key.'.png';
			imagepng($target,$testUrl_t1); */
			imagecopy($target, $kuang,$xy['x']*$ctr_ratio,$xy['y']*$ctr_ratio, 0, 0,$inxy[0]*$ctr_ratio, $inxy[1]*$ctr_ratio);
			/* $testUrl_t2='/www/web/photobook/public_html/attachment/BOOKS/test/temp_book'.$ordersub_id.'_ta'.$key.'.png';
			imagepng($target,$testUrl_t2); */
			imagedestroy($kuang);
			logging_run('20-位置调制的图片，在此整合完毕','info',"photobook");

			//准备保存order_sub的json串
			$trimarray[$key]['top']=$xy['x'].'px';
			$trimarray[$key]['left']=$xy['y'].'px';
			$trimarray[$key]['width']=$w.'px';
			$trimarray[$key]['height']=$h.'px';
			$trimarray[$key]['Xleft']=$klt['x'].'px';
			$trimarray[$key]['Xtop']=$klt['y'].'px';


		}else if($invalue['type']=='name' && !empty($trimarray[$key]['text'])){
			// 文字
			$text=autowrap($invalue['size'], 0, $trimarray[$key]['text'], (int)$T_size[0]/318*$invalue['width']);
			$textData=array('size'=>$invalue['size'],'color'=>$invalue['color'],'left'=>(int)$T_size[0]/318*$invalue['left'],'top'=>(int)$T_size[0]/318*$invalue['top']);
		}
	}
	mergeImage_thumb($target,tomedia($T_photo),array('left'=>0,'top'=>0,'width'=>318*$ctr_ratio,'height'=>$T_size318_height*$ctr_ratio),$ordersub_id);

	
	if(!empty($text)){
		// echo $text;
		mergeText('',$target,$text,$textData);
	}
	imagepng($target, $img);
	imagedestroy($target);

	//更新订单页的json串，不仅是新建页，也包括已有页的更改，但已有页不能经过处理，而要直接保存！
	if($newPage){
		$trim=json_encode($trimarray);
		logging_run($xy,'21-准备更新照片页的Trim值--新页首次更新:',"photobook");
		pdo_update('ly_photobook_order_sub',array('img_path'=>$img,'trim'=>$trim),array('id'=>$ordersub_id));
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

//创建一页--》可能没用该函数--sun
//参数顺序：订单页的trim值，模板的json值，模板图的位置（可能是缩略图），合成图的路径，newurl未知，手机屏幕宽度
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

//此函数经过改造，先让imgurl图片缩放的和target一样大
 function mergeImage_thumb($target, $imgurl , $data,$id) {
	$img = imagecreates($imgurl);
	//$imgUrl_img='/www/web/photobook/public_html/attachment/BOOKS/test/temp_book'.$id.'_img.png';//这一步就开始背景是黑色了，先解决这个问题
	//imagepng($img,$imgUrl_img);
	
	$w = imagesx($img);
	$h = imagesy($img);
	$img_src_resized=imagecreatetruecolor($data['width'],$data['height']);//用于缩放模板背景图的
		
	//有没有下面两行，都是黑色
	$color=imagecolorallocate($img_src_resized,0,0,0); //关键是这个设置黑色为透明色
	imagecolortransparent($img_src_resized,$color); 
	
	/* $imgUrl_emp='/www/web/photobook/public_html/attachment/BOOKS/test/temp_book'.$id.'_emp.png';//这一步就开始背景是黑色了，先解决这个问题
	$result=imagepng($img_src_resized,$imgUrl_emp);
	logging_run($result,'创建空白待缩小模板背景图画布','photobook'); */

	imagecopyresized($img_src_resized,$img,0,0,0,0,$data['width'],$data['height'],$w,$h);
	/* $testUrl='/www/web/photobook/public_html/attachment/BOOKS/test/temp_book'.$id.'_sr.png';
	imagepng($img_src_resized,$testUrl); */
	//参数：目标图，原图，目标位置，原图位置，目标尺寸，原图尺寸
	//之所以原图像在上边，那是因为源图像是png格式，要起到蒙版的效果
	imagecopyresized($target, $img_src_resized, 0, 0, 0, 0, $data['width'], $data['height'], $data['width'], $data['height']);
	imagedestroy($img);
	return $target;
}



 function mergeImage($target, $imgurl , $data) {
	$img = imagecreates($imgurl);
	$w = imagesx($img);
	$h = imagesy($img);
	//参数：目标图，原图，目标位置，原图位置，目标尺寸，原图尺寸
	//之所以原图像在上边，那是因为源图像是png格式，要起到蒙版的效果
	imagecopyresized($target, $img, $data['left'], $data['top'], 12, 12, $data['width'], $data['height'], $w-24, $h-24);
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
	} 

	$qrcode = str_replace('#sid#',$share['id'],IA_ROOT ."/addons/photobook/poster/mposter#sid#.jpg");//海报的存放位置
	if(file_exists($qrcode)){
		//重新生成新的海报，覆盖掉原先海报
		// 如果海报存在，不再生，直接返回 // 注意：====> 海报二维码可能过期了 改为永久二维码
		// recordlog('没有生成海报，返回以前生成的');
		// return $qrcode;	
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
	$flag = true;//显示微信号 true为显示手机号
	$userInfo = getUserInfo($openid);//用户信息
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
		}elseif ($value['type'] == 'name') {
			$textInfo = $flag? $userInfo['name'] : $userInfo['phone'];
			$flag = $flag? false : true;//切换微信号或手机号
			mergeText($modulename,$target,$textInfo,array('size'=>$value['size'],'color'=>$value['color'],'left'=>$value['left'],'top'=>$value['top']));
		}
	}
	imagejpeg($target, $qrcode);
	imagedestroy($target);
	recordlog($qrcode);
	return $qrcode;
}
function getUserInfo($openid){
	return pdo_get('ly_photobook_user',array('openid'=>$openid));
}
function getQR($fans,$poster,$share_id,$modulename){//shid：分享表中的主键
	recordlog('进入getQR');
	global $_W;
	$posterid = $poster['id'];
	//看看是否已有记录
	$share = pdo_fetch('select * from '.tablename($modulename."_share")." where id='{$share_id}'");
	recordlog('share记录'.json_encode($share));
	if (!empty($share['image_url'])){
		// 已经生产了
		$out = false;
		recordlog('进入share非空记录'.json_encode($share));
		recordlog('poster值：'.json_encode($poster));
		if ($poster['rtype']){
			//若是临时二维码 需要查看时间
			recordlog('进入临时二维码'.json_encode($share));
			$qrcode = pdo_fetch('select * from '.tablename('qrcode')
					." where uniacid='{$_W['uniacid']}' and qrcid='{$share['sceneid']}' "
					." and name='{$poster['title']}' and ticket='{$share['ticketid']}' and url='{$share['image_url']}'");
			recordlog('qrcode记录'.json_encode($qrcode));
			if($qrcode['createtime'] + $qrcode['expire'] < time()){//过期
				pdo_delete('qrcode',array('id'=>$qrcode['id']));
				$out = true;
			}
		}
		if (!$out){
			// qr
			recordlog('进入out');
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
		recordlog('进入poster-rtype记录'.$poster['rtype']);
		$barcode['action_name'] = 'QR_SCENE';
		$barcode['expire_seconds'] = 30*24*3600;
		$res = $uniacccount->barCodeCreateDisposable($barcode);
		recordlog('临时二维码创建返回值'.json_encode($res));
		$time = $barcode['expire_seconds'];
	}else{
		recordlog('永久二维码开始创建');
		$barcode['action_name'] = 'QR_LIMIT_SCENE';
		$res = $uniacccount->barCodeCreateFixed($barcode);
		recordlog('永久二维码创建返回值'.json_encode($res));
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
