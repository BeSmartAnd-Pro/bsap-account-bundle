# BSAP Account Bundle

Bundle provides authentication and invoice client integration for BSAP Accounting.

## Installation

```bash
composer require besmartand-pro/bsap-account-bundle
```

## Configuration

Default endpoints point to the production BSAP Accounting instance:

- `https://ksiegowosc.besmartand.pro/api/login_check`
- `https://ksiegowosc.besmartand.pro/api/graphql`

Use `alternativeHost` only when you want to connect to a different environment, for example local or development.

```yaml
bsap_account:
  username: '%env(resolve:BESMARTANDPRO_KSIEGOWOSC_USERNAME)%'
  password: '%env(resolve:BESMARTANDPRO_KSIEGOWOSC_PASSWORD)%'
  alternativeHost: '%env(resolve:BESMARTANDPRO_KSIEGOWOSC_HOST)%'
```

If `alternativeHost` is set to `https://ksiegowosc.dev.besmartand.pro`, the bundle will use:

- `https://ksiegowosc.dev.besmartand.pro/api/login_check`
- `https://ksiegowosc.dev.besmartand.pro/api/graphql`

If you do not need an override, omit `alternativeHost`.

## Payments API (1.3.0)

`Client\PaymentClient` uses the configured JWT credentials and accounting host. It provides:

- `documentPayment(DocumentPaymentReference $reference): DocumentPaymentResult` — reads incoming/outgoing invoice settlements, including partial payments, bank confirmation, manual settlements and the current fingerprint.
- `confirmDocumentPaymentManually($reference, $fingerprint, $paidOn, $note)` — records the remaining amount with an audit trail; it does not assert bank confirmation.
- `uploadCostInvoice($companyId, $fileName, $pdfContent): string` — uploads a PDF for the existing accounting verification workflow and returns its document ID.
- `prepareTransferBatch($companyId, $bankAccountId, $requestKey, $executionDate, $transfers)` — requests a package using typed `Model\TransferRequest` instructions.
- `transferBatch($companyId, $id)` — reads an existing package and its current settlement status.

Requires the corresponding accounting API deployment and database migrations. Company and bank account IDs refer to records configured in the accounting application.

`DocumentPaymentReference` accepts a company ID plus `sale:<uuid>` or `cost:<uuid>`. Without a document key, supply the exact invoice number, counterparty tax ID, gross decimal amount, currency and direction. A VAT RR invoice issued by the buyer has an outgoing payment direction even when stored as `sale:<uuid>`.

Transfer modes are `domestic`, `split_payment`, `reverse_charge` and `vat_rr`. Explicit classification must follow verified document data. A difference between net and gross amounts is not a payment classification rule.

**mBank firma / retail:** the mBank v3 bundle PIS endpoint is unsupported. The accounting API returns `MANUAL_ACTION_REQUIRED` with no file. The calling portal should notify an operator and retain its manual payment workflow.

**mBank CompanyNet:** an account explicitly configured for CompanyNet can generate a pain.001.001.09 file. `READY_FOR_BANK_IMPORT` means import and authorization are still required in the bank. File generation does not initiate or confirm a payment. The request key is idempotent; reuse with a different payload is rejected. The API reserves the documents against another export. The package content is decoded to binary XML by this client.

`BANK_CONFIRMED` identifies a full settlement from current imported bank allocations. `SETTLED` can also represent manual settlement, offset or opening balance. `REVIEW_REQUIRED` and `PARTIAL` must not be treated as full payment. Queries read already imported/reconciled history; they do not themselves fetch new bank statements.

Reference: [mBank API v3](https://developer.api.mbank.pl/documentation/api-v3).
