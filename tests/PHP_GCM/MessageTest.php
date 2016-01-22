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

  public function testAddsData() {
    $message = new Message();

    $message->addData('key1', 'value1');

    $this->assertEquals(array('key1' => 'value1'), $message->getData());
  }

  public function testAddsDataWhenDataAlreadyExists() {
    $message = new Message();
    $message->data(array('key1' => 'value1', 'key2' => 'value2'));

    $message->addData('key3', 'value3');

    $this->assertEquals(array('key1' => 'value1', 'key2' => 'value2', 'key3' => 'value3'), $message->getData());
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

  public function testBuildAcceptsSingleRecipient() {
    $message = new Message();

    $builtMessage = json_decode($message->build('recipient'), true);

    $this->assertEquals('recipient', $builtMessage[Message::TO]);
  }

  public function testBuildAcceptsArrayWithSingleRecipient() {
    $message = new Message();

    $builtMessage = json_decode($message->build(array('recipient')), true);

    $this->assertEquals('recipient', $builtMessage[Message::TO]);
  }

  public function testBuildAcceptsArrayWithMultipleRecipients() {
    $message = new Message();

    $builtMessage = json_decode($message->build(array('recipient', 'other-recipient')), true);

    $this->assertEquals(2, count($builtMessage[Message::REGISTRATION_IDS]));
  }

  public function testBuildDoesNotSetCollapseKeyWhenAbsent() {
    $message = new Message();

    $builtMessage = json_decode($message->build(array('recipient')), true);

    $this->assertFalse(array_key_exists(Message::COLLAPSE_KEY, $builtMessage));
  }

  public function testBuildSetsCollapseKey() {
    $message = new Message();
    $message->collapseKey('collapse-key');

    $builtMessage = json_decode($message->build(array('recipient')), true);

    $this->assertEquals('collapse-key', $builtMessage[Message::COLLAPSE_KEY]);
  }

  public function testBuildSetsDelayWhileIdle() {
    $message = new Message();
    $message->delayWhileIdle(true);

    $builtMessage = json_decode($message->build(array('recipient')), true);

    $this->assertTrue($builtMessage[Message::DELAY_WHILE_IDLE]);
  }

  public function testBuildSetsTimeToLive() {
    $message = new Message();
    $message->timeToLive(100);

    $builtMessage = json_decode($message->build(array('recipient')), true);

    $this->assertEquals(100, $builtMessage[Message::TIME_TO_LIVE]);
  }

  public function testBuildSetsDryRun() {
    $message = new Message();
    $message->dryRun(true);

    $builtMessage = json_decode($message->build(array('recipient')), true);

    $this->assertTrue($builtMessage[Message::DRY_RUN]);
  }

  public function testBuildDoesNotSetRestrictedPackageNameWhenAbsent() {
    $message = new Message();

    $builtMessage = json_decode($message->build(array('recipient')), true);

    $this->assertFalse(array_key_exists(Message::RESTRICTED_PACKAGE_NAME, $builtMessage));
  }

  public function testBuildSetsRestrictedPackageName() {
    $message = new Message();
    $message->restrictedPackageName('package-name');

    $builtMessage = json_decode($message->build(array('recipient')), true);

    $this->assertEquals('package-name', $builtMessage[Message::RESTRICTED_PACKAGE_NAME]);
  }

  public function testBuildDoesNotSetDataWhenDataIsAbsent() {
    $message = new Message();

    $builtMessage = json_decode($message->build(array('recipient')), true);

    $this->assertFalse(array_key_exists(Message::DATA, $builtMessage));
  }

  public function testBuildDoesNotSetDataWhenDataIsEmpty() {
    $message = new Message();
    $message->data(array());

    $builtMessage = json_decode($message->build(array('recipient')), true);

    $this->assertFalse(array_key_exists(Message::DATA, $builtMessage));
  }

  public function testBuildSetsData() {
    $message = new Message();
    $message->data(array('key' => 'value'));

    $builtMessage = json_decode($message->build(array('recipient')), true);

    $this->assertEquals('value', $builtMessage[Message::DATA]['key']);
  }

  public function testBuildHandlesMultiDimentionalArraysForData() {
    $message = new Message();
    $message->data(array('foo' => array('bar' => 'baz')));

    $builtMessage = json_decode($message->build(array('recipient')), true);

    $this->assertEquals('baz', $builtMessage[Message::DATA]['foo']['bar']);
  }
}
