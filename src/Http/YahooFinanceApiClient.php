<?php

namespace App\Http;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

class YahooFinanceApiClient  implements FinanceApiClientInterface
{

    /**
     * @var HttpClientInterface
     */
    private $httpClient;

    private const URL = 'https://yh-finance.p.rapidapi.com/stock/v2/get-profile';
    private const X_RAPID_API_HOST = 'yh-finance.p.rapidapi.com';
    private $rapidApiKey;

    public function __construct(HttpClientInterface $httpClient, $rapidApiKey)
    {
        $this->httpClient = $httpClient;
        $this->rapidApiKey = $rapidApiKey;
    }

    public function fetchStockProfile($symbol, $region): JsonResponse
    {
        $response = $this->httpClient->request('GET', self::URL, [
            'query'   => [
                'symbol' => $symbol,
                'region' => $region
            ],
            'headers' => [
                'x-rapidapi-host' => self::X_RAPID_API_HOST,
                'x-rapidapi-key'  => $this->rapidApiKey
            ]
        ]);

        // @todo handle non 200 responses
        if ($response->getStatusCode() !== 200) {
            return new JsonResponse('Finance API Client Error ', 400);
        }

        $stockProfile = json_decode($response->getContent())->price;

        $stockProfileAsArray = [
            'symbol'        => $stockProfile->symbol,
            'shortName'     => $stockProfile->shortName,
            'region'        => $region,
            'exchangeName'  => $stockProfile->exchangeName,
            'currency'      => $stockProfile->currency,
            'price'         => $stockProfile->regularMarketPrice->raw,
            'previousClose' => $stockProfile->regularMarketPreviousClose->raw,
            'priceChange'   => $stockProfile->regularMarketPrice->raw - $stockProfile->regularMarketPreviousClose->raw
        ];

        return new JsonResponse($stockProfileAsArray, 200);
    }
}
