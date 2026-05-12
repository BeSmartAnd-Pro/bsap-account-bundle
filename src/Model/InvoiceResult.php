<?php

declare(strict_types=1);

namespace BeSmartAndPro\BsapAccountBundle\Model;

readonly class InvoiceResult
{
    public function __construct(
        protected string $id,
        protected string $number,
        protected string $content,
        protected ?string $ksefNumber = null,
        protected ?string $ksefStatus = null,
        protected ?string $ksefStatusDescription = null,
        protected string $type = 'default',
        protected ?string $parentInvoiceId = null,
        protected bool $correction = false,
    ) {
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getNumber(): string
    {
        return $this->number;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function getKsefNumber(): ?string
    {
        return $this->ksefNumber;
    }

    public function getKsefStatus(): ?string
    {
        return $this->ksefStatus;
    }

    public function getKsefStatusDescription(): ?string
    {
        return $this->ksefStatusDescription;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getParentInvoiceId(): ?string
    {
        return $this->parentInvoiceId;
    }

    public function isCorrection(): bool
    {
        return $this->correction;
    }
}
