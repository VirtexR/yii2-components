<?php

namespace app\models;

use yii\db\ActiveRecord;

/**
 * Class OrderItem - Наименование в заказе.
 *
 *  ActiveRecord модель для работы с таблицей `order_item` в базе данных
 *
 * @property int $id
 * @property int $order_id
 * @property int $product_id
 * @property int $cost
 * @property float $price
 * @property int $created_at
 *
 * @package app\models
 */
class OrderItem extends ActiveRecord
{

}