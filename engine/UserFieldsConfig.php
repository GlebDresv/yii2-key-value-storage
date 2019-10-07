<?php

namespace common\modules\kvs\engine;

use common\components\model\ActiveRecord;
use common\models\User;
use yii\base\Model;

/**
 * This is the model class for table "user_fields_config".
 *
 * @property int $id
 * @property int $user_id
 * @property string $form_name
 * @property string $option
 * @property string $value
 */
class UserFieldsConfig extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'user_fields_config';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id', 'form_name', 'option'], 'required'],
            [['user_id'], 'integer'],
            [['form_name', 'option', 'value'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => 'User ID',
            'form_name' => 'Form Name',
            'attribute' => 'Attribute',
            'option' => 'Option',
            'value' => 'Value',
        ];
    }

    public static function aggregateConfig(Model $defaultConfig): object
    {
        /** @var User $user */
        $user = app()->user->getIdentity();
        $configs = $user->userFieldsConfig;
        foreach ($configs as $config) {
            if (
                $config->form_name == $defaultConfig->formName()
            ) {
                $defaultConfig->{$config->option} = $config->value;
            }
        }
        return $defaultConfig;
    }
}
