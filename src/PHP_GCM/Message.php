<?php

namespace PHP_GCM;

class Message {

  const COLLAPSE_KEY = 'collapse_key';
  const TIME_TO_LIVE = 'time_to_live';
  const DRY_RUN = 'dry_run';
  const DELAY_WHILE_IDLE = 'delay_while_idle';
  const RESTRICTED_PACKAGE_NAME = 'restricted_package_name';
  const REGISTRATION_IDS = 'registration_ids';
  const DATA = 'data';
  const TO = 'to';
  const NOTIFICATION = 'notification';
  const NOTIFICATION_TITLE = 'title';
  const NOTIFICATION_BODY = 'body';
  const NOTIFICATION_ICON = 'icon';
  const NOTIFICATION_SOUND = 'sound';
  const NOTIFICATION_BADGE = 'badge';
  const NOTIFICATION_TAG = 'tag';
  const NOTIFICATION_COLOR = 'color';
  const NOTIFICATION_CLICK_ACTION = 'click_action';
  const NOTIFICATION_BODY_LOC_KEY = 'body_loc_key';
  const NOTIFICATION_BODY_LOC_ARGS = 'body_loc_args';
  const NOTIFICATION_TITLE_LOC_KEY = 'title_loc_key';
  const NOTIFICATION_TITLE_LOC_ARGS = 'title_loc_args';
  const CONTENT_AVAILABLE = 'content_available';
  const PRIORITY = 'priority';

  private $collapseKey;
  private $delayWhileIdle;
  private $dryRun;
  private $timeToLive;
  private $data;
  private $restrictedPackageName;
  private $notification;
  private $contentAvailable;
  private $priority;

  /**
   * Message Constructor
   */
  public function __construct() {
    $this->timeToLive = 2419200;
    $this->delayWhileIdle = false;
    $this->dryRun = false;
  }

  /**
   * Sets the collapseKey property.
   *
   * @param string $collapseKey
   * @return Message Returns the instance of this Message for method chaining.
   */
  public function collapseKey($collapseKey) {
    $this->collapseKey = $collapseKey;
    return $this;
  }

  public function getCollapseKey() {
    return $this->collapseKey;
  }

  /**
   * Sets the delayWhileIdle property (default value is {false}).
   *
   * @param bool $delayWhileIdle
   * @return Message Returns the instance of this Message for method chaining.
   */
  public function delayWhileIdle($delayWhileIdle) {
    $this->delayWhileIdle = $delayWhileIdle;
    return $this;
  }

  public function getDelayWhileIdle() {
    if(isset($this->delayWhileIdle))
      return $this->delayWhileIdle;
    return null;
  }

  /**
   * Sets the dryRun property (default value is {false}).
   *
   * @param bool $dryRun
   * @return Message Returns the instance of this Message for method chaining.
   */
  public function dryRun($dryRun) {
    $this->dryRun = $dryRun;
    return $this;
  }

  public function getDryRun() {
    return $this->dryRun;
  }

  /**
   * Sets the time to live, in seconds.
   *
   * @param int $timeToLive
   * @return Message Returns the instance of this Message for method chaining.
   */
  public function timeToLive($timeToLive) {
    $this->timeToLive = $timeToLive;
    return $this;
  }

  public function getTimeToLive() {
    return $this->timeToLive;
  }

  /**
   * Adds a key/value pair to the payload data.
   *
   * @param string $key
   * @param string $value
   * @return Message Returns the instance of this Message for method chaining.
   */
  public function addData($key, $value) {
    $this->data[$key] = $value;
    return $this;
  }

  /**
   * Sets the data property
   *
   * @param array $data
   * @return Message Returns the instance of this Message for method chaining.
   */
  public function data(array $data) {
    $this->data = $data;
    return $this;
  }

  public function getData() {
    return $this->data;
  }

