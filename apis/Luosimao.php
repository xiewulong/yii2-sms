<?php
/*!
 * yii2 extension - 短信发送接口 - Luosimao sdk
 * xiewulong <xiewulong@vip.qq.com>
 * https://github.com/xiewulong/yii2-sms
 * https://raw.githubusercontent.com/xiewulong/yii2-sms/master/LICENSE
 * create: 2015/9/23
 * update: 2015/9/23
 * version: 0.0.1
 */

namespace yii\sms\apis;

use Yii;

class Luosimao{

	//接口地址
	private $api = 'https://sms-api.luosimao.com/v1/send.json';

	//用户名
	private $username = 'api';

	//密码
	private $password;

	//状态
	private $messages = [
		'0' => '发送成功',
		'-10' => '验证信息失败',
		'-20' => '短信余额不足',
		'-30' => '短信内容为空',
		'-31' => '短信内容存在敏感词',
		'-32' => '短信内容缺少签名信息',
		'-40' => '错误的手机号',
		'-41' => '号码在黑名单中',
		'-42' => '验证码类短信发送频率过快',
		'-50' => '请求发送IP不在白名单内',
	];

	/**
	 * 构造器
	 * @method __construct
	 * @since 0.0.1
	 * @param {array} $key 验证密码
	 * @return {none}
	 */
	public function __construct($key){
		$this->password = 'key-' . $key;
	}

	/**
	 * 获取类对象
	 * @method sdk
	 * @since 0.0.1
	 * @param {array} $key 参数数组
	 * @return {none}
	 * @example static::sdk($key);
	 */
	public static function sdk($key){
		return new static($key);
	}

	/**
	 * 获取类对象
	 * @method sdk
	 * @since 0.0.1
	 * @param {string} $mobile 目标手机号码
	 * @param {string} $message 短信内容
	 * @return {array}
	 * @example $this->send($mobile, $message);
	 */
	public function send($mobile, $message){
		$result = json_decode($this->curl($this->api, ['mobile' => $mobile, 'message' => $message]));

		return [
			'status' => $result->error == 0,
			'message' => $this->messages[$result->error],
		];
	}

	/**
	 * curl远程请求
	 * @method curl
	 * @since 0.0.1
	 * @param {string} $url 请求地址
	 * @param {array|string} [$data=null] post数据
	 * @return {string}
	 */
	private function curl($url, $data = null){
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
		curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 30);
		curl_setopt($curl, CURLOPT_HEADER, 0);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
		curl_setopt($curl, CURLOPT_USERPWD, $this->username . ':' . $this->password);
		if(!empty($data)){
			curl_setopt($curl, CURLOPT_POST, 1);
			curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
		}
		$data = curl_exec($curl);
		curl_close($curl);

		return $data;
	}

}
