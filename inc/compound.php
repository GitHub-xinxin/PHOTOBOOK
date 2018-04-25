<?php
global $_W,$_GPC;
	/**
 * 先备份模板图上传oss
 */
$template_thumb = $ordersub_id.$T_photo;
$send_data ='x-oss-process=image/resize,w_360|sys/saveas,o_'.base64_encode($template_thumb).',b_'.base64_encode('demo-photo');
$response = ihttp_post('http://demo-photo.oss-cn-beijing.aliyuncs.com/'.$T_photo.'?x-oss-process', $send_data);

foreach ($data as $key => $frame){
    /**
     * 判断是否为图片 生成临时
     */
    if($frame['type'] == 'img'){
        
        $thumb_img = "roate_".$trimarray[$key]['imgurl']; 
        if($trimarray[$key]['roate']==90 || $trimarray[$key]['roate']==-270){
            $send_data ='x-oss-process=image/resize,w_360/rotate,90|sys/saveas,o_'.base64_encode($thumb_img).',b_'.base64_encode('demo-photo');
        }elseif($trimarray[$key]['roate']==180 || $trimarray[$key]['roate']==-180){
            $send_data ='x-oss-process=image/resize,w_360/rotate,180|sys/saveas,o_'.base64_encode($thumb_img).',b_'.base64_encode('demo-photo');
        }elseif($trimarray[$key]['roate']==270 || $trimarray[$key]['roate']==-90){
            $send_data ='x-oss-process=image/resize,w_360/rotate,270|sys/saveas,o_'.base64_encode($thumb_img).',b_'.base64_encode('demo-photo');
        }else{
            $send_data ='x-oss-process=image/resize,w_360|sys/saveas,o_'.base64_encode($thumb_img).',b_'.base64_encode('demo-photo');
        }
        $response = ihttp_post('http://demo-photo.oss-cn-beijing.aliyuncs.com/'.$trimarray[$key]['imgurl'].'?x-oss-process', $send_data);
        /**
         * 获取图片的信息
         */
        $img_info = ihttp_get('http://demo-photo.oss-cn-beijing.aliyuncs.com/'.$thumb_img.'?x-oss-process=image/info');
        $de_info =json_decode($img_info['content'],true);
        /**
         * 缩率图宽高
         */
        $img_h = $de_info['ImageHeight']['value'];
        $img_w = $de_info['ImageWidth']['value'];
        /**
         * 框的宽高
         */
        $frame_w = trim($frame['width'],'px');
        $frame_h = trim($frame['height'],'px');
        $frame_top = trim($frame['top'],'px');
        $frame_left = trim($frame['left'],'px');
       
        /**
         * 计算缩放那一边
         */
        if($frame_w / $frame_h > $img_w / $img_h){
            $thumb_type = 'w'; 
            $thumb_value = $frame_w; 
        }else{
            $thumb_type = 'h'; 
            $thumb_value = $frame_h; 
        }
        /**
         * 如果是新生成的缩略图
         */
        if($trimarray[$key]['Xtop'] =='' && $trimarray[$key]['Xleft']==''){
            if($thumb_type == 'w'){
                $tailor_x = 0;
                $tailor_y = (($thumb_value / $img_w * $img_h)-$frame_h)/2;
            }elseif($thumb_type == 'h'){
                $tailor_x = (($thumb_value / $img_h * $img_w)-$frame_w)/2;
                $tailor_y = 0;
            }
        }else{
            $tailor_x = trim($trimarray[$key]['Xtop'],'px');
            $tailor_y = trim($trimarray[$key]['Xleft'],'px');
        }
       
        /**
         * 缩放与裁剪
         */ 
        $tailor_data = '/crop,x_'.round($tailor_x).',y_'.round($tailor_y).',w_'.$frame_w.',h_'.$frame_h;
        $send_data ='x-oss-process=image/resize,'.$thumb_type.'_'.$thumb_value.$tailor_data.'|sys/saveas,o_'.base64_encode($thumb_img).',b_'.base64_encode('demo-photo');
        $response = ihttp_post('http://demo-photo.oss-cn-beijing.aliyuncs.com/'.$thumb_img.'?x-oss-process', $send_data);
        
        /**
         * 合成模板
         */
        // var_dump('frame.top=='.$frame_top.' frame.left=='.$frame_left); 
        $compound_data =base64_encode($thumb_img);
        $send_data ='x-oss-process=image/watermark,image_'.$compound_data.',g_nw,x_'.round($frame_left).',y_'.round($frame_top).'|sys/saveas,o_'.base64_encode($template_thumb).',b_'.base64_encode('demo-photo');
        $response = ihttp_post('http://demo-photo.oss-cn-beijing.aliyuncs.com/'.$template_thumb.'?x-oss-process', $send_data);
        /**
         * 删除上传的临时图片
         */
        // var_dump($thumb_img);
        if($trimarray[$key]['roate']==90 || $trimarray[$key]['roate']==-270 || $trimarray[$key]['roate']==-90 || $trimarray[$key]['roate']==270){
            $trimarray[$key]['width']=$img_h.'px';
            $trimarray[$key]['height']=$img_w.'px';
        }else{
            $trimarray[$key]['width']=$img_w.'px';
            $trimarray[$key]['height']=$img_h.'px';
        } 

    }
}
/**
 * png覆盖
 */
$send_data ='x-oss-process=image/watermark,image_'.base64_encode($T_photo).',g_nw,x_0,y_0|sys/saveas,o_'.base64_encode($template_thumb).',b_'.base64_encode('demo-photo');
$response = ihttp_post('http://demo-photo.oss-cn-beijing.aliyuncs.com/'.$template_thumb.'?x-oss-process', $send_data);
/**
 * 更新数据表
 */
$trim =json_encode($trimarray);
pdo_update('ly_photobook_order_sub',array('img_path'=>$template_thumb),array('id'=>$ordersub_id));

?>