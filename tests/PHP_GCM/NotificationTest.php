<?php

namespace PHP_GCM;

class NotificationTest extends \PHPUnit_Framework_TestCase {

  protected $notification;

  protected function setUp() {
    $this->notification = new Notification();
  }

  public function testConstructsCorrectly() {
    $this->assertEquals('default', $this->notification->getSound());
  }

  public function testSetsIcon() {
    $this->notification->icon('icon');

    $this->assertEquals('icon', $this->notification->getIcon());
  }

  public function testSetsSound() {
    $this->notification->sound('sound');

    $this->assertEquals('sound', $this->notification->getSound());
  }

  public function testSetsTitle() {
    $this->notification->title('title');

    $this->assertEquals('title', $this->notification->getTitle());
  }

  public function testSetsBody() {
    $this->notification->body('body');

    $this->assertEquals('body', $this->notification->getBody());
  }

  public function testSetsBadge() {
    $this->notification->badge(1);

    $this->assertEquals(1, $this->notification->getBadge());
  }

  public function testSetsTag() {
    $this->notification->tag('tag');

    $this->assertEquals('tag', $this->notification->getTag());
  }

  public function testSetsColor() {
    $this->notification->color('color');

    $this->assertEquals('color', $this->notification->getColor());
  }

  public function testSetsClickAction() {
    $this->notification->clickAction('click-action');

    $this->assertEquals('click-action', $this->notification->getClickAction());
  }

  public function testSetsBodyLocKey() {
    $this->notification->bodyLocKey('bodyLocKey');

    $this->assertEquals('bodyLocKey', $this->notification->getBodyLocKey());
  }

  public function testSetsBodyLocArgs() {
    $this->notification->bodyLocArgs(array('key' => 'value'));

    $this->assertEquals(array('key' => 'value'), $this->notification->getBodyLocArgs());
  }

  public function testSetsTitleLocKey() {
    $this->notification->titleLocKey('titleLocKey');

    $this->assertEquals('titleLocKey', $this->notification->getTitleLocKey());
  }

  public function testSetsTitleLocArgs() {
    $this->notification->titleLocArgs(array('key' => 'value'));

    $this->assertEquals(array('key' => 'value'), $this->notification->getTitleLocArgs());
  }

  public function testSettersAreChainable() {
    $this->notification->icon('icon')
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

    $this->assertEquals('icon', $this->notification->getIcon());
    $this->assertEquals('sound', $this->notification->getSound());
    $this->assertEquals('title', $this->notification->getTitle());
    $this->assertEquals('body', $this->notification->getBody());
    $this->assertEquals(1, $this->notification->getBadge());
    $this->assertEquals('tag', $this->notification->getTag());
    $this->assertEquals('color', $this->notification->getColor());
    $this->assertEquals('click-action', $this->notification->getClickAction());
    $this->assertEquals('bodyLocKey', $this->notification->getBodyLocKey());
    $this->assertEquals(array('key' => 'value'), $this->notification->getBodyLocArgs());
    $this->assertEquals('titleLocKey', $this->notification->getTitleLocKey());
    $this->assertEquals(array('key' => 'value'), $this->notification->getTitleLocArgs());
  }
}
