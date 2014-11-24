<?php
namespace ntentan_test_app\modules\roles;

class Roles extends \ntentan\models\Model
{
    public $hasMany = array(
        'users'
    );

    public $mustBeUnique = array(
        array('field' => 'name', 'message' => 'Two roles cannot have the same name')
    );
}
