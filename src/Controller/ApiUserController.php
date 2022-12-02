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

    /**
     * Passing parameter to constructor is not recommended. This option was added primarily for testing purposes.
     *
     * @param UserRepository|null $userRepository
     */
    public function __construct(?UserRepository $userRepository = null)
    {
        $this->userRepository = $userRepository ?? new UserRepository();
    }

    /**
     * @param int $page
     *
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
     * @param int $id
     *
     * @return string
     *
     * @RequestMapping(path="/api/users/id/{id}", method="GET")
     * @PathVariable(variableName="id")
     */
    public function userByEmail(int $id): string
    {
        return json_encode($this->userRepository->findById($id));
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
     * @param string $idArray
     *
     * @return void
     *
     * @RequestMapping(path="/api/users/delete/selected", method="DELETE")
     * @RequestBodyVariable(variableName="idArray")
     */
    public function deleteMultiple(string $idArray): void
    {
        $ids = json_decode($idArray);
        foreach ($ids as $id) {
            $this->userRepository->deleteUser($id);
        }
    }

    /**
     * @param int $id
     *
     * @return void
     *
     * @RequestMapping(path="/api/users/delete/one/{id}", method="DELETE")
     * @PathVariable(variableName="id")
     */
    public function deleteUser(int $id): void
    {
        $this->userRepository->deleteUser($id);
    }
}
