<?php

declare(strict_types=1);

namespace BeSmartAndPro\BsapAccountBundle\Auth;

use DateTime;
use Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class AuthService
{
    protected const string DEFAULT_AUTH_ENDPOINT = 'https://ksiegowosc.besmartand.pro/api/login_check';

    protected const string CACHE_TOKEN_KEY = 'besmartandpro_ksiegowosc_token';

    public function __construct(
        protected readonly CacheInterface $cache,
        protected readonly HttpClientInterface $httpClient,
        protected readonly string $username,
        protected readonly string $password,
        protected ?string $alternativeHost = null,
    ) {
    }

    protected function getEndpoint(): string
    {
        if ($this->alternativeHost) {
            return $this->alternativeHost . '/api/login_check';
        }

        return self::DEFAULT_AUTH_ENDPOINT;
    }

    public function getToken(): string
    {
        return $this->cache->get(self::CACHE_TOKEN_KEY, function (ItemInterface $item) {
            $response = $this->httpClient->request(Request::METHOD_POST, $this->getEndpoint(), [
                'json' => [
                    'username' => $this->username,
                    'password' => $this->password,
                ],
            ]);

            $data = $response->toArray();

            if (!isset($data['token']) || trim($data['token']) === '') {
                throw new Exception('Security problem, can`t fetch token');
            }

            $item->expiresAt((new DateTime())->modify('+59 minutes'));

            return $data['token'];
        });
    }
}
