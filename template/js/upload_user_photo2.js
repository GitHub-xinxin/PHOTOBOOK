	//	随机数函数
	function randomString(len) {
		　　len = len || 32;
		　　var $chars = 'ABCDEFGHJKMNPQRSTWXYZabcdefhijkmnprstwxyz2345678';   
		　　var maxPos = $chars.length;
		　　var pwd = '';
		　　for (i = 0; i < len; i++) {
		　　　　pwd += $chars.charAt(Math.floor(Math.random() * maxPos));
		　　}
		　　return pwd;
		}
		var policyText = {
			"expiration": "2120-01-01T12:00:00.000Z", //设置该Policy的失效时间，超过这个失效时间之后，就没有办法通过这个policy上传文件了
			"conditions": [
			["content-length-range", 0, 1048576000] // 设置上传文件的大小限制
			]
		};
		accessid= 'LTAIZlNllu4E2j6U';
		accesskey= 'S34tVsxyY0cucviwEgKwEBVLjUVNDc';
		host = 'http://demo-photo.oss-cn-beijing.aliyuncs.com';
		var policyBase64 = Base64.encode(JSON.stringify(policyText))
		message = policyBase64
		var bytes = Crypto.HMAC(Crypto.SHA1, message, accesskey, { asBytes: true }) ;
		var signature = Crypto.util.bytesToBase64(bytes);
		var tid = document.getElementsByName('tid')[0].value;
		
		var uploader = new plupload.Uploader({
			runtimes : 'html5,flash,silverlight,html4',
			browse_button : 'selectfiles', 
			filters: {
				mime_types : [ //只允许上传图片和zip文件
				{ title : "Image files", extensions : "jpg,gif,png,jpeg,bmp" }, 
				],
			},
			container: document.getElementById('container'),
			flash_swf_url : 'lib/plupload-2.1.2/js/Moxie.swf',
			silverlight_xap_url : 'lib/plupload-2.1.2/js/Moxie.xap',
			url : host,
			multipart_params: {
			},
		
			init: {
				PostInit: function() {
					document.getElementById('ossfile').innerHTML = '';	
					document.getElementById('postfiles').onclick = function() {
						uploader.start();	
						return false;
					};
				},
				//添加文件执行
				FilesAdded: function(up, files) {	
					plupload.each(files, function(file) { 
						document.getElementById('ossfile').innerHTML += '<div style="font-size:0.7em;" id="' + file.id + '">' + file.name + ' (' + plupload.formatSize(file.size) + ')<b></b>'
						+'<div class="progress"><div class="progress-bar" style="width: 0%"></div></div>'
						+'</div>';
					});
				},
				//文件上传之前执行
				BeforeUpload:function(up, file) {
					//随机生成文件名+后缀名
					var index1=file.name.lastIndexOf(".");
					var index2=file.name.length;  
					var suffix=file.name.substring(index1,index2);//后缀名   
					var filename=randomString(10)+suffix
					file.name =filename
					//重新设置参数  
					uploader.setOption('multipart_params',{
						'Filename':filename,
						'key' :filename, 
						'policy': policyBase64,
						'OSSAccessKeyId': accessid, 
						'success_action_status' : '200', //让服务端返回200,不然，默认会返回204
						'signature': signature
					})
				},
				//文件上传过程中执行 会一直触发 
				UploadProgress: function(up, file) {
					var d = document.getElementById(file.id);
					d.getElementsByTagName('b')[0].innerHTML = '<span>' + file.percent + "%</span>";     
					var prog = d.getElementsByTagName('div')[0];
					var progBar = prog.getElementsByTagName('div')[0]
					progBar.style.width= 2*file.percent+'px';
					progBar.setAttribute('aria-valuenow', file.percent);
				},
				// 文件上传完执行
				FileUploaded: function(up, file, info) {
		
					if (info.status >= 200 || info.status < 200)
					{	
						//调用ajax请求 将上传的图片存入数据表中
						//然后在服务器中跨域调用请求生成缩略图
						$.ajax({
							url:"http://photos.leyaocn.com/app/index.php?i=2&c=entry&do=upload_user_photo&m=photobook",
							data:{
								org_name:file.name
							},
							type:"post",
							dataType:"json",
							success:function(res){ 
								if(res.code ==0){
									// window.location.href ="{php echo $this->createMobileUrl('')}"
								}else{
									alert(res.status)
								}
							}
						})		
						document.getElementById(file.id).getElementsByTagName('b')[0].innerHTML = 'success';
					}
					else
					{
						document.getElementById(file.id).getElementsByTagName('b')[0].innerHTML = info.response;
					} 
				},
				Error: function(up, err) {
					document.getElementById('console').appendChild(document.createTextNode("\nError xml:" + err.response));
				},
				UploadComplete: function(up,file){
					if(tid != '' || tid != null){
						setTimeout(function(){  //使用  setTimeout（）方法设定定时2000毫秒
							window.location.replace("http://photos.leyaocn.com/app/index.php?i=2&c=entry&tid="+tid+"&do=userphotos&m=photobook");//页面刷新
							},700);
					}
				}
			}
		});
		
		uploader.init();
		//模拟文件删除
		$('#delete').click(function(){
			$.ajax({
				url:"http://demo-photo.oss-cn-beijing.aliyuncs.com/img_00.JPG",
				type:"delete",
				dataType:"json",
				success:function(res){
					console.log(res)
				}
			})
		})