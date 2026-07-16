<?php

declare(strict_types=1);

namespace BeSmartAndPro\BsapAccountBundle\Client;

use BeSmartAndPro\BsapAccountBundle\Auth\AuthService;
use BeSmartAndPro\BsapAccountBundle\Model\InvoiceRequest;
use BeSmartAndPro\BsapAccountBundle\Model\InvoiceResult;
use RuntimeException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * @phpstan-type InvoiceLineItem array{
 *     name: string,
 *     quantity: int|float,
 *     price: int|float,
 *     originalPrice: int|float,
 *     currency: string,
 *     taxRate: int|float
 * }
 * @phpstan-type InvoiceBillingData array{
 *     firstName: string|null,
 *     lastName: string|null,
 *     companyName: string|null,
 *     countryCode: string,
 *     street: string,
 *     city: string,
 *     postcode: string,
 *     taxId: string|null
 * }
 * @phpstan-type InvoiceShippingData array{
 *     firstName: string|null,
 *     lastName: string|null,
 *     companyName: string|null,
 *     countryCode: string,
 *     street: string,
 *     city: string,
 *     postcode: string
 * }
 * @phpstan-type CreateInvoiceVariables array{
 *     createInvoice: array{
 *         paymentInfo: string,
 *         shippingInfo: string,
 *         calculateOnNetto: bool,
 *         wdt: bool,
 *         vatRr: bool,
 *         type: string,
 *         parentInvoiceId: string|null,
 *         additionalContent: string|null,
 *         paymentDate: string,
 *         clientBillingData: InvoiceBillingData,
 *         clientShippingData: InvoiceShippingData,
 *         items: array<int, InvoiceLineItem>
 *     }
 * }
 * @phpstan-type DownloadInvoiceVariables array{id: string}
 * @phpstan-type InvoiceData array{
 *     id: string,
 *     number: string,
 *     content: string,
 *     ksefNumber?: string|null,
 *     ksefStatus?: string|null,
 *     ksefStatusDescription?: string|null,
 *     type?: string|null,
 *     parentInvoiceId?: string|null,
 *     correction?: bool|null
 * }
 * @phpstan-type ExecuteQueryResult array{
 *     data: array{
 *         createInvoice?: InvoiceData,
 *         invoice?: InvoiceData
 *     },
 *     errors?: array<int, array{message: string}>
 * }
 */
readonly class InvoiceClient
{
    protected const string DEFAULT_ENDPOINT = 'https://ksiegowosc.besmartand.pro/api/graphql';

    public function __construct(
        protected AuthService $authService,
        protected HttpClientInterface $client,
        protected ValidatorInterface $validator,
        protected ?string $alternativeHost = null,
    ) {
    }

    protected function getEndpoint(): string
    {
        if ($this->alternativeHost) {
            return $this->alternativeHost . '/api/graphql';
        }

        return self::DEFAULT_ENDPOINT;
    }

    /**
     * @param array<string, array<string, array<string, string|null>|array<int, array<string, int|float|string>>|bool|string|null>|string> $variables
     * @phpstan-param CreateInvoiceVariables|DownloadInvoiceVariables|array{} $variables
     * @return array<string, array<string, array<string, bool|string|null>>|array<int, array{message: string}>>
     * @phpstan-return ExecuteQueryResult
     */
    protected function executeQuery(string $query, array $variables = []): array
    {
        $response = $this->client->request(
            Request::METHOD_POST,
            $this->getEndpoint(),
            [
                'json' => [
                    'query' => $query,
                    'variables' => $variables,
                ],
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->authService->getToken(),
                    'Content-Type' => 'application/json',
                    'User-Agent' => 'BSAP Account Client',
                ],
            ],
        );

        /** @var ExecuteQueryResult $result */
        $result = $response->toArray(false);

        return $result;
    }

    public function create(InvoiceRequest $request): InvoiceResult
    {
        return $this->createInvoice($request, false);
    }

    public function createVatRr(InvoiceRequest $request): InvoiceResult
    {
        return $this->createInvoice($request, true);
    }

    private function createInvoice(InvoiceRequest $request, bool $vatRr): InvoiceResult
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
                content,
                ksefNumber,
                ksefStatus,
                ksefStatusDescription,
                type,
                parentInvoiceId,
                correction
            }
        }
QUERY;

        $result = $this->executeQuery(
            $query,
            [
                'createInvoice' => [
                    'paymentInfo' => $request->getPaymentInfo(),
                    'shippingInfo' => $request->getShippingInfo(),
                    'calculateOnNetto' => $request->isCalculateOnNetto(),
                    'wdt' => $request->isWdt(),
                    'vatRr' => $vatRr,
                    'type' => $request->getType(),
                    'parentInvoiceId' => $request->getParentInvoiceId(),
                    'additionalContent' => $request->getAdditionalContent(),
                    'paymentDate' => $request->getPaymentDate()->format('Y-m-d'),
                    'clientBillingData' => [
                        'firstName' => $request->getClientBillingData()->getFirstName(),
                        'lastName' => $request->getClientBillingData()->getLastName(),
                        'companyName' => $request->getClientBillingData()->getCompanyName(),
                        'countryCode' => $request->getClientBillingData()->getCountryCode(),
                        'street' => $request->getClientBillingData()->getStreet(),
                        'city' => $request->getClientBillingData()->getCity(),
                        'postcode' => $request->getClientBillingData()->getPostCode(),
                        'taxId' => $request->getClientBillingData()->getTaxId(),
                    ],
                    'clientShippingData' => [
                        'firstName' => $request->getClientShippingData()->getFirstName(),
                        'lastName' => $request->getClientShippingData()->getLastName(),
                        'companyName' => $request->getClientShippingData()->getCompanyName(),
                        'countryCode' => $request->getClientShippingData()->getCountryCode(),
                        'street' => $request->getClientShippingData()->getStreet(),
                        'city' => $request->getClientShippingData()->getCity(),
                        'postcode' => $request->getClientShippingData()->getPostCode(),
                    ],
                    'items' => $request->getItems(),
                ],
            ],
        );

        if (isset($result['errors'])) {
            throw new RuntimeException($result['errors'][0]['message']);
        }

        return $this->createInvoiceResult($result['data']['createInvoice']);
    }

    /**
     * @param array{id: string, number: string, content: string, ksefNumber?: string|null, ksefStatus?: string|null, ksefStatusDescription?: string|null, type?: string|null, parentInvoiceId?: string|null, correction?: bool|null} $invoiceData
     */
    private function createInvoiceResult(array $invoiceData): InvoiceResult
    {
        return new InvoiceResult(
            $invoiceData['id'],
            $invoiceData['number'],
            base64_decode($invoiceData['content']),
            $invoiceData['ksefNumber'] ?? null,
            $invoiceData['ksefStatus'] ?? null,
            $invoiceData['ksefStatusDescription'] ?? null,
            $invoiceData['type'] ?? 'default',
            $invoiceData['parentInvoiceId'] ?? null,
            $invoiceData['correction'] ?? false,
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
                content,
                ksefNumber,
                ksefStatus,
                ksefStatusDescription,
                type,
                parentInvoiceId,
                correction
            }
        }
QUERY;

        $result = $this->executeQuery(
            $query,
            [
                'id' => $id,
            ],
        );

        return $this->createInvoiceResult($result['data']['invoice']);
    }
}
