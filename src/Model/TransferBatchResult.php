<?php

declare(strict_types=1);

namespace BeSmartAndPro\BsapAccountBundle\Model;

final readonly class TransferBatchResult
{
    public function __construct(
        private string $id,
        private string $status,
        private string $fileName,
        private string $content,
        private string $message,
    ) {
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getFileName(): string
    {
        return $this->fileName;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function getMessage(): string
    {
        return $this->message;
    }
}
