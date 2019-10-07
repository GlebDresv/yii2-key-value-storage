<?php
/**
 * Created by PhpStorm.
 * User: shaa
 * Date: 05.02.19
 * Time: 14:46
 */

namespace common\modules\kvs\engine;

use backend\components\FormBuilder;
use common\helpers\LanguageHelper;
use yii\helpers\ArrayHelper;

/**
 * Class KvsModelAbstract
 * @package common\modules\kvs\engine
 *
 * @property string $id
 */
abstract class KvsModelAbstract extends ActiveRecordEventsModel
{
    const NO_LANG_ATTRIBUTE = 'no-lang';

    public $relModelIndex = null;

    private $lang = '';

    /**
     * KvsModelAbstract constructor.
     * @param array $config
     * @throws \yii\base\InvalidConfigException
     */
    public function __construct(array $config = [])
    {
        parent::__construct($config);
        $default = LanguageHelper::getDefaultLanguage()->locale;
        $admLang = LanguageHelper::getEditLanguage();
        $apiLang = app()->language;
        $this->lang = $default;
        if ($default != $admLang){
            $this->lang = $admLang;
        }
        if ($default != $apiLang){
            $this->lang = $apiLang;
        }

        $this->loadFromDb();
    }

    /**
     * @return string
     * @throws \yii\base\InvalidConfigException
     */
    public function getTitle()
    {
        return \Yii::t(
            'back/' . $this->formName(),
            ucfirst(strtolower(trim(implode(' ', preg_split('~(?=[A-Z])~', $this->formName())))))
        );
    }

    /**
     * @return array
     */
    public function rules()
    {
        $rules = [];
        foreach ($this->getAttributesToRender() as $attribute) {
            $rules[] = [$attribute, 'safe'];
        }
        return $rules;
    }

    public function getAttributesToRender()
    {
        $attributes = [];
        $attributesParams = $this->getAttributesFormConfig();
        foreach ($this->attributes() as $attribute) {
            if ($attributesParams[$attribute]['render'] ?? true) {
                $attributes[] = $attribute;
            }
        }
        return $attributes;
    }

    /**
     * @return array
     * @throws \yii\base\InvalidConfigException
     */
    public function attributeLabels()
    {
        $labels = [];

        $formConfig = $this->getFormConfig();
        $formAttributes = [];
        foreach ($formConfig as $tabConfig) {
            $formAttributes = array_merge($formAttributes, array_keys($tabConfig));
        }
        $formAttributes = array_unique($formAttributes);

        foreach ($formAttributes as $attribute) {
            $label = ucfirst(implode(' ', preg_split('/(?=[A-Z1-9])/', $attribute)));
            $labels[$attribute] = \Yii::t('back/' . $this->formName(), $label);
        }
        return $labels;
    }

    public function getAttributesFormConfig(): array
    {
        return [
            'relModelIndex' => [
                'render' => false,
                'save' => false,
                'autoload' => false,
                'translatable' => false,
            ]
        ];
    }

    /**
     * @return array
     * @throws \yii\base\InvalidConfigException
     */
    public function getFormConfig(): array
    {
        $attributesByTabs = $this->getAttributesByTabs();
        $attributesParams = $this->getAttributesFormConfig();

        $tabs = [];
        foreach ($attributesByTabs as $tabName => $attributes) {
            $fieldsConfig = [];
            foreach ($attributes as $attribute) {
                $fieldsConfig[$attribute] = [
                    'type' => $attributesParams[$attribute]['type'] ?? FormBuilder::INPUT_TEXT,
                ];
            }
            $tabs[$tabName] = $fieldsConfig;
        }

        return $tabs;
    }

    /**
     * @return string
     * @throws \yii\base\InvalidConfigException
     */
    protected function getDefaultTab()
    {
        return \Yii::t('back/' . $this->formName(), 'Main');
    }

    /**
     * @return array
     * @throws \yii\base\InvalidConfigException
     */
    private function getAttributesByTabs(): array
    {
        $attributes = $this->attributes();
        $attributesParams = $this->getAttributesFormConfig();
        $result = [];

        foreach ($attributes as $attribute) {
            if (!($attributesParams[$attribute]['render'] ?? true)) {
                continue;
            }
            $tab = $this->getDefaultTab();
            if ($attributesParams[$attribute]['tab'] ?? false) {
                $tab = $attributesParams[$attribute]['tab'];
            }
            $result[$tab][] = $attribute;
        }
        return $result;
    }

