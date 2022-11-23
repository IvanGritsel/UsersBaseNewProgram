<?php

namespace App\Repository;

use App\Connection\ConnectionFactory;
use App\Entity\User;
use App\Exception\ConnectionException;
use App\Mapper\UserMapper;
use PDO;

class UserRepository
{
    private string $SQL_SELECT_ALL = 'SELECT * FROM user';
    private string $SQL_SELECT_BY_EMAIL = 'SELECT * FROM user WHERE email = :email';
    private string $SQL_INSERT = 'INSERT INTO user (email, name, gender, status) VALUE (:email, :name, :gender, :status)';
    private string $SQL_UPDATE = 'UPDATE user SET name = :name, gender = :gender, status = :status WHERE email = :email';
    private string $SQL_DELETE = 'DELETE FROM user WHERE email = :email';

    private ConnectionFactory $connectionFactory;

    public function __construct()
    {
        $this->connectionFactory = ConnectionFactory::getInstance();
    }

    public function findAll(): array
    {
        try {
            $connection = $this->connectionFactory->getConnection();
            $stmt = $connection->prepare($this->SQL_SELECT_ALL);

            $stmt->execute();
            $stmt->setFetchMode(PDO::FETCH_ASSOC);

            $result = [];
            foreach ($stmt->fetchAll() as $item) {
                $result[] = UserMapper::toEntity($item);
            }
            $connection = null;

            return $result;
        } catch (ConnectionException $e) {
            die();
        }
    }

    public function findByEmail(string $email): User
    {
        try {
            $connection = $this->connectionFactory->getConnection();
            $stmt = $connection->prepare($this->SQL_SELECT_BY_EMAIL);

            $stmt->bindParam(':email', $email);

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

            $email = $toUpdate->getEmail();
            $name = $toUpdate->getName();
            $gender = $toUpdate->getGender()->value;
            $status = $toUpdate->getStatus()->value;

            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':name', $name);
            $stmt->bindParam(':gender', $gender);
            $stmt->bindParam(':status', $status);
            $stmt->execute();

            $connection = null;

            return $toUpdate;
        } catch (ConnectionException $e) {
            die();
        }
    }

    public function deleteUser(string $email): void
    {
        try {
            $connection = $this->connectionFactory->getConnection();
            $stmt = $connection->prepare($this->SQL_DELETE);

            $stmt->bindParam(':email', $email);
            $stmt->execute();

            $connection = null;
        } catch (ConnectionException $e) {
            die();
        }
    }
}
