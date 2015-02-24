Douban FM Class For PHP
-----------------------
######Require php-curl
		Author: BruceZhang1993 <zy183525594@163.com>
		Version: 1.0
		License: GPL V2

----------------------
		Function Reference:
		get_channels() 							获得固定的可用频道信息
		login($email, $password) 					豆瓣FM登录函数
		like($channel, $sid, $info) 					给指定歌曲加红心函数
		dislike($channel, $sid, $info) 					给指定歌曲取消红心函数
		get_album_info($aid) 						获取专辑信息函数
		get_list($channel,$playing=false, $info=false) 			获得某频道歌曲列表
