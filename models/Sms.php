<?php

namespace yii\sms\models;

use Yii;
use yii\db\ActiveRecord;

class Sms extends ActiveRecord{

	public static function tableName(){
		return '{{%sms}}';
	}

}
