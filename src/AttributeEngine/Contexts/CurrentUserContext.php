<?php


namespace OpenDialogAi\AttributeEngine\Contexts;


use Ds\Map;
use OpenDialogAi\AttributeEngine\Attributes\UserName;
use OpenDialogAi\AttributeEngine\ContextManager\ContextInterface;
use OpenDialogAi\Core\Attribute\HasAttributesTrait;

class CurrentUserContext implements ContextInterface
{
    use HasAttributesTrait;

    private $supportedAttributes;

    public $userName;

    public function __construct()
    {
        $this->attributes = new Map();

        $this->supportedAttributes = [
            UserName::USER_NAME_ATTRIBUTE
        ];

        // Setup a dummy attribute
        $userName = new UserName('Adrian');
        $this->addAttribute($userName);
    }

    public function getSupportedAttributes()
    {
        return $this->supportedAttributes;
    }
}
