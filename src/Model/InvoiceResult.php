<?php

declare(strict_types=1);

namespace BeSmartAndPro\BsapAccountBundle\Model;

readonly class InvoiceResult
{
    public function __construct(
        protected string $id,
        protected string $number,
        protected string $content
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
}
