<?php

namespace App\Entity;

use App\Repository\LoginEventRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: LoginEventRepository::class)]
class LoginEvent
{
    const TYPE = 1;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'string', length: 15)]
    private $imei;

    #[ORM\Column(type: 'string', length: 255)]
    private $response;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getImei(): ?string
    {
        return $this->imei;
    }

    public function setImei(string $imei): self
    {
        $this->imei = $imei;

        return $this;
    }

    public function getResponse(): ?string
    {
        return $this->response;
    }

    public function setResponse(string $response): self
    {
        $this->response = $response;

        return $this;
    }
}
