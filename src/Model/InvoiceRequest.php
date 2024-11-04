<?php

declare(strict_types=1);

namespace BeSmartAndPro\BsapAccountBundle\Model;

use DateTime;

readonly class InvoiceRequest
{
    public function __construct(
        protected string $invoicePoolId,
        protected string $paymentInfo,
        protected string $shippingInfo,
        protected DateTime $paymentDate,
        protected InvoiceProfileData $clientBillingData,
        protected InvoiceProfileData $clientShippingData,
        /** @var InvoiceLineItem[] */
        protected array $items,

    ) {
    }

    public function getInvoicePoolId(): string
    {
        return $this->invoicePoolId;
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

    public function getClientShippingData(): InvoiceProfileData
    {
        return $this->clientShippingData;
    }

    public function getItems(): array
    {
        $result = [];

        foreach ($this->items as $item) {
            $result[] = [
                'name'     => $item->getName(),
                'quantity' => $item->getQuantity(),
                'price'    => $item->getPrice(),
                'currency' => $item->getCurrency(),
                'tax'      => [
                    'id' => $item->getTaxId(),
                ]
            ];
        }

        return $result;
    }
}
