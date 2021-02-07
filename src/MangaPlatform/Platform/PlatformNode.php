<?php

namespace App\MangaPlatform\Platform;

use Closure;
use Exception;

class PlatformNode
{
    /** @var $selector string */
    protected $selector;

    /** @var $text boolean */
    protected $text;

    /** @var $attribute string */
    protected $attribute;

    /** @var Closure $callback */
    protected $callback;

    /** @var Closure $script */
    protected $script;

    /**
     * @return string
     */
    public function getSelector(): string
    {
        return $this->selector;
    }

    /**
     * @param string $selector
     * @return PlatformNode
     */
    public function setSelector(string $selector): PlatformNode
    {
        $this->selector = $selector;
        return $this;
    }

    /**
     * @return string
     */
    public function getAttribute(): string
    {
        return $this->attribute;
    }

    /**
     * @param string $attribute
     * @return PlatformNode
     */
    public function setAttribute(string $attribute): PlatformNode
    {
        $this->attribute = $attribute;
        return $this;
    }

    /**
     * @return bool
     */
    public function isText(): bool
    {
        return $this->text;
    }

    /**
     * @param bool $text
     * @return PlatformNode
     */
    public function setText(bool $text): PlatformNode
    {
        $this->text = $text;
        return $this;
    }

    /**
     * @param Closure $callback
     * @return PlatformNode
     */
    public function setCallback(Closure $callback): PlatformNode
    {
        $this->callback = $callback;
        return $this;
    }

    /**
     * @return mixed
     */
    public function executeCallback()
    {
        $cb = $this->callback;
        return $cb();
    }

    /**
     * @param Closure $script
     * @return PlatformNode
     */
    public function setScript(Closure $script): PlatformNode
    {
        $this->script = $script;
        return $this;
    }

    /**
     * @return mixed
     */
    public function executeScript() {
        $script = $this->script;
        return $script();
    }
}
