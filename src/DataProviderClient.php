<?php
declare(strict_types=1);

namespace RMS\ResourceCollector;

use GuzzleHttp\Client;
use Psr\Log\LoggerInterface;
use TutuRu\Metrics\StatsdExporterClientInterface;

class DataProviderClient
{
    private $logger;
    private $statsd;

    public function __construct(LoggerInterface $logger, StatsdExporterClientInterface $statsd)
    {
        $this->logger = $logger;
        $this->statsd = $statsd;
    }

    public function getResources(string $targetUrl)
    {
        $response = $this->sendRequest($targetUrl);
        return json_decode($response, true);
    }

    private function sendRequest(string $url): string
    {
        $startTime = microtime(true);
        try {
            $client = new Client(
                ['base_uri' => $url, 'timeout' => 60]
            );
            $response = $client->request('get');
        } catch (\Throwable $e) {
            $errorText = "Resource collecting failed from {$url}\n" . $e;
            $this->logger->log('error', $errorText);
            throw new \Exception($errorText);
        }

        $this->statsd->timing(
            "resource_collector_data_provider_request_duration",
            microtime(true) - $startTime,
            ["provider_url" => $url]
            );

        return !is_null($response) ? $response->getBody()->getContents() : '';
    }
}