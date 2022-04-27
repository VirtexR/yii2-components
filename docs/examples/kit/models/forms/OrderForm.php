<?php

namespace app\models\forms;

use Yii;
use app\models\Order;
use app\models\OrderItem;
use andy87\yii_components\Kit;

/**
 *  Форма `OrderForm`.
 *  Содержит кастомный параметр `kitOrderItems` и переназначенные методы, для работы с ним:
 *      findOne(), load(), save()
 */
class OrderForm extends Order
{
    /** @var ?Kit $kitOrderItems  */
    public ?Kit $kitOrderItems = null;



    /***
     * @return void
     */
    public function init(): void
    {
        parent::init();

        // Инициализируем комплект
        $this->kitOrderItems = new Kit( OrderItem::class );
    }



    /**
     * @param $condition
     * @return null
     */
    public static function findOne($condition)
    {
        if ( $model = parent::findOne($condition))
        {
            //загружаем в комплект связанные модели
            $model->kitOrderItems->findModels(['order_id' => $model->id]);

            return $model;
        }

        return null;
    }

    /**
     * @param $data
     * @param $formName
     * @return bool
     */
    public function load($data, $formName = null): bool
    {
        // Загружаем в комплект модели из POST
        $this->kitOrderItems->loadModels();

        return parent::load($data, $formName);
    }


    /**
     * @param bool $runValidation
     * @param mixed $attributeNames
     * @return bool
     */
    public function save($runValidation = true, $attributeNames = null): bool
    {
        $error = null;
        $transaction = Yii::$app->db->beginTransaction();

        try {

            if ( parent::save($runValidation, $attributeNames) )
            {
                if ( $this->kitOrderItems->save($runValidation) )
                {
                    $transaction->commit();

                    return true;
                }

                $error = $this->kitOrderItems->getError();
            }

        } catch ( \Exception $e ) {

            $error = $e->getMessage();
        }

        if ( $error ) Yii::$app->session->setFlash('error', $error );

        $transaction->rollBack();

        return false;
    }
}