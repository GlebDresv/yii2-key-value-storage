<?php
/**
 * Created by PhpStorm.
 * User: shaa
 * Date: 05.02.19
 * Time: 16:28
 */

namespace common\modules\kvs\engine;


use common\modules\dynamicForm\components\Model;
use yii\base\ModelEvent;
use yii\db\AfterSaveEvent;

abstract class ActiveRecordEventsModel extends Model
{
    /**
     * @event Event an event that is triggered after the record is created and populated with query result.
     */
    const EVENT_AFTER_FIND = 'afterFind';

    /**
     * @event ModelEvent an event that is triggered before updating a record.
     * You may set [[ModelEvent::isValid]] to be `false` to stop the update.
     */
    const EVENT_BEFORE_UPDATE = 'beforeUpdate';
    /**
     * @event AfterSaveEvent an event that is triggered after a record is updated.
     */
    const EVENT_AFTER_UPDATE = 'afterUpdate';

    public function beforeSave()
    {
        $event = new ModelEvent();
        $this->trigger(self::EVENT_BEFORE_UPDATE, $event);

        return $event->isValid;
    }

    public function afterSave($changedAttributes)
    {
        $this->trigger(self::EVENT_AFTER_UPDATE, new AfterSaveEvent([
            'changedAttributes' => $changedAttributes,
        ]));
    }

    public function afterFind()
    {
        $this->trigger(self::EVENT_AFTER_FIND);
    }
}