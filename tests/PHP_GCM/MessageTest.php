<?php

namespace PHP_GCM;

class MessageTest extends \PHPUnit_Framework_TestCase {

  public function testAllConstructorParametersAreOptional() {
    $message = new Message();

    $this->assertEquals('', $message->getCollapseKey());
    $this->assertTrue(is_array($message->getData()) && count($message->getData()) == 0);
    $this->assertEquals(-1, $message->getTimeToLive());
    $this->assertEquals('', $message->getDelayWhileIdle());
    $this->assertEquals('', $message->getRestrictedPackageName());
    $this->assertFalse($message->getDryRun());
  }

  public function testSetsCollapseKeyCorrectly() {
    $message = new Message();

    $message->collapseKey('collapse_key');

    $this->assertEquals('collapse_key', $message->getCollapseKey());
  }

  public function testSetsDelayWhileIdleCorrectly() {
    $message = new Message();

    $message->delayWhileIdle(true);

    $this->assertTrue($message->getDelayWhileIdle());
  }

  public function testSetsDryRunCorrectly() {
    $message = new Message();

    $message->dryRun(true);

    $this->assertTrue($message->getDryRun());
  }

  public function testSetsTimeToLiveCorrectly() {
    $message = new Message();

    $message->timeToLive(100);

    $this->assertEquals(100, $message->getTimeToLive());
  }

  public function testSetsDataCorrectly() {
    $message = new Message();

    $message->data(array('key1' => 'value1', 'key2' => 'value2'));

    $this->assertEquals(array('key1' => 'value1', 'key2' => 'value2'), $message->getData());
  }

  public function testSetsRestrictedPackageNameCorrectly() {
    $message = new Message();

    $message->restrictedPackageName('com.lukekorth.android');

    $this->assertEquals('com.lukekorth.android', $message->getRestrictedPackageName());
  }
}
