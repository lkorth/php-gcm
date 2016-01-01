<?php

namespace PHP_GCM;

class MessageTest extends \PHPUnit_Framework_TestCase {

  public function testAllConstructorParametersAreOptional() {
    $message = new Message();

    $this->assertEquals('', $message->getCollapseKey());
    $this->assertTrue(is_array($message->getData()) && empty($message->getData()));
    $this->assertEquals(2419200, $message->getTimeToLive());
    $this->assertEquals(false, $message->getDelayWhileIdle());
    $this->assertEquals('', $message->getRestrictedPackageName());
    $this->assertFalse($message->getDryRun());
  }

  public function testSetsCollapseKey() {
    $message = new Message();

    $message->collapseKey('collapse_key');

    $this->assertEquals('collapse_key', $message->getCollapseKey());
  }

  public function testSetsDelayWhileIdle() {
    $message = new Message();

    $message->delayWhileIdle(true);

    $this->assertTrue($message->getDelayWhileIdle());
  }

  public function testSetsDryRun() {
    $message = new Message();

    $message->dryRun(true);

    $this->assertTrue($message->getDryRun());
  }

  public function testSetsTimeToLive() {
    $message = new Message();

    $message->timeToLive(100);

    $this->assertEquals(100, $message->getTimeToLive());
  }

  public function testSetsData() {
    $message = new Message();

    $message->data(array('key1' => 'value1', 'key2' => 'value2'));

    $this->assertEquals(array('key1' => 'value1', 'key2' => 'value2'), $message->getData());
  }

  public function testSetsRestrictedPackageName() {
    $message = new Message();

    $message->restrictedPackageName('com.lukekorth.android');

    $this->assertEquals('com.lukekorth.android', $message->getRestrictedPackageName());
  }
}