    /**
     * @throws \yii\base\InvalidConfigException
     */
    public function loadFromDb()
    {

        $attributes = $this->attributes();
        $noLangAttributes = array_keys(
            array_filter(
                $this->getAttributesFormConfig(),
                function ($params) {
                    $translatable = $params['translatable'] ?? true;
                    return !$translatable;
                }
            )
        );
        $this->loadAttributes($noLangAttributes, self::NO_LANG_ATTRIBUTE);
        $this->loadAttributes(array_diff($attributes, $noLangAttributes), $this->lang);

        $this->afterFind();
    }

    /**
     * @param $attributes
     * @param $lang
     * @throws \yii\base\InvalidConfigException
     */
    public function loadAttributes($attributes, $lang)
    {
        $noAutoloadAttributes = array_keys(
            array_filter(
                $this->getAttributesFormConfig(),
                function ($config) {
                    return !($config['autoload'] ?? true);
                }
            )
        );

        $attributesToLoad = array_diff($attributes, $noAutoloadAttributes);
        $rows = $this->findAttributeRecords($lang, $attributesToLoad);

        foreach ($rows as $row) {
            $this->{$row->key} = $row->value;
        }
    }

    /**
     * @return bool
     * @throws \yii\base\InvalidConfigException
     */
    public function save()
    {
        if (
            !$this->validate()
            || !$this->beforeSave()
        ) {
            return false;
        }

        $attributes = $this->attributes();
        $noLangAttributes = array_keys(
            array_filter(
                $this->getAttributesFormConfig(),
                function ($params) {
                    $translatable = $params['translatable'] ?? true;
                    return !$translatable;
                }
            )
        );
        $changedAttributesNLG = [];
        $savedNLG = $this->saveAttributes($noLangAttributes, self::NO_LANG_ATTRIBUTE, $changedAttributesNLG);
        $changedAttributes = [];
        $saved = $this->saveAttributes(array_diff($attributes, $noLangAttributes), $this->lang, $changedAttributes);

        $this->afterSave(array_merge($changedAttributes, $changedAttributesNLG));
        return $saved && $savedNLG;
    }


    /**
     * @param $attributes
     * @param $lang
     * @return bool
     * @throws \yii\base\InvalidConfigException
     */
    public function saveAttributes($attributes, $lang, &$changedAttributes)
    {
        /** @var RowRecord[] $rows */
        $rows = $this->findAttributeRecords($lang, $attributes);

        $notFoundKeys = array_diff($attributes, ArrayHelper::getColumn($rows, 'key'));
        foreach ($notFoundKeys as $key) {
            $rows[] = new RowRecord([
                'model' => $this->formName(),
                'lang' => $lang,
                'key' => $key
            ]);
        }

        $changedAttributes = [];
        $attributesParams = $this->getAttributesFormConfig();
        foreach ($rows as $row) {
            if (!($attributesParams[$row->key]['save'] ?? true)) {
                continue;
            }
            $row->value = $this->{$row->key};
            $dirtyAttributes = $row->dirtyAttributes;
            if ($row->save() && $dirtyAttributes) {
                $changedAttributes[] = $row->key;
            }
        }

        return true;
    }


    /**
     * @param $lang
     * @param $attributes
     * @return RowRecord[]
     * @throws \yii\base\InvalidConfigException
     */
    public function findAttributeRecords($lang, $attributes)
    {
        return RowRecord::find()->andWhere([
            'and',
            [
                'model' => $this->formName(),
                'lang' => $lang,
            ],
            [
                'in', 'key', $attributes
            ]
        ])->all();
    }

    /**
     * @param string $attribute
     * @return bool
     */
    public function hasAttribute(string $attribute)
    {
        return in_array($attribute, $this->attributes());
    }

    /**
     * @return string
     * @throws \yii\base\InvalidConfigException
     */
    public function getId()
    {
        return $this->formName();
    }

    /**
     * @return bool
     */
    public function isTranslatable()
    {
        return true;
    }

    /**
     * @return bool
     */
    public function getIsNewRecord()
    {
        return false;
    }
}