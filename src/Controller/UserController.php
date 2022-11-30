<?php

namespace App\Controller;

use App\Annotation\ControllerMapping;
use App\Annotation\PathVariable;
use App\Annotation\RequestBodyVariable;
use App\Annotation\RequestMapping;
use App\Logger\Logger;
use App\Logger\LogLevel;
use App\Mapper\UserMapper;
use App\Repository\UserRepository;
use Exception;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

/**
 * @ControllerMapping(classMapping="/users")
 */
class UserController implements Controller
{
    private UserRepository $userRepository;
    private Environment $twig;

    public function __construct()
    {
        $this->userRepository = new UserRepository();
        $loader = new FilesystemLoader(__DIR__ . '/../Views');
        $this->twig = new Environment($loader);
    }

    /**
     * @param int $page
     *
     * @return string
     *
     * @RequestMapping(path="/users/all/{page}", method="GET")
     * @PathVariable(variableName="page")
     */
    public function allUsers(int $page): string
    {
        try {
            $users = $this->userRepository->findAll($page);
            $usersArray = [];
            foreach ($users as $user) {
                $usersArray[] = UserMapper::toArray($user);
            }

            return $this->twig->render('index.twig', [
                'pageTitle' => "Users page $page",
                'users' => $usersArray,
                'page' => $page,
                'altDataSource' => false,
            ]);
        } catch (Exception $e) {
            return $this->twig->render('error.twig', [
                'pageTitle' => $e->getCode(),
                'message' => $e->getMessage(),
                'altDataSource' => false,
            ]);
        }
    }

    /**
     * @param int $id
     *
     * @return string
     *
     * @RequestMapping(path="/users/id/{id}", method="GET")
     * @PathVariable(variableName="id")
     */
    public function userById(int $id): string
    {
        return json_encode($this->userRepository->findById($id));
    }

    /**
     * @param string $userJson
     *
     * @return string
     *
     * @RequestMapping(path="/users/new", method="POST")
     * @RequestBodyVariable(variableName="userJson")
     */
    public function newUser(string $userJson): string
    {
        try {
            $newUser = $this->userRepository->addUser(UserMapper::toEntity(json_decode($userJson, true)));
            $userArray = UserMapper::toArray($newUser);

            return $this->twig->render('success.twig', [
                'pageTitle' => 'Successfully added',
                'action' => 'post',
                'user' => $userArray,
                'altDataSource' => false,
            ]);
        } catch (Exception $e) {
            return $this->twig->render('error.twig', [
                'pageTitle' => $e->getCode(),
                'message' => $e->getMessage(),
                'altDataSource' => false,
            ]);
        }
    }

    /**
     * @param string $userJson
     *
     * @return string
     *
     * @RequestMapping(path="/users/update", method="PUT")
     * @RequestBodyVariable(variableName="userJson")
     */
    public function updateUser(string $userJson): string
    {
        Logger::getInstance()->log(LogLevel::DEBUG, $userJson);
        try {
            $updatedUser = $this->userRepository->updateUser(UserMapper::toEntity(json_decode($userJson, true)));
            $userArray = UserMapper::toArray($updatedUser);

            return $this->twig->render('success.twig', [
                'pageTitle' => 'Successfully updated',
                'action' => 'update',
                'user' => $userArray,
                'altDataSource' => false,
            ]);
        } catch (Exception $e) {
            return $this->twig->render('error.twig', [
                'pageTitle' => $e->getCode(),
                'message' => $e->getMessage(),
                'altDataSource' => false,
            ]);
        }
    }

    /**
     * @param string $idArray
     *
     * @return string
     *
     * @RequestMapping(path="/users/delete/selected", method="DELETE")
     * @RequestBodyVariable(variableName="idArray")
     */
    public function deleteMultiple(string $idArray): string
    {
        try {
            $ids = json_decode($idArray);
            foreach ($ids as $id) {
                $this->userRepository->deleteUser($id);
            }

            return $this->twig->render('success.twig', [
                'pageTitle' => 'Successfully deleted',
                'action' => 'delete',
                'altDataSource' => false,
            ]);
        } catch (Exception $e) {
            return $this->twig->render('error.twig', [
                'pageTitle' => $e->getCode(),
                'message' => $e->getMessage(),
                'altDataSource' => false,
            ]);
        }
    }

    /**
     * @param int $id
     *
     * @return string
     *
     * @RequestMapping(path="/users/delete/one/{id}", method="DELETE")
     * @PathVariable(variableName="id")
     */
    public function deleteUser(int $id): string
    {
        try {
            $this->userRepository->deleteUser($id);

            return $this->twig->render('success.twig', [
                'pageTitle' => 'Successfully deleted',
                'action' => 'delete',
                'altDataSource' => false,
            ]);
        } catch (Exception $e) {
            return $this->twig->render('error.twig', [
                'pageTitle' => $e->getCode(),
                'message' => $e->getMessage(),
                'altDataSource' => false,
            ]);
        }
    }
}
