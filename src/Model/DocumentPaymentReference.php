<?php

declare(strict_types=1);

namespace BeSmartAndPro\BsapAccountBundle\Model;

final readonly class DocumentPaymentReference
{
    public function __construct(
        private string $companyId,
        private string $documentKey = '',
        private string $invoiceNumber = '',
        private string $partyTaxId = '',
        private string $grossAmount = '',
        private string $currency = 'PLN',
        private string $direction = 'incoming',
    ) {
    }

    public function getCompanyId(): string
    {
        return $this->companyId;
    }

    public function getDocumentKey(): string
    {
        return $this->documentKey;
    }

    public function getInvoiceNumber(): string
    {
        return $this->invoiceNumber;
    }

    public function getPartyTaxId(): string
    {
        return $this->partyTaxId;
    }

    public function getGrossAmount(): string
    {
        return $this->grossAmount;
    }

    public function getCurrency(): string
    {
        return $this->currency;
    }

    public function getDirection(): string
    {
        return $this->direction;
    }
}
