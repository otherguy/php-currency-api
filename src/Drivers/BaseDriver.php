<?php namespace Otherguy\Currency\Drivers;

/**
 * Class BaseDriver
 *
 * @package Otherguy\Currency\Drivers
 */
abstract class BaseDriver
{
    protected $symbols      = [];
    protected $baseCurrency = 'USD';
    protected $protocol     = 'http';
    protected $accessKey    = null;

    /**
     * @param string $baseCurrency
     *
     * @return self
     */
    public function setBase(string $baseCurrency): DriverInterface
    {
        $this->baseCurrency = $baseCurrency;

        return $this;
    }

    /**
     * @param array $symbols
     *
     * @return self
     */
    public function setSymbols(array $symbols = []): DriverInterface
    {
        $this->symbols = (array)$symbols;
        return $this;
    }

    /**
     * @return array
     */
    public function getSymbols(): array
    {
        return $this->symbols;
    }

    /**
     * @return string
     */
    public function getBaseCurrency(): string
    {
        return $this->baseCurrency;
    }

    /**
     * @return self
     */
    public function secure(): DriverInterface
    {
        $this->protocol = 'https';

        return $this;
    }

    /**
     * @return string
     */
    public function getProtocol(): string
    {
        return $this->protocol;
    }

    /**
     * Sets the API key to use.
     *
     * @param string $accessKey Your API key
     *
     * @return self
     */
    public function setAccessKey(string $accessKey): DriverInterface
    {
        $this->accessKey = $accessKey;
        return $this;
    }
}