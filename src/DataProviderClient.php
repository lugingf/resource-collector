<?php
declare(strict_types=1);

namespace RMS\ResourceCollector;

use GuzzleHttp\Client;
use Psr\Log\LoggerInterface;

class DataProviderClient
{
    private $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function getResources(string $targetUrl)
    {
        $response = $this->sendRequest($targetUrl);
        return \GuzzleHttp\json_decode($response, true);
    }

    private function sendRequest(string $url): string
    {
        try {
            $client = new Client(
                ['base_uri' => $url, 'timeout' => 60]
            );
            $response = $client->request('get');
        } catch (\Throwable $e) {
            $errorText = "Resource collecting failed from {$url}\n" . $e->getMessage();
            $this->logger->log('error', $errorText);
            throw new \Exception($errorText);
        }

        return !is_null($response) ? $response->getBody()->getContents() : '';
    }
}