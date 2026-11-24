<?php

declare(strict_types=1);

namespace BeSmartAndPro\BsapAccountBundle\Model;

final readonly class DocumentPaymentResult
{
    public function __construct(
        private string $documentKey,
        private string $invoiceNumber,
        private string $currency,
        private string $grossAmount,
        private string $bankPaidAmount,
        private string $otherPaidAmount,
        private string $remainingAmount,
        private bool $paid,
        private bool $bankConfirmed,
        private string $status,
        private string $fingerprint,
        private ?string $bankPaidAt,
        private string $direction,
        private string $issueDate,
        private string $vatAmount,
        private ?string $ksefNumber,
    ) {
    }

    public function getDocumentKey(): string
    {
        return $this->documentKey;
    }

    public function getInvoiceNumber(): string
    {
        return $this->invoiceNumber;
    }

    public function getCurrency(): string
    {
        return $this->currency;
    }

    public function getGrossAmount(): string
    {
        return $this->grossAmount;
    }

    public function getBankPaidAmount(): string
    {
        return $this->bankPaidAmount;
    }

    public function getOtherPaidAmount(): string
    {
        return $this->otherPaidAmount;
    }

    public function getRemainingAmount(): string
    {
        return $this->remainingAmount;
    }

    public function getPaid(): bool
    {
        return $this->paid;
    }

    public function getBankConfirmed(): bool
    {
        return $this->bankConfirmed;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getFingerprint(): string
    {
        return $this->fingerprint;
    }

    public function getBankPaidAt(): ?string
    {
        return $this->bankPaidAt;
    }

    public function getDirection(): string
    {
        return $this->direction;
    }

    public function getIssueDate(): string
    {
        return $this->issueDate;
    }

    public function getVatAmount(): string
    {
        return $this->vatAmount;
    }

    public function getKsefNumber(): ?string
    {
        return $this->ksefNumber;
    }
}
