<?php

namespace PHP_GCM;

class SenderTest extends \PHPUnit_Framework_TestCase {

  public function testConstructsCorrectly() {
    $sender = new Sender('api-key');

    $this->assertNotNull($sender);
  }

  public function testAcceptsCertificatePath() {
    $sender = new Sender('api-key');
    $sender->setCertificatePath('/my/cert/path');

    $this->assertNotNull($sender);
  }
}
