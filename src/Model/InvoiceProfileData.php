<?php

declare(strict_types=1);

namespace BeSmartAndPro\BsapAccountBundle\Model;

readonly class InvoiceProfileData
{
    public function __construct(
        protected string $countryCode,
        protected string $street,
        protected string $city,
        protected string $postCode,
        protected ?string $firstName,
        protected ?string $lastName,
        protected ?string $companyName,
        protected ?string $taxId
    ) {
    }

    public function getCountryCode(): string
    {
        return $this->countryCode;
    }

    public function getStreet(): string
    {
        return $this->street;
    }

    public function getCity(): string
    {
        return $this->city;
    }

    public function getPostCode(): string
    {
        return $this->postCode;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function getCompanyName(): ?string
    {
        return $this->companyName;
    }

    public function getTaxId(): ?string
    {
        return $this->taxId;
    }
}
