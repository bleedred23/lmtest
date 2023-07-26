<?php

namespace app\models;

use yii\db\ActiveRecord;

/**
 * @property int $id
 * @property int $type_id
 * @property string $title
 * @property int $price
 */
class Ingredient extends ActiveRecord
{
    public function rules()
    {
        return
            [
                [['id', 'type_id', 'price', 'title'], 'required'],
                [['id', 'type_id', 'price'], 'integer'],
                ['title', 'string'],
            ];
    }
}