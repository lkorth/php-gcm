<?php

namespace PHP_GCM;

class ResultTest extends \PHPUnit_Framework_TestCase {

  public function testConstructsCorrectly() {
    $result = new Result();

    $this->assertEquals('', $result->getMessageId());
    $this->assertEquals('', $result->getCanonicalRegistrationId());
    $this->assertEquals('', $result->getErrorCode());
  }

  public function testSetsMessageId() {
    $result = new Result();

    $result->setMessageId('message-id');

    $this->assertEquals('message-id', $result->getMessageId());
  }

  public function testSetsCanonicalRegistrationId() {
    $result = new Result();

    $result->setCanonicalRegistrationId('canonical-registration-id');

    $this->assertEquals('canonical-registration-id', $result->getCanonicalRegistrationId());
  }

  public function testSetsErrorCode() {
    $result = new Result();

    $result->setErrorCode(422);

    $this->assertEquals(422, $result->getErrorCode());
  }
}
