<?php
/**
 * Created by PhpStorm.
 * User: shaa
 * Date: 05.02.19
 * Time: 14:41
 */

namespace common\modules\kvs\controllers;

use backend\widgets\previewWidget\configs\Config;
use common\modules\kvs\engine\KvsModelAbstract;
use common\modules\kvs\engine\UserFieldsConfig;
use common\modules\kvs\KvsModule;
use yii\web\NotFoundHttpException;

class KvsController extends \yii\web\Controller
{
    /** @var KvsModule */
    public $module;

    private $pathToView = '@backend/views/templates/update';

    /**
     * @param $alias
     * @return string
     * @throws NotFoundHttpException
     * @throws \yii\base\InvalidConfigException
     */
    public function actionUpdate($alias)
    {
        $model = $this->getModelByAlias($alias);
        if (app()->request->isPost) {
            $model->load(app()->request->post());
            if($model->save()){
                session()->setFlash('success', \Yii::t('back/admin', 'Saved success'));
            }
        }
        return $this->render($this->pathToView, [
            'model' => $model,
            'formConfig' => 'getFormConfig',
            'enableAjaxValidation' => 'true'
        ]);
    }

    /**
     * @param $id
     * @return false|string
     * @throws NotFoundHttpException
     */
    public function actionAjaxValidation($id)
    {
        $model = $this->getModelByAlias($id);
        $model->load(app()->request->post());
        $model->validate();
        return json_encode($model->getErrors());
    }

    public function actionSaveFieldConfig()
    {
        $configsNamespaces = 'backend\widgets\previewWidget\configs';

        $fieldName = app()->request->post('fieldName');
        $fieldNameArray = preg_split('~[\[\]]~', $fieldName);
        $className = $fieldNameArray[0];
        $option = $fieldNameArray[1];
        $value = app()->request->post('value');
        $fullClassName = $configsNamespaces . '\\' . $className;
        /** @var Config $config */
        $config = UserFieldsConfig::aggregateConfig(new $fullClassName());
        $config->$option = $value;
        $config->save();
        return json_encode($config->getErrors());
    }

    /**
     * @param string $alias
     * @return mixed
     * @throws NotFoundHttpException
     */
    private function getModelByAlias(string $alias): KvsModelAbstract
    {
        $modelName = implode(
            '',
            array_map(
                function ($string) {
                    return ucfirst($string);
                },
                explode('-', $alias)
            )
        );
        $namespace = $this->module->modelsNamespace;
        $fullName = $namespace . '\\' . $modelName;
        if (!class_exists($fullName)) {
            throw new NotFoundHttpException('cant find model - ' . $fullName);
        }
        return new $fullName();
    }
}