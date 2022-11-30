<?php

namespace App\Mapper;

use App\Entity\Enum\Gender;
use App\Entity\Enum\Status;
use App\Entity\User;
use App\Logger\Logger;
use App\Logger\LogLevel;

class UserMapper
{
    public static function toEntity(array $toMap): User
    {
        $user = new User();
        $user->setId($toMap['id'] ?? 0);
        $user->setEmail($toMap['email']);
        $user->setName($toMap['name']);
        $user->setStatus(Status::from($toMap['status_id']));
        $user->setGender(Gender::from($toMap['gender_id']));

        return $user;
    }

    public static function toArray(User $toMap): array
    {
        return [
            'id' => $toMap->getId(),
            'email' => $toMap->getEmail(),
            'name' => $toMap->getName(),
            'gender' => ucfirst(strtolower($toMap->getGender()->name)),
            'status' => ucfirst(strtolower($toMap->getStatus()->name)),
        ];
    }
}
