<?php

namespace app\models;

use yii\db\ActiveRecord;

/**
 * @property int $id
 * @property string $title
 * @property string $code
 */
class IngredientType extends ActiveRecord
{
    public function rules()
    {
        return
            [
                [['id', 'code', 'title'], 'required'],
                ['id', 'integer'],
                [['code', 'title',], 'string'],
            ];
    }
}