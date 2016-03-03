<?php
/*!
 * yii2 extension - 短信发送接口 - Luosimao sdk
 * xiewulong <xiewulong@vip.qq.com>
 * https://github.com/xiewulong/yii2-sms
 * https://raw.githubusercontent.com/xiewulong/yii2-sms/master/LICENSE
 * create: 2015/9/23
 * update: 2016/3/3
 * version: 0.0.1
 */

namespace yii\sms\apis;

use Yii;

class Luosimao {

	//网关地址
	private $api = 'https://sms-api.luosimao.com';

	//接口版本
	private $version = 'v1';

	//数据返回格式
	private $format = 'json';

	//用户名
	private $username = 'api';

	//密码
	private $password;

	//状态
	private $messages = [
		'0' => '发送成功',
		'-10' => '验证信息失败',	//检查api key是否和各种中心内的一致，调用传入是否正确
		'-20' => '短信余额不足',	//进入个人中心购买充值
		'-30' => '短信内容为空',	//检查调用传入参数：message
		'-31' => '短信内容存在敏感词',	//接口会同时返回  hit 属性提供敏感词说明，请修改短信内容，更换词语
		'-32' => '短信内容缺少签名信息',	//短信内容末尾增加签名信息eg.【公司名称】
		'-33' => '短信过长，超过300字（含签名）',	//调整短信内容或拆分为多条进行发送
		'-40' => '错误的手机号',	//检查手机号是否正确
		'-41' => '号码在黑名单中',	//号码因频繁发送或其他原因暂停发送，请联系客服确认
		'-42' => '验证码类短信发送频率过快',	//前台增加60秒获取限制
		'-43' => '号码数量太多',	//单次提交控制在10万个号码以内
		'-50' => '请求发送IP不在白名单内',	//查看触发短信IP白名单的设置
	];

	//错误码
	public $errcode = 0;

	//错误码描述
	public $errmsg;

	//敏感词, 错误码为-31时会出现
	public $hit;

	/**
	 * 构造器
	 * @method __construct
	 * @since 0.0.1
	 * @param {string} $key 验证密码
	 * @return {none}
	 */
	public function __construct($key) {
		$this->password = 'key-' . $key;
		$this->errmsg = $this->messages[$this->errcode];
	}

	/**
	 * 获取类对象
	 * @method sdk
	 * @since 0.0.1
	 * @param {array} $key 参数数组
	 * @return {none}
	 * @example static::sdk($key);
	 */
	public static function sdk($key) {
		return new static($key);
	}

	/**
	 * 发送短信
	 * @method send
	 * @since 0.0.1
	 * @param {string} $mobile 目标手机号码
	 * @param {string} $message 短信内容
	 * @param {int} [$time=0] 短信发送定时时间戳, 0立即, >0定时(将调用批量发送短信接口)
	 * @return {boolean}
	 * @example $this->send($mobile, $message, $time);
	 */
	public function send($mobile, $message, $time = 0) {
		if(preg_match('/,/', $mobile) || ($time && $time > time())) {
			return $this->batchSend($mobile, $message, $time);
		}

		$data = $this->getData('/send', [
			'mobile' => $mobile,
			'message' => $message,
		]);

		return $this->errcode == 0;
	}

	/**
	 * 批量发送短信
	 * @method batchSend
	 * @since 0.0.1
	 * @param {string} $mobile_list 目标手机号码列表, 多个号码使用","分隔
	 * @param {string} $message 短信内容
	 * @param {int} [$time=0] 短信发送定时时间戳, 0立即, >0定时
	 * @return {boolean}
	 * @example $this->batchSend($mobile_list, $message, $time);
	 */
	public function batchSend($mobile_list, $message, $time = 0) {
		$postData = [
			'mobile_list' => $mobile_list,
			'message' => $message,
		];
		if($time && $time > time()) {
			$postData['time'] = date('Y-m-d H:i:s', $time);
		}

		$this->getData('/send_batch', $postData);

		return $this->errcode == 0;
	}

	/**
	 * 查询账户短信余量
	 * @method getStatus
	 * @since 0.0.1
	 * @return {int}
	 * @example $this->getStatus();
	 */
	public function getStatus() {
		$data = $this->getData('/status');

		return $this->errcode == 0 ? $data['deposit'] : -1;
	}

	/**
	 * 获取数据
	 * @method getData
	 * @since 0.0.1
	 * @param {string} $action 接口名称
	 * @param {string|array} [$data] 数据
	 * @return {array}
	 */
	private function getData($action, $data = null) {
		$_result = $this->curl($this->getApiUrl($action), $data);

		if(!$_result) {
			$this->errcode = '503';
			$this->errmsg = '接口服务不可用';
		}

		$result = json_decode($_result, true);
		if(json_last_error()) {
			$this->errcode = '503';
			$this->errmsg = '数据不合法';
		} else if(isset($result['error']) && isset($result['msg'])) {
			$this->errcode = $result['error'];
			$this->errmsg = isset($this->messages[$this->errcode]) ? $this->messages[$this->errcode] : "Error: $this->errcode";
			if($this->errcode == '-31') {
				$this->hit = $result['hit'];
			}
		}

		return $result;
	}

	/**
	 * 获取接口完整访问地址
	 * @method getApiUrl
	 * @since 0.0.1
	 * @param {string} $action 接口名称
	 * @param {array} [$query=[]] 参数
	 * @return {string}
	 */
	private function getApiUrl($action, $query = []) {
		return $this->api . '/' . $this->version . $action . '.' . $this->format . (empty($query) ? '' : '?' . http_build_query($query));
	}

	/**
	 * curl远程请求
	 * @method curl
	 * @since 0.0.1
	 * @param {string} $url 请求地址
	 * @param {string|array} [$data] post数据
	 * @return {string}
	 */
	private function curl($url, $data = null) {
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
		curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 30);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_HEADER, false);
		curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
		curl_setopt($curl, CURLOPT_USERPWD, $this->username . ':' . $this->password);
		if(!empty($data)) {
			curl_setopt($curl, CURLOPT_POST, true);
			curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
		}
		$data = curl_exec($curl);
		curl_close($curl);

		return $data;
	}

}
