<?php

namespace Test\Controller;

use App\Controller\ApiUserController;
use App\Entity\User;
use App\Mapper\UserMapper;
use PHPUnit\Framework\TestCase;

/**
 * ApiUserController is the only controller that returns raw json containing data instead of rendered twig template.
 */
class ApiUserControllerTest extends TestCase
{
    private array $allUsers;
    private User $oneUser;
    private User $changedUser;

    public function setUp(): void
    {
        $user1 = UserMapper::toEntity(
            [
                'id' => 1,
                'email' => 'mail@mail.com',
                'name' => 'Name Surname',
                'gender_id' => 1,
                'status_id' => 2,
            ]
        );
        $user2 = UserMapper::toEntity(
            [
                'id' => 2,
                'email' => 'doe@mail.com',
                'name' => 'John Doe',
                'gender_id' => 1,
                'status_id' => 1,
            ]
        );
        $user3 = UserMapper::toEntity(
            [
                'id' => 2,
                'email' => 'doe@mail.com',
                'name' => 'Jane Doe',
                'gender_id' => 2,
                'status_id' => 1,
            ]
        );
        $this->allUsers = [$user1, $user2];
        $this->oneUser = $user1;
        $this->changedUser = $user3;
    }

    public function testGetAll()
    {
        $mock = $this->getMockBuilder('App\Repository\UserRepository')->getMock();
        $mock->expects($this->once())
            ->method('findAll')
            ->with(1)
            ->will($this->returnValue($this->allUsers));

        $controller = new ApiUserController($mock);

        $this->assertEquals(json_encode($this->allUsers), $controller->allUsers(1));
    }

    public function testGetOne()
    {
        $mock = $this->getMockBuilder('App\Repository\UserRepository')->getMock();
        $mock->expects($this->once())
            ->method('findById')
            ->with(2)
            ->will($this->returnValue($this->oneUser));

        $controller = new ApiUserController($mock);

        $this->assertEquals(json_encode($this->oneUser), $controller->userByEmail(2));
    }

    public function testAdd()
    {
        $this->oneUser->setId(0);
        $mock = $this->getMockBuilder('App\Repository\UserRepository')->getMock();
        $mock->expects($this->once())
            ->method('addUser')
            ->with($this->oneUser)
            ->will($this->returnValue($this->oneUser));

        $controller = new ApiUserController($mock);

        $this->assertEquals(json_encode($this->oneUser), $controller->newUser(json_encode([
            'email' => 'mail@mail.com',
            'name' => 'Name Surname',
            'gender_id' => 1,
            'status_id' => 2,
        ])));
    }

    public function testUpgrade()
    {
        $mock = $this->getMockBuilder('App\Repository\UserRepository')->getMock();
        $mock->expects($this->once())
            ->method('updateUser')
            ->with($this->changedUser)
            ->will($this->returnValue($this->changedUser));

        $controller = new ApiUserController($mock);

        $this->assertEquals(json_encode($this->changedUser), $controller->updateUser(json_encode([
            'id' => 2,
            'email' => 'doe@mail.com',
            'name' => 'Jane Doe',
            'gender_id' => 2,
            'status_id' => 1,
        ])));
    }

    public function testDelete()
    {
        $mock = $this->getMockBuilder('App\Repository\UserRepository')->getMock();
        $mock->expects($this->once())
            ->method('deleteUser')
            ->with(1);

        $controller = new ApiUserController($mock);

        $controller->deleteUser(1);
    }

    public function testDeleteSelected()
    {
        $mock = $this->getMockBuilder('App\Repository\UserRepository')->getMock();
        $mock->expects($this->exactly(2))
            ->method('deleteUser')
            ->withConsecutive([$this->equalTo(1)], [$this->equalTo(2)]);

        $controller = new ApiUserController($mock);

        $this->assertNull($controller->deleteMultiple(json_encode([1, 2]))); // Ignore warning, works as intended
    }
}
