<?php namespace Otherguy\Currency\Middleware;

use GuzzleHttp\Psr7\Response;
use function json_decode;

/**
 * Class JsonAwareResponse
 *
 * @package Otherguy\Currency\Middleware
 */
class JsonAwareResponse extends Response
{
  public function getBody()
  {
    // Get parent Body stream
    $body = parent::getBody();

    // If JSON HTTP header detected then decode
    if (false !== strpos($this->getHeaderLine('Content-Type'), 'application/json')) {
      return json_decode($body, true);
    }

    return $body;
  }
}
