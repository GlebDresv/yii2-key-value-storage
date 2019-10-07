<?php
/**
 * Created by PhpStorm.
 * User: shaa
 * Date: 05.02.19
 * Time: 14:37
 */
namespace common\modules\kvs;

use yii\base\Module;

class KvsModule extends Module
{
    public $var;

    /**
     * @var string
     */
    public $controllerNamespace = 'common\modules\kvs\controllers';
    public $modelsNamespace = 'common\modules\kvs\models';

}