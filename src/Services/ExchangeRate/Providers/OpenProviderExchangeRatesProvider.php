<?php

namespace App\Services\ExchangeRate\Providers;

use Symfony\Component\HttpFoundation\Request;
use App\Services\ExchangeRate\DTO\ExchangeListDto;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class OpenProviderExchangeRatesProvider implements ExchangeRateProviderInterface
{
    protected string $apiUrl = 'https://openexchangerates.org/api';

    public function __construct(
        protected readonly string $appId,
        protected HttpClientInterface $httpClient
    ) {
    }

    /**
     * @param string $currencyISO
     *
     * @return ExchangeListDto|null
     * @throws \Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    public function getLatestExchangeRates(string $currencyISO): ?ExchangeListDto
    {
        $url = $this->apiUrl . '/latest.json?app_id=' . $this->appId . '&base=' . $currencyISO;

        /** @var ResponseInterface $result */
        $result = $this->httpClient->request(Request::METHOD_GET, $url);

        if ($result->getStatusCode() !== 200) {
            $data = $result->toArray(false);

            throw new \Exception($data['description'], $data['status']);
        }

        $data = $result->toArray();

        if (empty($data['rates'])) {
            return null;
        }

        return new ExchangeListDto(
            $data['timestamp'],
            $data['base'],
            $data['rates'],
        );
    }
}
