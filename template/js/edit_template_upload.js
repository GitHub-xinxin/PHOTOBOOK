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
		
		var uploader = new plupload.Uploader({
			runtimes : 'html5,flash,silverlight,html4',
			browse_button : 'selectfiles', 
			//runtimes : 'flash',
			container: document.getElementById('container'),
			flash_swf_url : '../mobile/lib/plupload-2.1.2/js/Moxie.swf',
			silverlight_xap_url : '../mobile/lib/plupload-2.1.2/js/Moxie.xap',
			url : host,
			multipart_params: {
			},
		
			init: {
				PostInit: function() {
					document.getElementById('ossfile').innerHTML = '';	
				},
				//添加文件执行
				FilesAdded: function(up, files) {	
					uploader.start();	
						return false;
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

				// 文件上传完执行
				FileUploaded: function(up, file, info) {
		
					if (info.status >= 200 || info.status < 200)
					{	
						$.ajax({
							url:"http://photos.leyaocn.com/web/index.php?c=site&a=entry&do=Editimage&m=photobook",
							type:'post',
							data:{
								org_name:file.name
							},
							dataType:'json',
							success:function(res){
								if(res.code == 0){
									/**
									 * oss取到照片 生成样式图
									 */
									$('#filename').val(res.filename)
									var bg ="http://demo-photo.oss-cn-beijing.aliyuncs.com/"+res.filename;
									$('#bgtd').css('width','362px');//.css('height',img.height/2+'px');
									$('#jun_poster').css('width','362px');//.css('height',img.height/2+'px');
									$('#jun_poster .bg').remove();
									var bgh = $("<img src='" + bg + "' class='bg' style='width:362px;'/>");
									var first = $('#jun_poster .drag:first');
									if(first.length>0){
										bgh.insertBefore(first);  
									} else{
										$('#jun_poster').append(bgh);      
									}
								}else{
									alert(res.status)
								}		
							}
						})	
					}
				},
				Error: function(up, err) {

					document.getElementById('console').appendChild(document.createTextNode("\nError xml:"+res.code + err.response));
				}
			}
		});
		
		uploader.init();