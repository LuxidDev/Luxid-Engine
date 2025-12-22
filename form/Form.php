<?php

namespace engine\system\form;

use engine\system\Entity;
use engine\system\form\InputField;

class Form
{
    public static function begin($action, $method)
    {
        echo sprintf('<form action="%s" method="%s" >', $action, $method);
        return new Form();
    }

    public static function end()
    {
        echo '</form>';
    }

    public function field(Entity $entity, $attribute)
    {
        return new InputField($entity, $attribute);
    }
}

