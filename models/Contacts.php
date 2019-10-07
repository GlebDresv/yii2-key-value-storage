<?php
/**
 * Created by PhpStorm.
 * User: shaa
 * Date: 06.02.19
 * Time: 13:40
 */

namespace common\modules\kvs\models;


use backend\components\FormBuilder;
use backend\modules\map\behaviors\MapBehavior;
use backend\modules\map\widgets\mapSelector\MapSelector;
use common\behaviors\SeoBehavior;
use common\models\MapEntity;
use common\modules\kvs\engine\KvsModelAbstract;
use yii\helpers\ArrayHelper;

/**
 * Class Contacts
 * @package common\modules\kvs\models
 * @property MapEntity $mapEntity
 */
class Contacts extends KvsModelAbstract
{
    public $breadcrumbLabel;

    public $title;
    public $phone;
    public $address;
    public $email;

    public $map;
    private $mapEntity;

    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['seo'] = SeoBehavior::class;
        $behaviors['map'] = MapBehavior::class;
        return $behaviors;
    }

    public function rules()
    {
        return [
            ['breadcrumbLabel', 'required'],
            ['breadcrumbLabel', 'string', 'max' => 50],

            ['title', 'required'],
            ['title', 'string', 'max' => 50],

            ['phone', 'required'],
            ['phone', 'string'],

            ['address', 'required'],
            ['address', 'string'],

            ['email', 'string'],
            ['email', 'required'],
            ['email', 'email'],
        ];
    }

    public function getAttributesFormConfig(): array
    {
        return array_merge(
            parent::getAttributesFormConfig(),
            [
                'map' => [
                    'render' => false,
                    'save' => false,
                ]
            ]
        );
    }


    public function getFormConfig(): array
    {
        $config = [
            $this->getDefaultTab() => [
                'map' => [
                    'type' => FormBuilder::INPUT_WIDGET,
                    'widgetClass' => MapSelector::class,
                ],
            ],
        ];

        return ArrayHelper::merge(
            parent::getFormConfig(),
            $config
        );
    }

    /**
     * @throws \yii\base\InvalidConfigException
     */
    public function getMapEntity()
    {
        if(!$this->mapEntity){
            $this->mapEntity = MapEntity::find()
                ->andOnCondition(['entity_model_name' => static::formName()])
                ->andOnCondition(['entity_model_id' => $this->getId()])->one();
        }
        return $this->mapEntity;
    }

    /**
     * @return array
     * @throws \yii\base\InvalidConfigException
     */
    public function getMapData()
    {
        $result = [
            'latitude' => null,
            'longitude' => null,
            'zoom' => null,
        ];

        if ($this->getMapEntity()) {
            $result = [
                'latitude' => $this->mapEntity->latitude,
                'longitude' => $this->mapEntity->longitude,
                'zoom' => $this->mapEntity->zoom,
            ];
        }

        return $result;
    }

    public function getH1Value()
    {
        return $this->title;
    }
}