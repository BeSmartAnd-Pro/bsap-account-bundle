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
    protected const string DEV_AUTH  = 'http://ksiegowosc.dev.besmartand.pro/api/login_check';
    protected const string PROD_AUTH = 'https://ksiegowosc.besmartand.pro/api/login_check';

    protected const string CACHE_TOKEN_KEY = 'besmartandpro_ksiegowosc_token';

    public function __construct(
        protected readonly CacheInterface $cache,
        protected readonly HttpClientInterface $httpClient,
        protected readonly string $mode,
        protected readonly string $username,
        protected readonly string $password
    ) {
    }

    protected function getEndpoint(): string
    {
        return $this->mode === 'production' ? self::PROD_AUTH : self::DEV_AUTH;
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

            $date  = null;

            if ($date === null) {
                $date = (new DateTime())->modify('+59 minutes');
            }

            $item->expiresAt($date);

            return $data['token'];
        });
    }
}
