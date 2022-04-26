<?php

namespace app\models;

use yii\db\ActiveRecord;

/**
 * Class Order - Заказ.
 *
 *  ActiveRecord модель для работы с таблицей `order` в базе данных
 *
 * @property int $id
 * @property int $user_id
 * @property int $created_at
 *
 * @package app\models
 */
class Order extends ActiveRecord
{

}