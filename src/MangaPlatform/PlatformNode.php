<?php

namespace App\MangaPlatform;

use Closure;
use Symfony\Component\DomCrawler\Crawler;

class PlatformNode
{
    /** @var $selector string */
    private $selector;

    /** @var $text boolean */
    private $text;

    /** @var $attribute string */
    private $attribute;

    /** @var Closure $callback */
    private $callback;

    /** @var Closure $script */
    private $script;

    /** @var bool $init */
    private $init = false;

    /** @var string $name */
    private $name = '';

    public function __construct(string $name) {
        $this->name = $name;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function isInit(): bool
    {
        return $this->init;
    }

    /**
     * @return string
     */
    public function getSelector(): ?string
    {
        return $this->selector;
    }

    /**
     * @param string $selector
     * @return PlatformNode
     */
    public function setSelector(string $selector): PlatformNode
    {
        $this->init = true;
        $this->selector = $selector;
        return $this;
    }

    /**
     * @return string
     */
    public function getAttribute(): ?string
    {
        return $this->attribute;
    }

    /**
     * @param string $attribute
     * @return PlatformNode
     */
    public function setAttribute(string $attribute): PlatformNode
    {
        $this->init = true;
        $this->attribute = $attribute;
        return $this;
    }

    /**
     * @return bool
     */
    public function isText(): bool
    {
        return $this->text ?? false;
    }

    /**
     * @param bool $text
     * @return PlatformNode
     */
    public function setText(bool $text): PlatformNode
    {
        $this->init = true;
        $this->text = $text;
        return $this;
    }

    /**
     * @param Closure $callback
     * @return PlatformNode
     */
    public function setCallback(Closure $callback): PlatformNode
    {
        $this->init = true;
        $this->callback = $callback;
        return $this;
    }

    /**
     * @return bool
     */
    public function hasCallback(): bool
    {
        return isset($this->callback);
    }

    /**
     * @param Crawler $el
     * @param $cbParams
     * @return mixed
     */
    public function executeCallback(Crawler $el, $cbParams)
    {
        $cb = $this->callback;
        return $cb($el, $cbParams);
    }

    /**
     * @param Closure $script
     * @return PlatformNode
     */
    public function setScript(Closure $script): PlatformNode
    {
        $this->init = true;
        $this->script = $script;
        return $this;
    }

    /**
     * @return bool
     */
    public function hasScript(): bool
    {
        return isset($this->script);
    }

    /**
     * @param $client
     * @param $cbParams
     * @return mixed
     */
    public function executeScript($client, $cbParams) {
        $script = $this->script;
        return $script($client, $cbParams);
    }
}
