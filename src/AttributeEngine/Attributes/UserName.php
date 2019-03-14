<?php


namespace OpenDialogAi\AttributeEngine\Attributes;


use OpenDialogAi\Core\Attribute\StringAttribute;

class UserName extends StringAttribute
{
    const USER_NAME_ATTRIBUTE = 'opendialog.attribute.userName';

    public function __construct(string $userName)
    {
        parent::__construct(self::USER_NAME_ATTRIBUTE, $userName);
    }
}
