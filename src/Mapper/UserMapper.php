<?php

namespace App\Mapper;

use App\Entity\Enum\Gender;
use App\Entity\Enum\Status;
use App\Entity\User;

class UserMapper
{
    public static function toEntity(array $toMap): User
    {
        $user = new User();
        $user->setEmail($toMap['email']);
        $user->setName($toMap['name']);
        $user->setStatus(Status::from($toMap['status']));
        $user->setGender(Gender::from($toMap['gender']));

        return $user;
    }
}
