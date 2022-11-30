<?php

namespace App\Controller;

use App\Annotation\ControllerMapping;
use App\Annotation\PathVariable;
use App\Annotation\RequestBodyVariable;
use App\Annotation\RequestMapping;
use App\Mapper\UserMapper;
use App\Repository\UserRepository;

/**
 * @ControllerMapping(classMapping="/api/users")
 */
class ApiUserController implements Controller
{
    private UserRepository $userRepository;

    public function __construct()
    {
        $this->userRepository = new UserRepository();
    }

    /**
     * @param int $page
     * @return string
     *
     * @RequestMapping(path="/api/users/all/{page}", method="GET")
     * @PathVariable(variableName="page")
     */
    public function allUsers(int $page): string
    {
        return json_encode($this->userRepository->findAll($page));
    }

    /**
     * @param string $email
     *
     * @return string
     *
     * @RequestMapping(path="/api/users/email/{email}", method="GET")
     * @PathVariable(variableName="email")
     */
    public function userByEmail(string $email): string
    {
        return json_encode($this->userRepository->findById($email));
    }

    /**
     * @param string $userJson
     *
     * @return string
     *
     * @RequestMapping(path="/api/users/new", method="POST")
     * @RequestBodyVariable(variableName="userJson")
     */
    public function newUser(string $userJson): string
    {
        return json_encode($this->userRepository->addUser(UserMapper::toEntity(json_decode($userJson, true))));
    }

    /**
     * @param string $userJson
     *
     * @return string
     *
     * @RequestMapping(path="/api/users/update", method="PUT")
     * @RequestBodyVariable(variableName="userJson")
     */
    public function updateUser(string $userJson): string
    {
        return json_encode($this->userRepository->updateUser(UserMapper::toEntity(json_decode($userJson, true))));
    }

    /**
     * @param string $email
     *
     * @return void
     *
     * @RequestMapping(path="/api/users/delete/{email}", method="DELETE")
     * @PathVariable(variableName="email")
     */
    public function deleteUser(string $email): void
    {
        $this->userRepository->deleteUser($email);
    }

    /**
     * @param string $emailArray
     *
     * @return void
     *
     * @RequestMapping(path="/api/users/delete/selected", method="DELETE")
     * @RequestBodyVariable(variableName="emailArray")
     */
    public function deleteMultiple(string $emailArray): void
    {
        $emails = json_decode($emailArray);
        foreach ($emails as $email) {
            $this->userRepository->deleteUser($email);
        }
    }
}
