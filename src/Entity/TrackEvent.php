<?php

namespace App\Entity;

use App\Repository\TrackEventRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TrackEventRepository::class)]
//todo set ORM params correctly before persist
class TrackEvent
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'string', length: 15)]
    private $imei;

    #[ORM\Column(type: 'string', length: 15, nullable: true)]
    private $simCard;

    #[ORM\Column(type: 'string', length: 50, nullable: true)]
    private $keyword;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private $deviceTime;

    #[ORM\Column(type: 'boolean', nullable: true)]
    private $isFromGps;

    #[ORM\Column(type: 'string', length: 20)]
    private $latitude;

    #[ORM\Column(type: 'string', length: 20)]
    private $longitude;

    #[ORM\Column(type: 'smallint', nullable: true)]
    private $speed;

    #[ORM\Column(type: 'smallint', nullable: true)]
    private $direction;

    #[ORM\Column(type: 'float', nullable: true)]
    private $altitude;

    #[ORM\Column(type: 'smallint', nullable: true)]
    private $accState;

    #[ORM\Column(type: 'smallint', nullable: true)]
    private $doorState;

    #[ORM\Column(type: 'smallint', nullable: true)]
    private $jammer;

    #[ORM\Column(type: 'integer', nullable: true)]
    private $temperature;

    #[ORM\Column(type: 'datetime')]
    private $createdAt;

    #[ORM\Column(type: 'smallint', nullable: true)]
    private $towerSignal;


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

    public function getSimCard(): ?string
    {
        return $this->simCard;
    }

    public function setSimCard(?string $simCard): self
    {
        $this->simCard = $simCard;

        return $this;
    }

    public function getKeyword(): ?string
    {
        return $this->keyword;
    }

    public function setKeyword(?string $keyword): self
    {
        $this->keyword = $keyword;

        return $this;
    }

    public function getDeviceTime(): ?string
    {
        return $this->deviceTime;
    }

    public function setDeviceTime(?string $deviceTime): self
    {
        $this->deviceTime = $deviceTime;

        return $this;
    }

    public function getIsFromGps(): ?bool
    {
        return $this->isFromGps;
    }

    public function setIsFromGps(?string $isFromGps): self
    {
        $this->isFromGps = $isFromGps;

        return $this;
    }

    public function getLatitude(): ?string
    {
        return $this->latitude;
    }

    public function setLatitude(string $latitude): self
    {
        $this->latitude = $latitude;

        return $this;
    }

    public function getLongitude(): ?string
    {
        return $this->longitude;
    }

    public function setLongitude(string $longitude): self
    {
        $this->longitude = $longitude;

        return $this;
    }

    public function getSpeed(): ?string
    {
        return $this->speed;
    }

    public function setSpeed(?string $speed): self
    {
        $this->speed = $speed;

        return $this;
    }

    public function getDirection(): ?string
    {
        return $this->direction;
    }

    public function setDirection(?string $direction): self
    {
        $this->direction = $direction;

        return $this;
    }

    public function getAltitude(): ?string
    {
        return $this->altitude;
    }

    public function setAltitude(?string $altitude): self
    {
        $this->altitude = $altitude;

        return $this;
    }

    public function getAccState(): ?string
    {
        return $this->accState;
    }

    public function setAccState(?string $accState): self
    {
        $this->accState = $accState;

        return $this;
    }

    public function getDoorState(): ?string
    {
        return $this->doorState;
    }

    public function setDoorState(string $doorState): self
    {
        $this->doorState = $doorState;

        return $this;
    }

    public function getJammer(): ?string
    {
        return $this->jammer;
    }

    public function setJammer(?string $jammer): self
    {
        $this->jammer = $jammer;

        return $this;
    }

    public function getTemperature(): ?string
    {
        return $this->temperature;
    }

    public function setTemperature(?string $temperature): self
    {
        $this->temperature = $temperature;

        return $this;
    }

    public function getCreatedAt(): ?string
    {
        return $this->createdAt;
    }

    public function setCreatedAt(string $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getTowerSignal(): ?string
    {
        return $this->towerSignal;
    }

    public function setTowerSignal(?string $towerSignal): self
    {
        $this->towerSignal = $towerSignal;

        return $this;
    }
}
