//index页用到的js
document.getElementById("cebian_but").addEventListener("tap", function() {
	mui('.mui-off-canvas-wrap').offCanvas().toggle();
});
var gallery = mui('.mui-slider');
gallery.slider({
	interval: 0
	//interval: 1000 //自动轮播周期，若为0则不自动播放，默认为0；
});
mui('#scroll').scroll({
	scrollY: true, //是否竖向滚动
	scrollX: false, //是否横向滚动
	startX: 0, //初始化时滚动至x
	startY: 0, //初始化时滚动至y
	indicators: true, //是否显示滚动条
	deceleration: 0.0005, //阻尼系数,系数越小滑动越灵敏
	bounce: true //是否启用回弹

});
//mui('#scroll').scroll().scrollToBottom(100);


//alert(window.screen.height)
