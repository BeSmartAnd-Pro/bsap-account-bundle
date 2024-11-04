<?php

declare(strict_types=1);

namespace BeSmartAndPro\BsapAccountBundle\Client;

use BeSmartAndPro\BsapAccountBundle\Auth\AuthService;
use BeSmartAndPro\BsapAccountBundle\Model\InvoiceRequest;
use BeSmartAndPro\BsapAccountBundle\Model\InvoiceResult;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

readonly class InvoiceClient
{
    protected const string DEV_ENDPOINT  = 'http://ksiegowosc.dev.besmartand.pro/api/graphql';
    protected const string PROD_ENDPOINT = 'https://ksiegowosc.besmartand.pro/api/graphql';
    
    public function __construct(
        protected string $mode,
        protected AuthService $authService,
        protected HttpClientInterface $client,
        protected ValidatorInterface $validator
    ) {
    }
    
    protected function getEndpoint(): string
    {
        return $this->mode === 'production' ? self::PROD_ENDPOINT : self::DEV_ENDPOINT;
    }
    
    protected function executeQuery(string $query, array $variables = []): array
    {
        $response = $this->client->request(
            Request::METHOD_POST,
            $this->getEndpoint(),
            [
                'json'    => [
                    'query'     => $query,
                    'variables' => $variables,
                ],
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->authService->getToken(),
                    'Content-Type'  => 'application/json',
                    'User-Agent'    => 'BSAP Account Client'
                ],
            ]
        );
        
        return $response->toArray();
    }
    
    public function create(InvoiceRequest $request): InvoiceResult
    {
        $query = <<<'QUERY'
        mutation createInvoice (
            $createInvoice: CreateInvoiceInput!
         ) {
            createInvoice(
                createInvoice: $createInvoice
            ) {
                id,
                number,
                content
            }
        }
QUERY;
        
        $result = $this->executeQuery(
            $query,
            [
                'createInvoice' => [
                    'invoicePool'        => [
                        'id' => $request->getInvoicePoolId()
                    ],
                    'paymentInfo'        => $request->getPaymentInfo(),
                    'shippingInfo'       => $request->getShippingInfo(),
                    'paymentDate'        => $request->getPaymentDate()->format('Y-m-d'),
                    'clientBillingData'  => [
                        'firstName'   => $request->getClientBillingData()->getFirstName(),
                        'lastName'    => $request->getClientBillingData()->getLastName(),
                        'companyName' => $request->getClientBillingData()->getCompanyName(),
                        'countryCode' => $request->getClientBillingData()->getCountryCode(),
                        'street'      => $request->getClientBillingData()->getStreet(),
                        'city'        => $request->getClientBillingData()->getCity(),
                        'postcode'    => $request->getClientBillingData()->getPostCode(),
                        'taxId'       => $request->getClientBillingData()->getTaxId(),
                    ],
                    'clientShippingData' => [
                        'firstName'   => $request->getClientShippingData()->getFirstName(),
                        'lastName'    => $request->getClientShippingData()->getLastName(),
                        'companyName' => $request->getClientShippingData()->getCompanyName(),
                        'countryCode' => $request->getClientShippingData()->getCountryCode(),
                        'street'      => $request->getClientShippingData()->getStreet(),
                        'city'        => $request->getClientShippingData()->getCity(),
                        'postcode'    => $request->getClientShippingData()->getPostCode()
                    ],
                    'items' => $request->getItems()
                ]
            ]
        );
        
        return new InvoiceResult(
            $result['data']['createInvoice']['id'],
            $result['data']['createInvoice']['number'],
            base64_decode($result['data']['createInvoice']['content']),
        );
    }
    
    public function download(string $id): ?InvoiceResult
    {
        $query = <<<'QUERY'
        query invoice (
            $id: String!
         ) {
            invoice(
                invoice: {
                    id: $id
                }
            ) {
                id,
                number,
                content
            }
        }
QUERY;
        
        $result = $this->executeQuery(
            $query,
            [
                'id' => $id
            ]
        );
        
        return new InvoiceResult(
            $result['data']['invoice']['id'],
            $result['data']['invoice']['number'],
            base64_decode($result['data']['invoice']['content']),
        );
    }
}
