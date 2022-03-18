<?php

namespace App\AppBundle;

class DeviceResponse
{
    private $dataType;

    private $statusCode;

    private $statusMsg;

    private $response;

    public function getDataType(): int
    {
        return $this->dataType;
    }

    public function setDataType(int $dataType): self
    {
        $this->dataType = $dataType;

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

    public function getResponse()
    {
        return $this->response;
    }

    public function setResponse($response): self
    {
        $this->response = $response;

        return $this;
    }

    public function __toString()
    {
        return "[".$this->getStatusCode()."]".$this->getStatusMsg();
    }
}
