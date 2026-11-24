<?php

declare(strict_types=1);

namespace BeSmartAndPro\BsapAccountBundle\Tests;

use BeSmartAndPro\BsapAccountBundle\Auth\AuthService;
use BeSmartAndPro\BsapAccountBundle\Client\PaymentClient;
use BeSmartAndPro\BsapAccountBundle\Model\DocumentPaymentReference;
use BeSmartAndPro\BsapAccountBundle\Model\TransferRequest;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

final class PaymentClientTest extends TestCase
{
    public function testGraphqlErrorsAndPartialResponsesNeverBecomePaymentConfirmations(): void
    {
        $auth = $this->createMock(AuthService::class);
        $auth->method('getToken')->willReturn('test-token');
        $client = new PaymentClient($auth, new MockHttpClient(new MockResponse('{"data":{"documentPayment":{"paid":true}},"errors":[{"message":"Forbidden"}]}')), 'https://accounting.example');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Forbidden');
        $client->documentPayment(new DocumentPaymentReference('company', 'sale:invoice'));
    }

    public function testMissingConfirmationIsRejected(): void
    {
        $auth = $this->createMock(AuthService::class);
        $auth->method('getToken')->willReturn('test-token');
        $client = new PaymentClient($auth, new MockHttpClient(new MockResponse('{"data":{"documentPayment":{"paid":true}}}')));

        $this->expectException(RuntimeException::class);
        $client->documentPayment(new DocumentPaymentReference('company', 'sale:invoice'));
    }

    public function testRetailFallbackHasNoPaymentFileAndKeepsTheRequestKey(): void
    {
        $auth = $this->createMock(AuthService::class);
        $auth->method('getToken')->willReturn('test-token');
        $http = new MockHttpClient(static function (string $method, string $url, array $options): MockResponse {
            self::assertSame('POST', $method);
            self::assertSame('https://accounting.example/api/graphql', $url);
            $body = json_decode($options['body'], true, 512, JSON_THROW_ON_ERROR);
            self::assertSame('request-1', $body['variables']['requestKey']);
            self::assertSame('reverse_charge', $body['variables']['transfers'][0]['mode']);

            return new MockResponse('{"data":{"prepareTransferBatch":{"id":"","status":"MANUAL_ACTION_REQUIRED","fileName":"","contentBase64":"","message":"mBank firma"}}}');
        });
        $client = new PaymentClient($auth, $http, 'https://accounting.example/');
        $result = $client->prepareTransferBatch('company', 'account', 'request-1', '2026-12-01', [new TransferRequest('cost:id', 'fingerprint', 'PL61109010140000071219812874', 'reverse_charge')]);

        self::assertSame('MANUAL_ACTION_REQUIRED', $result->getStatus());
        self::assertSame('', $result->getContent());
    }

    public function testInvalidBase64PackageIsRejected(): void
    {
        $auth = $this->createMock(AuthService::class);
        $auth->method('getToken')->willReturn('test-token');
        $client = new PaymentClient($auth, new MockHttpClient(new MockResponse('{"data":{"transferBatch":{"contentBase64":"!invalid"}}}')));

        $this->expectException(RuntimeException::class);
        $client->transferBatch('company', 'batch');
    }
}
