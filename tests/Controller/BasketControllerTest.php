<?php

namespace App\Tests\Controller;

use Helmich\JsonAssert\JsonAssertions;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class BasketControllerTest extends WebTestCase
{
    use JsonAssertions;

    protected $client;

    public function setUp(): void
    {
        $this->client = static::createClient();
    }

    public function testGetCalcCostSuccessResponse()
    {
        $requestData = $this->getRequestData();
        $this->client->jsonRequest('POST', '/api/v1/basket/calc-cost', $requestData);

        $responseContent = json_decode($this->client->getResponse()->getContent(), null, 512, JSON_THROW_ON_ERROR);

        $this->assertResponseIsSuccessful();
        $this->assertJsonDocumentMatchesSchema($responseContent, [
            'type' => 'object',
            'required' => ['checkoutPrice', 'checkoutCurrency'],
            'properties' => [
                'checkoutPrice' => ['type' => 'number'],
                'checkoutCurrency' => ['type' => 'string'],
            ],
        ]);
        $this->assertEquals('USD', $responseContent->checkoutCurrency);
    }

    public function testGetCalcCostFailCurrencyValidation()
    {
        $requestData = $this->getRequestData();
        $requestData['items']['0']['currency'] = 'RNL';

        $this->client->jsonRequest('POST', '/api/v1/basket/calc-cost', $requestData);

        $responseContent = json_decode($this->client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);

        $this->assertResponseStatusCodeSame(400);
        $this->assertJsonDocumentMatchesSchema($responseContent, [
            'type' => 'object',
            'required' => ['error', 'code', 'messages'],
            'properties' => [
                'error' => ['type' => 'boolean'],
                'code' => ['type' => 'number'],
                'messages' => ['type' => 'object'],
            ],
        ]);
        $this->assertEquals(true, $responseContent['error']);
        $this->assertTrue(!empty($responseContent['messages']['items.0.currency']));
    }

    protected function getRequestData()
    {
        return [
            'items' => [
                [
                    'currency' => 'EUR',
                    'price' => 12.39,
                    'quantity' => 1,
                ]
            ],
            'checkoutCurrency' => 'USD',
        ];
    }
}
