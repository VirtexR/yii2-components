<?php

namespace andy87\test\models;

use yii\db\ActiveRecord;

class Test extends ActiveRecord
{
    const STATUS_1 = 1;
    const STATUS_2 = 2;
    const STATUS_3 = 3;
    const STATUS_4 = 4;
    const STATUS_5 = 5;

    public static array $_data = [
        1 => [ 'id' => 1, 'status_id' => self::STATUS_1],
        2 => [ 'id' => 2, 'status_id' => self::STATUS_2],
        3 => [ 'id' => 3, 'status_id' => self::STATUS_3],
    ];

    public int $id;
    public int $status_id;



    /**
     * @param $condition
     * @return ?Test
     */
    public static function findOne($condition): ?Test
    {
        if ( isset(self::$_data[$condition]) )
        {
            $model = new self();
            $model->setAttributes(self::$_data[$condition]);

            return $model;
        }

        return null;
    }

    public static function findAll($condition)
    {
        $models = [];

        foreach ( self::$_data as $datum )
        {
            $model = new self();
            $model->setAttributes($datum);
            $models[] = $model;
        }

        return $models;
    }

    /**
     * @param $runValidation
     * @param $attributeNames
     * @return bool
     */
    public function save($runValidation = true, $attributeNames = null): bool
    {
        $params = $this->getAttributes();

        $id = ( self::$_data[ $this->id ] ) ? $this->id : ( max( array_keys(self::$_data) )  + 1 );

        self::$_data[ $id ] = $params;

        return true;
    }
}