Douban FM Class For PHP
-----------------------
######Require php-curl
		Author: BruceZhang1993 <zy183525594@163.com>
		Version: 1.0
		License: GPL V2

=====================================
Function Description:
		get_channels
			获得固定的可用频道信息
		login 
			豆瓣FM登录函数
		like 
			给指定歌曲加红心函数
		dislike 	
			给指定歌曲取消红心函数
		get_album_info
			获取专辑信息函数
		get_list	
			获得某频道歌曲列表

----------------------------
####Documentation

===========================
Function Reference:

		get_channels
			parameters: none
			returns: array or false(if connection failed)
			returns example:
				array (size=55)
				  0 => 
				    array (size=5)
				      'name_en' => string 'Personal Radio' (length=14)
				      'seq_id' => int 0
				      'abbr_en' => string 'My' (length=2)
				      'name' => string '私人兆赫' (length=12)
				      'channel_id' => int 0
				  1 => 
				    array (size=5)
				      'name' => string '华语' (length=6)
				      'seq_id' => int 0
				      'abbr_en' => string '' (length=0)
				      'channel_id' => string '1' (length=1)
				      'name_en' => string '' (length=0)
				  2 => 
				    array (size=5)
				      'name' => string '欧美' (length=6)
				      'seq_id' => int 1
				      'abbr_en' => string '' (length=0)
				      'channel_id' => string '2' (length=1)
				      'name_en' => string '' (length=0)
				      		.
				      		.
				      		.
