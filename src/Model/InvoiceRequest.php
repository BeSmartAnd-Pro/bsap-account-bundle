<?php

declare(strict_types=1);

namespace BeSmartAndPro\BsapAccountBundle\Model;

use DateTime;

readonly class InvoiceRequest
{
    public function __construct(
        protected string $paymentInfo,
        protected string $shippingInfo,
        protected DateTime $paymentDate,
        protected InvoiceProfileData $clientBillingData,
        protected InvoiceProfileData $clientShippingData,
        protected bool $calculateOnNetto,
        protected bool $wdt,
        /** @var InvoiceLineItem[] */
        protected array $items,
        protected string $type = 'default',
        protected ?string $parentInvoiceId = null,
        protected ?string $additionalContent = null,
    ) {
    }

    public function getPaymentInfo(): string
    {
        return $this->paymentInfo;
    }

    public function getShippingInfo(): string
    {
        return $this->shippingInfo;
    }

    public function getPaymentDate(): DateTime
    {
        return $this->paymentDate;
    }

    public function getClientBillingData(): InvoiceProfileData
    {
        return $this->clientBillingData;
    }

    public function isCalculateOnNetto(): bool
    {
        return $this->calculateOnNetto;
    }

    public function isWdt(): bool
    {
        return $this->wdt;
    }

    public function getClientShippingData(): InvoiceProfileData
    {
        return $this->clientShippingData;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getParentInvoiceId(): ?string
    {
        return $this->parentInvoiceId;
    }

    public function getAdditionalContent(): ?string
    {
        return $this->additionalContent;
    }

    public function getItems(): array
    {
        $result = [];

        foreach ($this->items as $item) {
            $result[] = [
                'name' => $item->getName(),
                'quantity' => $item->getQuantity(),
                'price' => $item->getPrice(),
                'originalPrice' => $item->getOriginalPrice(),
                'currency' => $item->getCurrency(),
                'taxRate' => $item->getTaxRate(),
            ];
        }

        return $result;
    }
}
