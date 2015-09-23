<?php

use yii\db\Schema;

class m150923_123405_sms extends \yii\db\Migration{

	public function up(){
		$tableOptions = 'engine=innodb character set utf8';
		if($this->db->driverName === 'mysql') {
			$tableOptions .= ' collate utf8_unicode_ci';
		}

		$this->createTable('{{%sms}}', [
			'id' => Schema::TYPE_PK . ' comment "id"',
			'uid' => Schema::TYPE_INTEGER . ' not null default 0 comment "操作者: 0系统, >0用户id"',
			'phone' => Schema::TYPE_TEXT . ' not null comment "手机号"',
			'content' => Schema::TYPE_STRING . ' not null comment "短信内容"',
			'status' => Schema::TYPE_BOOLEAN . ' not null default 0 comment "发送状态, 0失败, 1成功"',
			'message' => Schema::TYPE_STRING . '(50) comment "状态信息"',
			'created_at' => Schema::TYPE_INTEGER . ' not null comment "发送时间"',
		], $tableOptions . ' comment="短信发送记录"');
	}

	public function down(){
		$this->dropTable('{{%sms}}');
	}

}
