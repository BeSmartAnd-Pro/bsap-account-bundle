<?php

declare(strict_types=1);

namespace BeSmartAndPro\BsapAccountBundle\Model;

readonly class InvoiceLineItem
{
    public function __construct(
        protected string $name,
        protected int $quantity,
        protected float $price,
        protected string $currency,
        protected float $taxRate,
        protected float $originalPrice,
    ) {
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }

    public function getPrice(): float
    {
        return $this->price;
    }

    public function getCurrency(): string
    {
        return $this->currency;
    }

    public function getTaxRate(): float
    {
        return $this->taxRate;
    }

    public function getOriginalPrice(): float
    {
        return $this->originalPrice;
    }
}
