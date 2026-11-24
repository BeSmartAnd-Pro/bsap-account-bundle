<?php

declare(strict_types=1);

namespace BeSmartAndPro\BsapAccountBundle\Client;

use BeSmartAndPro\BsapAccountBundle\Auth\AuthService;
use BeSmartAndPro\BsapAccountBundle\Model\DocumentPaymentReference;
use BeSmartAndPro\BsapAccountBundle\Model\DocumentPaymentResult;
use BeSmartAndPro\BsapAccountBundle\Model\TransferBatchResult;
use BeSmartAndPro\BsapAccountBundle\Model\TransferRequest;
use RuntimeException;
use stdClass;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/** @phpstan-type Variables array<string, string|array<string, string>|list<array<string, string>>> */
final readonly class PaymentClient
{
    private const string PAYMENT_FIELDS = 'documentKey invoiceNumber currency grossAmount bankPaidAmount otherPaidAmount remainingAmount paid bankConfirmed status fingerprint bankPaidAt direction issueDate vatAmount ksefNumber';
    private const string BATCH_FIELDS = 'id status fileName contentBase64 message';

    public function __construct(private AuthService $authService, private HttpClientInterface $client, private ?string $alternativeHost = null)
    {
    }

    public function uploadCostInvoice(string $companyId, string $fileName, string $pdfContent): string
    {
        $query = 'mutation($input: UploadCostInvoiceInput!) { uploadCostInvoice(input: $input) { id status } }';
        $result = $this->execute($query, ['input' => ['companyId' => $companyId, 'fileName' => $fileName, 'pdfBase64' => base64_encode($pdfContent)]], 'uploadCostInvoice');

        return $this->string($result, 'id');
    }

    public function documentPayment(DocumentPaymentReference $reference): DocumentPaymentResult
    {
        return $this->payment($this->execute('query($reference: DocumentPaymentReferenceInput!) { documentPayment(reference: $reference) { ' . self::PAYMENT_FIELDS . ' } }', ['reference' => $this->reference($reference)], 'documentPayment'));
    }

    public function confirmDocumentPaymentManually(DocumentPaymentReference $reference, string $fingerprint, string $paidOn, string $note): DocumentPaymentResult
    {
        $query = 'mutation($reference: DocumentPaymentReferenceInput!, $fingerprint: String!, $paidOn: String!, $note: String!) { confirmDocumentPaymentManually(reference: $reference, fingerprint: $fingerprint, paidOn: $paidOn, note: $note) { ' . self::PAYMENT_FIELDS . ' } }';

        return $this->payment($this->execute($query, ['reference' => $this->reference($reference), 'fingerprint' => $fingerprint, 'paidOn' => $paidOn, 'note' => $note], 'confirmDocumentPaymentManually'));
    }

    /** @param list<TransferRequest> $transfers */
    public function prepareTransferBatch(string $companyId, string $bankAccountId, string $requestKey, string $executionDate, array $transfers): TransferBatchResult
    {
        $items = [];

        foreach ($transfers as $item) {
            $items[] = ['documentKey' => $item->getDocumentKey(), 'fingerprint' => $item->getFingerprint(), 'creditorIban' => $item->getCreditorIban(), 'mode' => $item->getMode(), 'vatAmount' => $item->getVatAmount(), 'ksefNumber' => $item->getKsefNumber()];
        }

        $query = 'mutation($companyId: String!, $bankAccountId: String!, $requestKey: String!, $executionDate: String!, $transfers: [TransferRequestInput!]!) { prepareTransferBatch(companyId: $companyId, bankAccountId: $bankAccountId, requestKey: $requestKey, executionDate: $executionDate, transfers: $transfers) { ' . self::BATCH_FIELDS . ' } }';

        return $this->batch($this->execute($query, ['companyId' => $companyId, 'bankAccountId' => $bankAccountId, 'requestKey' => $requestKey, 'executionDate' => $executionDate, 'transfers' => $items], 'prepareTransferBatch'));
    }

    public function transferBatch(string $companyId, string $id): TransferBatchResult
    {
        return $this->batch($this->execute('query($companyId: String!, $id: String!) { transferBatch(companyId: $companyId, id: $id) { ' . self::BATCH_FIELDS . ' } }', ['companyId' => $companyId, 'id' => $id], 'transferBatch'));
    }

    /** @return array<string, string> */
    private function reference(DocumentPaymentReference $reference): array
    {
        return ['companyId' => $reference->getCompanyId(), 'documentKey' => $reference->getDocumentKey(), 'invoiceNumber' => $reference->getInvoiceNumber(), 'partyTaxId' => $reference->getPartyTaxId(), 'grossAmount' => $reference->getGrossAmount(), 'currency' => $reference->getCurrency(), 'direction' => $reference->getDirection()];
    }

    /** @param Variables $variables */
    private function execute(string $query, array $variables, string $field): stdClass
    {
        $endpoint = rtrim($this->alternativeHost ?: 'https://ksiegowosc.besmartand.pro', '/') . '/api/graphql';
        $response = $this->client->request('POST', $endpoint, ['headers' => ['Authorization' => 'Bearer ' . $this->authService->getToken(), 'User-Agent' => 'BSAP Account Client'], 'json' => ['query' => $query, 'variables' => $variables], 'timeout' => 30, 'max_duration' => 60]);
        $data = json_decode($response->getContent(), false, 512, JSON_THROW_ON_ERROR);

        if (!$data instanceof stdClass || isset($data->errors) || !isset($data->data) || !$data->data instanceof stdClass || !isset($data->data->{$field}) || !$data->data->{$field} instanceof stdClass) {
            $error = $data instanceof stdClass && isset($data->errors) && is_array($data->errors) ? ($data->errors[0] ?? null) : null;

            throw new RuntimeException($error instanceof stdClass && isset($error->message) && is_string($error->message) ? $error->message : 'Niepoprawna odpowiedź API płatności.');
        }

        return $data->data->{$field};
    }

    private function payment(stdClass $data): DocumentPaymentResult
    {
        if (!isset($data->paid, $data->bankConfirmed) || !is_bool($data->paid) || !is_bool($data->bankConfirmed)) {
            throw new RuntimeException('Brak poprawnego potwierdzenia płatności.');
        }

        return new DocumentPaymentResult($this->string($data, 'documentKey'), $this->string($data, 'invoiceNumber'), $this->string($data, 'currency'), $this->string($data, 'grossAmount'), $this->string($data, 'bankPaidAmount'), $this->string($data, 'otherPaidAmount'), $this->string($data, 'remainingAmount'), $data->paid, $data->bankConfirmed, $this->string($data, 'status'), $this->string($data, 'fingerprint'), isset($data->bankPaidAt) ? $this->string($data, 'bankPaidAt') : null, $this->string($data, 'direction'), $this->string($data, 'issueDate'), $this->string($data, 'vatAmount'), isset($data->ksefNumber) ? $this->string($data, 'ksefNumber') : null);
    }

    private function batch(stdClass $data): TransferBatchResult
    {
        $content = base64_decode($this->string($data, 'contentBase64'), true);

        if ($content === false) {
            throw new RuntimeException('Niepoprawna zawartość paczki.');
        }

        return new TransferBatchResult($this->string($data, 'id'), $this->string($data, 'status'), $this->string($data, 'fileName'), $content, $this->string($data, 'message'));
    }

    private function string(stdClass $data, string $key): string
    {
        if (!isset($data->{$key}) || !is_string($data->{$key})) {
            throw new RuntimeException('Brak poprawnego pola odpowiedzi: ' . $key);
        }

        return $data->{$key};
    }
}
