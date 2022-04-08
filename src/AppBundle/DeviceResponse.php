<?php

namespace App\AppBundle;

class DeviceResponse
{
    private $eventType;

    private $statusCode;

    private $statusMsg;

    private $event;

    private $createdAt;

    public function getEventType(): int
    {
        return $this->eventType;
    }

    public function setEventType(int $eventType): self
    {
        $this->eventType = $eventType;

        return $this;
    }


    public function getStatusCode(): ?int
    {
        return $this->statusCode;
    }

    public function setStatusCode(int $statusCode): self
    {
        $this->statusCode = $statusCode;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getStatusMsg()
    {
        return $this->statusMsg;
    }

    /**
     * @param mixed $statusMsg
     */
    public function setStatusMsg($statusMsg): void
    {
        $this->statusMsg = $statusMsg;
    }

    public function getEvent()
    {
        return $this->event;
    }

    public function setEvent($event): self
    {
        $this->event = $event;

        return $this;
    }



    public function __toString()
    {
        return "[".$this->getStatusCode()."]".$this->getStatusMsg();
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
}