  /**
   * Sets the restrictedPackageName property.
   *
   * @param string $restrictedPackageName
   * @return Message Returns the instance of this Message for method chaining.
   */
  public function restrictedPackageName($restrictedPackageName) {
    $this->restrictedPackageName = $restrictedPackageName;
    return $this;
  }

  public function getRestrictedPackageName() {
    return $this->restrictedPackageName;
  }

  /**
   * Sets the notification for the message. See the Notification class for more information.
   *
   * @param Notification $notification
   * @return Message Returns the instance of this Message for method chaining.
   */
  public function notification(Notification $notification) {
    $this->notification = $notification;
    return $this;
  }

  public function getNotification() {
    return $this->notification;
  }

  /**
   * Sets the contentAvailable property
   *
   * @param $contentAvailable
   * @return $this
   */
  public function contentAvailable($contentAvailable) {
    $this->contentAvailable = $contentAvailable;
    return $this;
  }

  public function getContentAvailable() {
    return $this->contentAvailable;
  }

  /**
   * Sets the priority property
   *
   * @param $priority
   * @return $this
   */
  public function priority($priority) {
    $this->priority = $priority;
    return $this;
  }

  public function getPriority() {
    return $this->priority;
  }

  public function build($recipients) {
    $message = array();

    if (!is_array($recipients)) {
      $message[self::TO] = $recipients;
    } else if (count($recipients) == 1) {
      $message[self::TO] = $recipients[0];
    } else {
      $message[self::REGISTRATION_IDS] = $recipients;
    }

    if (!empty($this->collapseKey)) {
      $message[self::COLLAPSE_KEY] = $this->collapseKey;
    }

    $message[self::DELAY_WHILE_IDLE] = $this->delayWhileIdle;
    $message[self::TIME_TO_LIVE] = $this->timeToLive;
    $message[self::DRY_RUN] = $this->dryRun;

    if (!empty($this->restrictedPackageName)) {
      $message[self::RESTRICTED_PACKAGE_NAME] = $this->restrictedPackageName;
    }

    if (!is_null($this->contentAvailable)) {
      $message[self::CONTENT_AVAILABLE] = $this->contentAvailable;
    }

    if ($this->priority) {
      $message[self::PRIORITY] = $this->priority;
    }

    if (!is_null($this->data) && count($this->data) > 0) {
      $message[self::DATA] = $this->data;
    }

    if ($this->notification != null) {
      $message[self::NOTIFICATION] = array();

      if ($this->notification->getBadge() != null) {
        $message[self::NOTIFICATION][self::NOTIFICATION_BADGE] = $this->notification->getBadge();
      }

      $message[self::NOTIFICATION][self::NOTIFICATION_BODY] = $this->notification->getBody();
      $message[self::NOTIFICATION][self::NOTIFICATION_BODY_LOC_ARGS] = $this->notification->getBodyLocArgs();
      $message[self::NOTIFICATION][self::NOTIFICATION_BODY_LOC_KEY] = $this->notification->getBodyLocKey();
      $message[self::NOTIFICATION][self::NOTIFICATION_CLICK_ACTION] = $this->notification->getClickAction();
      $message[self::NOTIFICATION][self::NOTIFICATION_COLOR] = $this->notification->getColor();
      $message[self::NOTIFICATION][self::NOTIFICATION_ICON] = $this->notification->getIcon();
      $message[self::NOTIFICATION][self::NOTIFICATION_SOUND] = $this->notification->getSound();
      $message[self::NOTIFICATION][self::NOTIFICATION_TAG] = $this->notification->getTag();
      $message[self::NOTIFICATION][self::NOTIFICATION_TITLE] = $this->notification->getTitle();
      $message[self::NOTIFICATION][self::NOTIFICATION_TITLE_LOC_ARGS] = $this->notification->getTitleLocArgs();
      $message[self::NOTIFICATION][self::NOTIFICATION_TITLE_LOC_KEY] = $this->notification->getTitleLocKey();
    }

    return json_encode($message);
  }
}
