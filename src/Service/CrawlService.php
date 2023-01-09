<?php

namespace App\Service;

use App\Entity\Platform;
use App\MangaPlatform\PlatformNode;
use Facebook\WebDriver\Exception\WebDriverCurlException;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Panther\Client;
use Symfony\Component\Panther\DomCrawler\Crawler;

class CrawlService
{
    /** @var Client $client */
    private $client;

    /** @var LoggerInterface $logger */
    private $logger;

    private $clientOptions = [];

    private $clientArgs = [];

    public function __construct(
        LoggerInterface $logger
    ) {
        $this->clientArgs = [
            "--headless",
            "--disable-gpu",
            "--no-sandbox",
            "--disable-dev-shm-usage",
            '--user-agent=Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/100.0.4896.88 Safari/537.36', // Avoir obligatoirement un user agent !!!
        ];

//        $chromeOptions = new ChromeOptions();
//        $chromeOptions->setExperimentalOption('w3c', false);

        $this->clientOptions = [
            'port' => mt_rand(9500, 9600),
            'connection_timeout_in_ms' => 60000,
            'request_timeout_in_ms' => 60000,
//            'capabilities' => [
//                ChromeOptions::CAPABILITY => $chromeOptions
//            ]
        ];

        $this->logger = $logger;
    }

    public function initClient(): void
    {
        $this->client = Client::createChromeClient(null, $this->clientArgs, $this->clientOptions);
    }

    public function openUrl(string $url): void
    {
        if (!$this->client) {
            $this->initClient();
        }

        if ($this->client instanceof Client && $this->client->getCurrentURL() !== $url) {
            $this->logger->info("[URL] opening $url");
            $this->client->request('GET', $url);
            $this->logger->info("[URL] $url opened.");
        }
    }

    public function findNode(
        PlatformNode $platformNode,
        array $callbackParameters = []
    ) {
        $returnValue = null;
        $nodeName = $platformNode->getName();
        if (!empty($selector = $platformNode->getSelector())) {
            $this->logger->debug("[CRAWL]: " . $nodeName . ' - selector: ' . $selector);
            $crawler = $this->client->getCrawler();

//            $this->client->waitFor($selector);
            $node = $crawler->filter($selector);

            if ($platformNode->hasCallback() === true) {
                try {
                    $returnValue = $platformNode->executeCallback($node, $callbackParameters);
                } catch (\Exception $e) {
                    $this->logger->error($e->getMessage(), [
                        'line' => $e->getLine(),
                        'file' => $e->getFile(),
                        'node' => $nodeName,
                        'parameters' => $callbackParameters
                    ]);
                }
            }

            if ($platformNode->isText() === true) {
                try {
                    $returnValue = $node->getText();
                } catch (\Exception $e) {
                    $this->logger->error($e->getMessage(), ['node' => $nodeName]);
                }
            }

            if (!empty($attribute = $platformNode->getAttribute())) {
                $returnValue = $node->getAttribute($attribute);
            }
        }

        if ($platformNode->hasScript()) {
            $this->logger->debug("[CRAWL]: " . $nodeName . ' - script');
            $returnValue = $platformNode->executeScript($this->client, $callbackParameters);
        }

        $this->logger->debug("[$nodeName]: " . (is_array($returnValue) ? count($returnValue) . ' values' : $returnValue) );

        return $returnValue;
    }

    public function closeClient() {
        $this->client->quit();
    }
}