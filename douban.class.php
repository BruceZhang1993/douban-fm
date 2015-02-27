<?php
	
	/**
	 * 豆瓣FM功能类，需要安装php_curl扩展
	 * @version 1.0
	 * @author Bruce Zhang <zy183525594@163.com>
	 * @copyright All Copyrights Reserved.
	 * @static $ch
	 */
	class Douban {
		static $ch;

		/**
		 * 类的构造函数，用于创建对象
		 * @access public
		 */
		public function __construct(){
			//curl对象的初始化
			self::$ch = curl_init();
			//屏蔽SSL验证
			curl_setopt(self::$ch, CURLOPT_SSL_VERIFYPEER, false); 
			//设置curl_exec函数返回数据而非自动输出
			curl_setopt(self::$ch, CURLOPT_RETURNTRANSFER, true);
			//模拟浏览器访问，可随意修改为喜欢的浏览器UserAgent
			curl_setopt(self::$ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_10_2) AppleWebKit/600.3.18 (KHTML, like Gecko) Version/8.0.3 Safari/600.3.18");
		} 

		/**
		 * 析构函数，处理一些关闭动作
		 * @access public
		 */
		public function __destruct() {
			//销毁对象时关闭curl连接
			curl_close(self::$ch);
		}

		/**
		 * 获得固定的可用频道信息（返回值中的频道号channel_id为重要信息）
		 * @return array 返回存储频道信息的数组
		 * @access public
		 */
		public function get_channels() {
			//设置URL地址和请求方式--GET
			curl_setopt(self::$ch, CURLOPT_URL, "http://www.douban.com/j/app/radio/channels");
			curl_setopt(self::$ch, CURLOPT_POST, false);
			//执行请求
			$response = curl_exec(self::$ch);
			//将返回的json数据转换为数组返回
			$channels = json_decode($response, true);
			return $channels['channels'];
		}

		/**
		 * 豆瓣FM登录函数（POST）
		 * @param  string $email    待登录账户的邮箱
		 * @param  string $password 账户密码（明文，不加密）
		 * @return array           	返回保存有用户信息的数组，或者出错信息
		 * @access public
		 */
		public function login($email, $password) {
			//设置POST参数列表，包括账户邮箱和密码
			$email = trim($email);
			$data = "app_name=radio_desktop_win&version=100&email={$email}&password={$password}";
			//设置URL，设置POST请求，加入POST参数
			curl_setopt(self::$ch, CURLOPT_URL, "http://www.douban.com/j/app/login");
			curl_setopt(self::$ch, CURLOPT_POST, true);
			curl_setopt(self::$ch, CURLOPT_POSTFIELDS, $data);
			//执行请求
			$response = curl_exec(self::$ch);
			//json解密并整理数据返回
			$info = json_decode($response, true);
			if($info['r']) {
				$content['success'] = false;
				$content['msg'] = $info['err'];
			}else {
				$content['success'] = true;
				$content['id'] = $info['user_id']; 		//
				$content['token'] = $info['token']; 	//
				$content['expire'] = $info['expire']; 	//
				$content['name'] = $info['user_name'];
				$content['email'] = $info['email'];
			}
			//返回的数组中success下标的值表示状态
			return $content;
		}

		/**
		 * 给指定歌曲加红心函数
		 * @param  integer $channel 当前频道号，从get_channels函数取得
		 * @param  string  $sid     要操作的歌曲编号sid，从get_list函数获取
		 * @param  array   $info    保存的登录信息
		 * @return array          	返回歌曲列表或出错信息，当数组下标为r的值为0时表示操作成功，下同
		 * @access public
		 */
		public function like($channel, $sid, $info) {
			//登录信息参数，URL地址
			$logged = '&user_id='.$info['id'].'&expire='.$info['expire'].'&token='.$info['token'];
			$url = "http://www.douban.com/j/app/radio/people?app_name=radio_desktop_win&version=100{$logged}&channel={$channel}&sid={$sid}&type=r";
			//设置URL和GET请求
			curl_setopt(self::$ch, CURLOPT_URL, $url);
			curl_setopt(self::$ch, CURLOPT_POST, false);
			//执行请求
			$response = curl_exec(self::$ch);
			//json解密并整理数据返回
			$list = json_decode($response, true);
			if($list['r']) {
				$content['success'] = false;
				$content['msg'] = $list['err'];
			}else {
				$content['success'] = true;
				$content['songs'] = $list['song'];
			}
			//song下标存储歌曲列表
			return $content;
		}

		/**
		 * 给指定歌曲取消红心函数
		 * @param  integer $channel 当前频道号，从get_channels函数取得
		 * @param  string  $sid     要操作的歌曲编号sid，从get_list函数获取
		 * @param  array   $info    保存的登录信息
		 * @return array          	返回歌曲列表或出错信息
		 * @access public
		 */
		public function dislike($channel, $sid, $info=false) {
			//注释信息与like函数一致
			$logged = "";
			if($info) {
				$logged = '&user_id='.$info['id'].'&expire='.$info['expire'].'&token='.$info['token'];
			}
			$url = "http://www.douban.com/j/app/radio/people?app_name=radio_desktop_win&version=100{$logged}&channel={$channel}&sid={$sid}&type=u";
			curl_setopt(self::$ch, CURLOPT_URL, $url);
			curl_setopt(self::$ch, CURLOPT_POST, false);
			$response = curl_exec(self::$ch);
			$list = json_decode($response, true);
			if($list['r']) {
				$content['success'] = false;
				$content['msg'] = $list['err'];
			}else {
				$content['success'] = true;
				$content['songs'] = $list['song'];
			}
			return $content;
		}

		//    $options=array('q'=>'', 'tag'=>''...);
		//    q  tag   start  count
		/**
		 * 搜索专辑/歌曲
		 * @param  array $options  参数数组
		 * @return array           返回搜索结果数组
		 */
		public function search($options) {
			$url = "https://api.douban.com/v2/music/search?";
			foreach ($options as $key => $value) {
				$url .= "{$key}={$value}&";
			}
			$url = rtrim($url, '&');
			curl_setopt(self::$ch, CURLOPT_URL, $url);
			curl_setopt(self::$ch, CURLOPT_POST, false);
			$response = curl_exec(self::$ch);
			$array = json_decode($response, true);
			return $array;
		}

		/**
		 * 获得歌曲最多的20个标签
		 * @param  string $song subject id
		 * @return array        返回最多的标签
		 */
		public function get_tags($song) {
			$url = "https://api.douban.com/v2/music/{$song}/tags";
			curl_setopt(self::$ch, CURLOPT_URL, $url);
			curl_setopt(self::$ch, CURLOPT_POST, false);
			$response = curl_exec(self::$ch);
			$array = json_decode($response, true);
			return $array;
		}

		/**
		 * 获取专辑信息函数 -- V2接口
		 * @param  mixed $aid 专辑号aid，get_list函数取得
		 * @return mixed      返回专辑信息，失败返回false
		 * @access public
		 */
		public function get_subject_info($song) {
			curl_setopt(self::$ch, CURLOPT_URL, "https://api.douban.com/v2/music/{$song}");
			curl_setopt(self::$ch, CURLOPT_POST, false);
			$response = curl_exec(self::$ch);
			$array = json_decode($response, true);
			return $array;
		}

		/**
		 * 获取专辑信息函数 -- 旧接口
		 * @param  mixed $aid 专辑号aid，get_list函数取得
		 * @return mixed      返回专辑信息，失败返回false
		 * @access public
		 */
		public function get_subject_info_old($aid) {
			//设置API获取地址，GET请求
			curl_setopt(self::$ch, CURLOPT_URL, "http://api.douban.com/music/subject/{$aid}");
			curl_setopt(self::$ch, CURLOPT_POST, false);
			//执行请求，返回XML数据
			$response = curl_exec(self::$ch);
			//解析XML数据，并转换为关联数组
			$xml = simplexml_load_string($response);
			$json = json_encode($xml);
			$array = json_decode($json, true);
			//返回包含专辑信息的数组
			return $array;
		}

		/**
		 * 跳过当前歌曲
		 * @param  integer  $channel 频道号
		 * @param  string  	$sid     歌曲sid
		 * @param  array 	$info    登录信息
		 * @return array           
		 */
		public function next($channel, $sid, $info=false) {
			$logged = "";
			if($info) {
				$logged = '&user_id='.$info['id'].'&expire='.$info['expire'].'&token='.$info['token'];
			}
			$url = "http://www.douban.com/j/app/radio/people?app_name=radio_desktop_win&version=100{$logged}&channel={$channel}&sid={$sid}&type=s";
			curl_setopt(self::$ch, CURLOPT_URL, $url);
			curl_setopt(self::$ch, CURLOPT_POST, false);
			$response = curl_exec(self::$ch);
			$list = json_decode($response, true);
			if($list['r']) {
				$content['success'] = false;
				$content['msg'] = $list['err'];
			}else {
				$content['success'] = true;
				$content['songs'] = $list['song'];
			}
			return $content;
		}

		/**
		 * 歌曲不再播放
		 * @param  integer  $channel 频道号
		 * @param  string  	$sid     歌曲sid
		 * @param  array 	$info    登录信息
		 * @return array           
		 */
		public function never_play($channel, $sid, $info=false) {
			$logged = "";
			if($info) {
				$logged = '&user_id='.$info['id'].'&expire='.$info['expire'].'&token='.$info['token'];
			}
			$url = "http://www.douban.com/j/app/radio/people?app_name=radio_desktop_win&version=100{$logged}&channel={$channel}&sid={$sid}&type=b";
			curl_setopt(self::$ch, CURLOPT_URL, $url);
			curl_setopt(self::$ch, CURLOPT_POST, false);
			$response = curl_exec(self::$ch);
			$list = json_decode($response, true);
			if($list['r']) {
				$content['success'] = false;
				$content['msg'] = $list['err'];
			}else {
				$content['success'] = true;
				$content['songs'] = $list['song'];
			}
			return $content;
		}

		/**
		 * 歌曲播放完毕
		 * @param  integer  $channel 频道号
		 * @param  string  	$sid     歌曲sid
		 * @param  array 	$info    登录信息
		 * @return array           
		 */
		public function end($channel, $sid, $info=false) {
			$logged = "";
			if($info) {
				$logged = '&user_id='.$info['id'].'&expire='.$info['expire'].'&token='.$info['token'];
			}
			$url = "http://www.douban.com/j/app/radio/people?app_name=radio_desktop_win&version=100{$logged}&channel={$channel}&sid={$sid}&type=e";
			curl_setopt(self::$ch, CURLOPT_URL, $url);
			curl_setopt(self::$ch, CURLOPT_POST, false);
			$response = curl_exec(self::$ch);
			$list = json_decode($response, true);
			if($list['r']) {
				$content['success'] = false;
				$content['msg'] = $list['err'];
			}else {
				$content['success'] = true;
				$content['songs'] = $list['song'];
			}
			return $content;
		}

		/**
		 * 获得某频道歌曲列表
		 * @param  integer $channel 频道号
		 * @param  boolean $playing 是否正在播放歌曲，默认为false
		 * @param  array   $info    登录信息，可选参数，默认为false，不能获得一些特殊频道的歌曲如：私人频道，红心频道
		 * @return array           	返回歌曲列表或出错信息
		 * @access public
		 */
		public function get_list($channel,$playing=false, $info=false) {
			$logged = "";
			$tag = 'n';
			if($playing) {
				$tag = 'p';
			}
			if($info) {
				$logged = '&user_id='.$info['id'].'&expire='.$info['expire'].'&token='.$info['token'];
			}
			$url = "http://www.douban.com/j/app/radio/people?app_name=radio_desktop_win&version=100{$logged}&channel={$channel}&type={$tag}";
			curl_setopt(self::$ch, CURLOPT_URL, $url);
			curl_setopt(self::$ch, CURLOPT_POST, false);
			$response = curl_exec(self::$ch);
			$list = json_decode($response, true);
			if($list['r']) {
				$content['success'] = false;
				$content['msg'] = $list['err'];
			}else {
				$content['success'] = true;
				$content['songs'] = $list['song'];
			}
			return $content;
		}

	}

 ?>
