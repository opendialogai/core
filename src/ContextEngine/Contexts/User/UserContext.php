<?php


namespace OpenDialogAi\ContextEngine\Contexts;


use OpenDialogAi\ContextEngine\ContextManager\AbstractContext;
use OpenDialogAi\Core\Graph\Node\Node;

class UserContext extends AbstractContext
{
    const USER_CONTEXT = 'context.core.user';

    /* @var \OpenDialogAi\Core\Graph\Node\Node */
    private $user;

    public function __construct(Node $user)
    {
        parent::__construct(self::USER_CONTEXT);
        $this->user = $user;

        // Move all the user attributes to the context;
        $this->setAttributes($this->user->getAttributes());
    }
}
