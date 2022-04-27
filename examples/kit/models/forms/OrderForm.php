<?php

namespace app\models\forms;

use Yii;
use app\models\Order;
use app\models\OrderItem;
use andy87\yii_components\collection\Kit;

/**
 *  Форма `OrderForm`.
 *  содержет кастомный параметр `order_items`
 */
class OrderForm extends Order
{
    /** @var ?Kit $orderItemsKit  */
    public ?Kit $orderItemsKit = null;

    public function init()
    {
        parent::init();

        // Инициализируем коллекцию
        $this->orderItemsKit = new Kit( OrderItem::class );
    }

    /**
     * @param $data
     * @param $formName
     * @return bool
     */
    public function load($data, $formName = null): bool
    {
        // Загружаем модели в коллекцию
        $this->orderItemsKit->loadModels();

        return parent::load($data, $formName);
    }

    /**
     * @param $condition
     * @return null
     */
    public static function findOne($condition)
    {
        if ( $model = parent::findOne($condition))
        {
            $model->orderItemsKit->findModels(['order_id' => $model->id]);

            return $model;
        }

        return null;
    }

    /**
     * @param bool $runValidation
     * @param mixed $attributeNames
     * @return bool
     */
    public function save($runValidation = true, $attributeNames = null): bool
    {
        $transaction = Yii::$app->db->beginTransaction();

        try {

            if ( parent::save($runValidation, $attributeNames) )
            {
                if ( $this->orderItemsKit->save() )
                {
                    $transaction->commit();

                    return true;
                }
            }

        } catch ( \Exception $e ) {

            Yii::$app->session->setFlash('error', $e->getMessage() );
        }

        $transaction->rollBack();

        return false;
    }
}