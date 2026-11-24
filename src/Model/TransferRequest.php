<?php

declare(strict_types=1);

namespace BeSmartAndPro\BsapAccountBundle\Model;

final readonly class TransferRequest
{
    public function __construct(
        private string $documentKey,
        private string $fingerprint,
        private string $creditorIban,
        private string $mode,
        private string $vatAmount = '0.00',
        private string $ksefNumber = '',
    ) {
    }

    public function getDocumentKey(): string
    {
        return $this->documentKey;
    }

    public function getFingerprint(): string
    {
        return $this->fingerprint;
    }

    public function getCreditorIban(): string
    {
        return $this->creditorIban;
    }

    public function getMode(): string
    {
        return $this->mode;
    }

    public function getVatAmount(): string
    {
        return $this->vatAmount;
    }

    public function getKsefNumber(): string
    {
        return $this->ksefNumber;
    }
}
