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

  public function testSetsNotification() {
    $message = new Message();
    $notification = new Notification();

    $message->notification($notification);

    $this->assertEquals($notification, $message->getNotification());
  }

  public function testSetsContentAvailable() {
    $message = new Message();

    $message->contentAvailable(true);

    $this->assertTrue($message->getContentAvailable());
  }

  public function testSetsPriority() {
    $message = new Message();

    $message->priority("high");

    $this->assertEquals("high", $message->getPriority());
  }

  public function testSettersAreChainable() {
    $message = new Message();
    $message->collapseKey('collapse-key')
      ->delayWhileIdle(true)
      ->dryRun(true)
      ->timeToLive(100)
      ->data(array('key1' => 'value1'))
      ->addData('key2', 'value2')
      ->restrictedPackageName('com.lukekorth.android')
      ->contentAvailable(true)
      ->priority("high");

    $this->assertEquals('collapse-key', $message->getCollapseKey());
    $this->assertEquals(true, $message->getDelayWhileIdle());
    $this->assertEquals(true, $message->getDryRun());
    $this->assertEquals(100, $message->getTimeToLive());
    $this->assertEquals(array('key1' => 'value1', 'key2' => 'value2'), $message->getData());
    $this->assertEquals('com.lukekorth.android', $message->getRestrictedPackageName());
    $this->assertTrue($message->getContentAvailable());
    $this->assertEquals("high", $message->getPriority());
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

  public function testBuildSetsContentAvailableWhenTrue() {
    $message = new Message();
    $message->contentAvailable(true);

    $builtMessage = json_decode($message->build(array('recipient')), true);

    $this->assertTrue($builtMessage[Message::CONTENT_AVAILABLE]);
  }

  public function testBuildSetsContentAvailableWhenFalse() {
    $message = new Message();
    $message->contentAvailable(false);

    $builtMessage = json_decode($message->build(array('recipient')), true);

    $this->assertFalse($builtMessage[Message::CONTENT_AVAILABLE]);
  }

  public function testBuildDoesNotSetContentAvailableWhenAbsent() {
    $message = new Message();

    $builtMessage = json_decode($message->build(array('recipient')), true);

    $this->assertFalse(array_key_exists(Message::CONTENT_AVAILABLE, $builtMessage));
  }

  public function testBuildSetsPriority() {
    $message = new Message();
    $message->priority("high");

    $builtMessage = json_decode($message->build(array('recipient')), true);

    $this->assertEquals("high", $builtMessage[Message::PRIORITY]);
  }

  public function testBuildDoesNotSetPriorityWhenAbsent() {
    $message = new Message();

    $builtMessage = json_decode($message->build(array('recipient')), true);

    $this->assertFalse(array_key_exists(Message::PRIORITY, $builtMessage));
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

  public function testBuildDoesNotIncludeNotificationWhenNotSet() {
    $message = new Message();

    $builtMessage = json_decode($message->build('recipient'), true);

    $this->assertFalse(array_key_exists(Message::NOTIFICATION, $builtMessage));
  }

  public function testBuildIncludesNotificationWhenSet() {
    $message = new Message();
    $message->notification(new Notification());

    $builtMessage = json_decode($message->build('recipient'), true);

    $this->assertTrue(array_key_exists(Message::NOTIFICATION, $builtMessage));
  }

  public function testBuildSetsNotification() {
    $notification = new Notification();
    $notification->icon('icon')
      ->sound('sound')
      ->title('title')
      ->body('body')
      ->badge(1)
      ->tag('tag')
      ->color('color')
      ->clickAction('click-action')
      ->bodyLocKey('bodyLocKey')
      ->bodyLocArgs(array('key' => 'value'))
      ->titleLocKey('titleLocKey')
      ->titleLocArgs(array('key' => 'value'));
    $message = new Message();
    $message->notification($notification);

    $builtMessage = json_decode($message->build('recipient'), true);
    $builtNotification = $builtMessage[Message::NOTIFICATION];

    $this->assertEquals('icon', $builtNotification[Message::NOTIFICATION_ICON]);
    $this->assertEquals('sound', $builtNotification[Message::NOTIFICATION_SOUND]);
    $this->assertEquals('title', $builtNotification[Message::NOTIFICATION_TITLE]);
    $this->assertEquals('body', $builtNotification[Message::NOTIFICATION_BODY]);
    $this->assertEquals(1, $builtNotification[Message::NOTIFICATION_BADGE]);
    $this->assertEquals('tag', $builtNotification[Message::NOTIFICATION_TAG]);
    $this->assertEquals('color', $builtNotification[Message::NOTIFICATION_COLOR]);
    $this->assertEquals('click-action', $builtNotification[Message::NOTIFICATION_CLICK_ACTION]);
    $this->assertEquals('bodyLocKey', $builtNotification[Message::NOTIFICATION_BODY_LOC_KEY]);
    $this->assertEquals(array('key' => 'value'), $builtNotification[Message::NOTIFICATION_BODY_LOC_ARGS]);
    $this->assertEquals('titleLocKey', $builtNotification[Message::NOTIFICATION_TITLE_LOC_KEY]);
    $this->assertEquals(array('key' => 'value'), $builtNotification[Message::NOTIFICATION_TITLE_LOC_ARGS]);
  }
}
