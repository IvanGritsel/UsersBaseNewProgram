<?php

namespace App\Entity;

use App\Entity\Enum\Gender;
use App\Entity\Enum\Status;
use JsonSerializable;

class User implements JsonSerializable
{
    private string $email;
    private string $name;
    private Gender $gender;
    private Status $status;

    /**
     * @return string
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * @param string $email
     */
    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return Gender
     */
    public function getGender(): Gender
    {
        return $this->gender;
    }

    /**
     * @param Gender $gender
     */
    public function setGender(Gender $gender): void
    {
        $this->gender = $gender;
    }

    /**
     * @return Status
     */
    public function getStatus(): Status
    {
        return $this->status;
    }

    /**
     * @param Status $status
     */
    public function setStatus(Status $status): void
    {
        $this->status = $status;
    }

    public function jsonSerialize(): array
    {
        return [
            'email' => $this->getEmail(),
            'name' => $this->getName(),
            'gender' => $this->getGender()->name,
            'status' => $this->getStatus()->name,
        ];
    }
}
