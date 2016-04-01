<?php

namespace PHP_GCM;

class SenderTest extends \PHPUnit_Framework_TestCase {

  public function testConstructsCorrectly() {
    $sender = new Sender('api-key');

    $this->assertNotNull($sender);
  }
}
