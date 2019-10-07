<?php

namespace common\modules\kvs\engine;

use common\components\model\ActiveRecord;

/**
 * This is the model class for table "kvs".
 *
 * @property string $model
 * @property string $key
 * @property string $lang
 * @property string $value
 */
class RowRecord extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'kvs';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['model', 'key', 'lang'], 'required'],
            [['value'], 'string'],
            [['model', 'key'], 'string', 'max' => 191],
            [['lang'], 'string', 'max' => 7],
            [['model', 'key', 'lang'], 'unique', 'targetAttribute' => ['model', 'key', 'lang']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'model' => 'Model',
            'key' => 'Key',
            'lang' => 'Lang',
            'value' => 'Value',
        ];
    }
}
