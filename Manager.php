<?php
/*!
 * yii2 extension - 短信发送接口
 * xiewulong <xiewulong@vip.qq.com>
 * https://github.com/xiewulong/yii2-sms
 * https://raw.githubusercontent.com/xiewulong/yii2-sms/master/LICENSE
 * create: 2015/9/22
 * update: 2016/3/3
 * version: 0.0.1
 */

namespace yii\sms;

use yii\base\ErrorException;
use yii\sms\apis\Luosimao;
use yii\sms\models\Sms;

class Manager {

	//服务商
	public $sp;

	//配置
	public $config;

	//署名
	public $sign;

	//前缀, 默认后缀
	public $pre = false;

	//debug
	public $dev = false;

	/**
	 * 发送
	 * @method send
	 * @since 0.0.1
	 * @param {string} $mobile 移动号码, 多个以英文逗号隔开, <=10000个
	 * @param {string} $content 内容, <=300个字(含签名)
	 * @param {number} [$operator_id=0] 操作者, 0系统, >0用户id
	 * @param {number} [$sent_at=0] 发送时间, 0立即, >0定时
	 * @return {boolean}
	 * @example \Yii::$app->sms->send($mobile, $content, $operator_id, $sent_at);
	 */
	public function send($mobile, $content, $operator_id = 0, $sent_at = 0) {
		switch(strtolower($this->sp)) {
			case 'luosimao':
				$sp = Luosimao::sdk($this->config);
				break;
			default:
				throw new ErrorException('The SP service provider does not supported');
				break;
		}

		$sms = new Sms;
		$sms->mobile = $this->formatMobile($mobile);
		$sms->content = $this->formatContent($content);
		$sms->sent_at = $sent_at;
		$sms->operator_id = $operator_id;
		$sms->status = $this->dev || $sp->send($sms->mobile, $sms->content, $sms->sent_at) ? 1 : 0;
		$sms->message = $sp->errmsg;
		$sms->created_at = time();
		$sms->save();

		return $sms->status;
	}

	/**
	 * 格式化内容
	 * @method formatContent
	 * @since 0.0.1
	 * @param {string} $content 内容
	 * @return {string}
	 * @example $this->formatContent($content);
	 */
	private function formatContent($content) {
		if($this->sign) {
			$content = ($this->pre ? '【' . $this->sign . '】 ' : '') . $content . ($this->pre ? '' : ' 【' . $this->sign . '】');
		}

		return $content;
	}

	/**
	 * 格式化移动号码
	 * @method formatMobile
	 * @since 0.0.1
	 * @param {string} $mobile 移动号码
	 * @return {string}
	 * @example $this->formatMobile($mobile);
	 */
	private function formatMobile($mobile) {
		$mobiles = array_unique(explode(',', trim(preg_replace('/[^\d,]/', '', $mobile), ',')));
		$_mobiles = [];
		foreach($mobiles as $_mobile) {
			$_mobiles[] = $_mobile;
		}

		return implode(',', $_mobiles);
	}

}
