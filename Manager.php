<?php
/*!
 * yii2 extension - 短信发送接口
 * xiewulong <xiewulong@vip.qq.com>
 * https://github.com/xiewulong/yii2-sms
 * https://raw.githubusercontent.com/xiewulong/yii2-sms/master/LICENSE
 * create: 2015/9/22
 * update: 2015/10/6
 * version: 0.0.1
 */

namespace yii\sms;

use yii\base\ErrorException;
use yii\sms\models\Sms;
use yii\sms\apis\Luosimao;

class Manager{

	//短信服务商
	public $mode;

	//接口配置
	public $config;

	//署名
	public $sign;

	//前缀, 默认后缀
	public $pre = false;

	//手机号
	private $phone;

	//短信内容
	private $content;

	/**
	 * 发送
	 * @method send
	 * @since 0.0.1
	 * @param {string} $phone 手机号
	 * @param {string} $content 短信内容
	 * @param {number} $uid 操作者, 0系统, >0用户id
	 * @return {boolean}
	 * @example \Yii::$app->sms->send($phone, $content, $uid);
	 */
	public function send($phone, $content, $uid = 0){
		if(empty($phone) || empty($content)){
			throw new ErrorException('Phone and content must be required');
		}

		$this->phone = $phone;
		$this->content = $content;
		$this->setContent();
		$status = false;

		switch(strtolower($this->mode)){
			case 'luosimao':
				$result = Luosimao::sdk($this->config)->send($this->phone, $this->content);
				break;
		}

		if(isset($result) && !empty($result)){
			$sms = new Sms;
			$sms->phone = $this->phone;
			$sms->content = $this->content;
			$sms->uid = $uid;
			$sms->status = $result['status'];
			$sms->message = $result['message'];
			$sms->created_at = time();
			if($sms->save()){
				$status = true;
			}
		}

		return $status;
	}

	/**
	 * 设置短信内容
	 * @method setContent
	 * @since 0.0.1
	 * @return {none}
	 * @example $this->setContent();
	 */
	private function setContent(){
		if($this->sign)$this->content = ($this->pre ? '【' . $this->sign . '】 ' : '') . $this->content . ($this->pre ? '' : ' 【' . $this->sign . '】');
	}

}
