<?php

namespace App\Repository;

use App\Connection\ConnectionFactory;
use App\Entity\User;
use App\Exception\ConnectionException;
use App\Mapper\UserMapper;
use PDO;

class UserRepository
{
    private string $SQL_SELECT_ALL = 'SELECT * FROM users ORDER BY `id` DESC LIMIT :page,10';
    private string $SQL_SELECT_BY_EMAIL = 'SELECT * FROM users WHERE id = :id';
    private string $SQL_INSERT = 'INSERT INTO users (email, name, gender_id, status_id) VALUE (:email, :name, :gender, :status)';
    private string $SQL_UPDATE = 'UPDATE users SET email = :email, name = :name, gender_id = :gender, status_id = :status WHERE id = :id';
    private string $SQL_DELETE = 'DELETE FROM users WHERE id = :id';

    private ConnectionFactory $connectionFactory;

    public function __construct()
    {
        $this->connectionFactory = ConnectionFactory::getInstance();
    }

    public function findAll(int $page): array
    {
        try {
            $connection = $this->connectionFactory->getConnection();
            $stmt = $connection->prepare($this->SQL_SELECT_ALL);
            $page = $page * 10 - 10;

            $stmt->bindParam(':page', $page, PDO::PARAM_INT);

            $stmt->execute();
            $stmt->setFetchMode(PDO::FETCH_ASSOC);
            $dbRaw = $stmt->fetchAll();

            $result = [];
            foreach ($dbRaw as $item) {
                $result[] = UserMapper::toEntity($item);
            }
            $connection = null;

            return $result;
        } catch (ConnectionException $e) {
            die();
        }
    }

    public function findById(int $id): User
    {
        try {
            $connection = $this->connectionFactory->getConnection();
            $stmt = $connection->prepare($this->SQL_SELECT_BY_EMAIL);

            $stmt->bindParam(':id', $id, PDO::PARAM_INT);

            $stmt->execute();

            $stmt->setFetchMode(PDO::FETCH_ASSOC);
            $result = $stmt->fetchAll();

            $connection = null;

            return UserMapper::toEntity($result[0]);
        } catch (ConnectionException $e) {
            die();
        }
    }

    public function addUser(User $toAdd): User
    {
        try {
            $connection = $this->connectionFactory->getConnection();
            $stmt = $connection->prepare($this->SQL_INSERT);

            $email = $toAdd->getEmail();
            $name = $toAdd->getName();
            $gender = $toAdd->getGender()->value;
            $status = $toAdd->getStatus()->value;

            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':name', $name);
            $stmt->bindParam(':gender', $gender);
            $stmt->bindParam(':status', $status);
            $stmt->execute();

            $connection = null;

            return $toAdd;
        } catch (ConnectionException $e) {
            die();
        }
    }

    public function updateUser(User $toUpdate): User
    {
        try {
            $connection = $this->connectionFactory->getConnection();
            $stmt = $connection->prepare($this->SQL_UPDATE);

            $id = $toUpdate->getId();
            $email = $toUpdate->getEmail();
            $name = $toUpdate->getName();
            $gender = $toUpdate->getGender()->value;
            $status = $toUpdate->getStatus()->value;

            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':name', $name);
            $stmt->bindParam(':gender', $gender);
            $stmt->bindParam(':status', $status);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();

            $connection = null;

            return $toUpdate;
        } catch (ConnectionException $e) {
            die();
        }
    }

    public function deleteUser(int $id): void
    {
        try {
            $connection = $this->connectionFactory->getConnection();
            $stmt = $connection->prepare($this->SQL_DELETE);

            $stmt->bindParam(':id', $id);
            $stmt->execute();

            $connection = null;
        } catch (ConnectionException $e) {
            die();
        }
    }
}
