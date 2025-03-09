<?php

namespace App\Service;

use App\Exception\NodeEmptyException;
use App\MangaPlatform\PlatformNode;
use Facebook\WebDriver\Exception\NoSuchElementException;
use Facebook\WebDriver\Exception\TimeoutException;
use Facebook\WebDriver\Exception\WebDriverCurlException;
use Psr\Log\LoggerInterface;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\Panther\Client;

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
            '--user-agent=Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/131.0.0.0 Safari/537.36', // Avoir obligatoirement un user agent !!!
        ];

//        $chromeOptions = new ChromeOptions();
//        $chromeOptions->setExperimentalOption('w3c', false);

        $this->clientOptions = [
            'port' => mt_rand(9500, 9600),
            'connection_timeout_in_ms' => 60000,
            'request_timeout_in_ms' => 60000,
//            'chromedriver_arguments' => [
//                '--log-path=myfile.log',
//                '--log-level=DEBUG'
//            ]
//            'capabilities' => [
//                ChromeOptions::CAPABILITY => $chromeOptions
//            ]
        ];

        $this->logger = $logger;
    }

    public function initClient(array $options = []): void
    {
        if (!$this->client) {
            $this->client = Client::createChromeClient(null, $this->clientArgs, $this->clientOptions);
        }

        $this->client->request('GET', $options['baseUrl']);
        foreach ($options['cookies'] ?? [] as $cookie) {
            $this->client->getCookieJar()->set(new Cookie($cookie['name'], $cookie['value'], strtotime('+1 day'), '/', $options['domain'], false, false));
        }
    }

    public function openUrl(string $url, array $options = []): void
    {
        $this->initClient($options);

        if ($this->client instanceof Client && $this->client->getCurrentURL() !== $url) {
            $this->logger->info("[URL] opening $url");
            $this->client->request('GET', $url);
            $this->logger->info("[URL] $url opened.");

            if ($this->client->getCurrentURL() !== $url) {
                throw new \Exception("Wrong url opened. Expected: $url, got: " . $this->client->getCurrentURL());
            }
        }
    }

    public function getClient() {
        return $this->client;
    }

    /**
     * @throws NoSuchElementException
     * @throws TimeoutException
     */
    public function findNode(
        PlatformNode $platformNode,
        array $callbackParameters = []
    ) {
        $returnValue = null;
        $nodeName = $platformNode->getName();
        $selector = $platformNode->getSelector();
        $XPathSelector = $platformNode->getXPathSelector();
        if ($selector || $XPathSelector) {
            $this->logger->debug("[CRAWL] " . $nodeName . ' - selector: ' . ($XPathSelector ?? $selector));
            $crawler = $this->client->getCrawler();

            if ($platformNode->getMustWait() === true) {
                $this->client->waitFor($XPathSelector ?? $selector);
            }

            if ($XPathSelector !== null) {
                $node = $crawler->filterXPath($XPathSelector);
            } else {
                $node = $crawler->filter($selector);
            }


            if ($platformNode->hasCallback() === true) {
                $this->logger->info($selector);

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
                } catch (\InvalidArgumentException $invalidArgumentException) {
                    throw new NodeEmptyException($invalidArgumentException->getMessage());
                } catch (\Exception $exception) {
                    $this->logger->error($exception->getMessage(), ['node' => $nodeName]);
                }
            }

            if (!empty($attribute = $platformNode->getAttribute())) {
                $returnValue = $node->getAttribute($attribute);
            }
        }

        if ($platformNode->hasScript()) {
            $this->logger->debug('[CRAWL]: ' . $nodeName . ' - script');
            $returnValue = $platformNode->executeScript($this->client, $callbackParameters);
        }

        $this->logger->debug("[$nodeName]: " . (is_array($returnValue) ? count($returnValue) . ' values' : $returnValue) );

        return $returnValue;
    }

    public function closeClient() {
        $this->client->quit();
    }
}