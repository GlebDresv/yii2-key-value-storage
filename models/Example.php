<?php
/**
 * Created by PhpStorm.
 * User: shaa
 * Date: 05.02.19
 * Time: 15:00
 */

namespace common\modules\kvs\models;

use backend\components\FormBuilder;
use common\modules\kvs\engine\KvsModelAbstract;

class Example extends KvsModelAbstract
{
    public $attr1;
    public $attr2;
    public $attr3;
    public $attr4;
    public $attr5;

    /**
     * @return array
     */
    public function rules()
    {
        $rules = [
            ['attr1', 'string'],
        ];
        return array_merge(parent::rules(), $rules);
    }

    /**
     * @return array
     * @throws \yii\base\InvalidConfigException
     */
    public function attributeLabels()
    {
        $labels = [
            'attr1' => \Yii::t('back/' . $this->formName(), 'test attribute label'),
        ];
        return array_merge(parent::attributeLabels(), $labels);
    }

    /**
     * @return array
     * @throws \yii\base\InvalidConfigException
     */
    public function getAttributesFormConfig(): array
    {
        return array_merge(
            parent::getAttributesFormConfig(),
            [
            /* default example */
            'attr1' => [
                'tab' => \Yii::t('vks/' . $this->formName(), 'Main'),
                'type' => FormBuilder::INPUT_TEXT,
                'render' => true,
                'save' => true,
                'autoload' => true,
                'translatable' => true,
            ],
            /* custom config, not defined attributes will be default */
            'attr2' => [
                'tab' => \Yii::t('vks/' . $this->formName(), 'My tab name'),
                'type' => FormBuilder::INPUT_TEXTAREA,
                'translatable' => false,
            ],
            'attr5' => [
                'render' => false,
            ]
        ]);
    }

}