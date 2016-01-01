<?php

namespace PHP_GCM;

class InvalidRequestExceptionTest extends \PHPUnit_Framework_TestCase {

  public function testConstructsCorrectlyWithStatusCode() {
    $exception = new InvalidRequestException(422);

    $this->assertEquals(422, $exception->getHttpStatusCode());
    $this->assertEquals('', $exception->getDescription());
    $this->assertEquals('PHP_GCM\InvalidRequestException: HTTP Status Code: 422 ()', (string) $exception);
  }

  public function testConstructsCorrectlyWithDescription() {
    $exception = new InvalidRequestException(422, 'there was an error');

    $this->assertEquals(422, $exception->getHttpStatusCode());
    $this->assertEquals('there was an error', $exception->getDescription());
    $this->assertEquals('PHP_GCM\InvalidRequestException: HTTP Status Code: 422 (there was an error)', (string) $exception);
  }
}
