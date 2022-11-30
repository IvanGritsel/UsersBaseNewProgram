<?php

namespace App\Controller;

use App\Annotation\ControllerMapping;
use App\Annotation\PathVariable;
use App\Annotation\RequestBodyVariable;
use App\Annotation\RequestMapping;
use App\Logger\Logger;
use App\Logger\LogLevel;
use App\Mapper\UserMapper;
use Exception;
use GuzzleHttp\Client;
use Twig\Environment;
use Twig\Extension\DebugExtension;
use Twig\Loader\FilesystemLoader;

/**
 * @ControllerMapping(classMapping="/gorest/users")
 */
class GorestUserController implements Controller
{
    private string $BASE_URL = 'https://gorest.co.in/public/v2/users';
    private string $TOKEN;

    private Logger $logger;
    private Environment $twig;
    private Client $httpClient;

    /**
     * @throws Exception
     */
    public function __construct()
    {
        $this->logger = Logger::getInstance();
        $loader = new FilesystemLoader(__DIR__ . '/../Views');
        $this->twig = new Environment($loader, ['debug' => true]);
        $this->twig->addExtension(new DebugExtension());

        $this->httpClient = new Client();
        $file = fopen(__DIR__ . '/../../resource/token', 'r');
        $this->TOKEN = fread($file, filesize(__DIR__ . '/../../resource/token')) ??
            throw new Exception('No access token found, request aborted');
    }

    /**
     * @param int $page
     *
     * @return string
     *
     * @RequestMapping(path="/gorest/users/all/{page}", method="GET")
     * @PathVariable(variableName="page")
     */
    public function allUsers(int $page): string
    {
        $result = $this->httpClient->get($this->BASE_URL . "?page=$page&per_page=10", [
            'headers' => [
                'Authorization' => "Bearer $this->TOKEN",
            ],
        ]);
        if ($result->getStatusCode() == 200) {
            $users = $result->getBody();

            return $this->twig->render('index.twig', [
                'pageTitle' => "Users page $page",
                'users' => json_decode($users),
                'page' => $page,
                'altDataSource' => true,
            ]);
        } else {
            return $this->twig->render('error.twig', [
                'pageTitle' => $result->getStatusCode(),
                'message' => $result->getBody(),
                'altDataSource' => true,
            ]);
        }
    }

    /**
     * @param int $id
     *
     * @return string
     *
     * @RequestMapping(path="/gorest/users/id/{id}", method="GET")
     * @PathVariable(variableName="id")
     */
    public function userById(int $id): string
    {
        $result = $this->httpClient->get($this->BASE_URL . "/$id", [
            'headers' => [
                'Authorization' => "Bearer $this->TOKEN",
            ],
        ]);

        return $result->getBody();
    }

    /**
     * @param string $userJson
     *
     * @return string
     *
     * @RequestMapping(path="/gorest/users/new", method="POST")
     * @RequestBodyVariable(variableName="userJson")
     */
    public function newUser(string $userJson): string
    {
        $user = UserMapper::toEntity(json_decode($userJson, true));
        $result = $this->httpClient->post($this->BASE_URL, [
            'headers' => [
                'Authorization' => "Bearer $this->TOKEN",
                'Content-Type' => 'application/json',
            ],
            'body' => json_encode([
                'email' => $user->getEmail(),
                'name' => $user->getName(),
                'gender' => strtolower($user->getGender()->name),
                'status' => strtolower($user->getStatus()->name),
            ]),
        ]);
        if ($result->getStatusCode() == 201) {
            return $this->twig->render('success.twig', [
                'pageTitle' => 'Successfully added',
                'action' => 'post',
                'altDataSource' => true,
            ]);
        } else {
            return $this->twig->render('error.twig', [
                'pageTitle' => $result->getStatusCode(),
                'message' => $result->getBody(),
                'altDataSource' => true,
            ]);
        }
    }

    /**
     * @param string $userJson
     *
     * @return string
     *
     * @RequestMapping(path="/gorest/users/update", method="PUT")
     * @RequestBodyVariable(variableName="userJson")
     */
    public function updateUser(string $userJson): string
    {
        $user = UserMapper::toEntity(json_decode($userJson, true));
        $result = $this->httpClient->put($this->BASE_URL . '/' . $user->getId(), [
            'headers' => [
                'Authorization' => "Bearer $this->TOKEN",
                'Content-Type' => 'application/json',
            ],
            'body' => json_encode([
                'email' => $user->getEmail(),
                'name' => $user->getName(),
                'gender' => strtolower($user->getGender()->name),
                'status' => strtolower($user->getStatus()->name),
            ]),
        ]);
        if ($result->getStatusCode() == 200) {
            return $this->twig->render('success.twig', [
                'pageTitle' => 'Successfully updated',
                'action' => 'update',
                'altDataSource' => true,
            ]);
        } else {
            return $this->twig->render('error.twig', [
                'pageTitle' => $result->getStatusCode(),
                'message' => $result->getBody(),
                'altDataSource' => true,
            ]);
        }
    }

    /**
     * @param int $id
     *
     * @return string
     *
     * @RequestMapping(path="/gorest/users/delete/one/{id}", method="DELETE")
     * @PathVariable(variableName="id")
     */
    public function deleteUser(int $id): string
    {
        $result = $this->httpClient->delete($this->BASE_URL . "/$id", [
            'headers' => [
                'Authorization' => "Bearer $this->TOKEN",
            ],
        ]);
        if ($result->getStatusCode() == 204) {
            return $this->twig->render('success.twig', [
                'pageTitle' => 'Successfully deleted',
                'action' => 'delete',
                'altDataSource' => true,
            ]);
        } else {
            return $this->twig->render('error.twig', [
                'pageTitle' => $result->getStatusCode(),
                'message' => $result->getBody(),
                'altDataSource' => true,
            ]);
        }
    }

    /**
     * @param string $idArray
     *
     * @return string
     *
     * @RequestMapping(path="/gorest/users/delete/selected", method="DELETE")
     * @RequestBodyVariable(variableName="idArray")
     */
    public function deleteMultiple(string $idArray): string
    {
        $ids = json_decode($idArray);
        foreach ($ids as $id) {
            $result = $this->httpClient->delete($this->BASE_URL . "/$id", [
                'headers' => [
                    'Authorization' => "Bearer $this->TOKEN",
                ],
            ]);
            if ($result->getStatusCode() != 204) {
                return $this->twig->render('error.twig', [
                    'pageTitle' => $result->getStatusCode(),
                    'message' => $result->getBody(),
                    'altDataSource' => true,
                ]);
            }
        }

        return $this->twig->render('success.twig', [
            'pageTitle' => 'Successfully deleted',
            'action' => 'delete',
            'altDataSource' => true,
        ]);
    }
}
