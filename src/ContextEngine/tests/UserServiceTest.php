<?php

namespace OpenDialogAi\ContextManager\Tests;

use OpenDialogAi\ContextEngine\Contexts\User\UserService;
use OpenDialogAi\Core\Attribute\StringAttribute;
use OpenDialogAi\Core\Tests\TestCase;
use OpenDialogAi\Core\Utterances\Webchat\WebchatTextUtterance;

class UserServiceTest extends TestCase
{
    /* @var UserService */
    private $userService;

    public function setUp(): void
    {
        parent::setUp();

        $this->userService = $this->app->make(UserService::class);
    }


    public function testUserCreation()
    {
        $userId = 'newUser' . time();

        $utterance = new WebchatTextUtterance();
        $utterance->setUserId($userId);

        $this->assertTrue(!$this->userService->userExists($userId));

        $this->userService->createOrUpdateUser($utterance);

        $this->assertTrue($this->userService->userExists($userId));
    }

    public function testUserUpdate()
    {
        // First create a user
        $userId = 'newUser' . time();
        $utterance = new WebchatTextUtterance();
        $utterance->setUserId($userId);

        $user = $this->userService->createOrUpdateUser($utterance);
        $this->assertTrue($this->userService->userExists($userId));

        // Let us get the uid of the user and the timestamp that was set first time
        $uid = $user->getUid();
        $timestamp = $user->getAttribute('user.timestamp');
        $this->assertTrue(isset($uid));
        $this->assertTrue(isset($timestamp));

        $user2 = $this->userService->createOrUpdateUser($utterance);
        $this->assertTrue($user2->getUid() == $user->getUid());
        dump($user2->getAttribute('user.timestamp')->getValue());
        dump($user->getAttribute('user.timestamp')->getValue());
        $this->assertTrue($user2->getAttribute('user.timestamp')->getValue() != $user->getAttribute('user.timestamp')->getValue());

    }

}
