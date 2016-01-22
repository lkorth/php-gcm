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

  private $collapseKey;
  private $delayWhileIdle;
  private $dryRun;
  private $timeToLive;
  private $data;
  private $restrictedPackageName;

  /**
   * Message Constructor
   *
   * @param string $collapseKey
   * @param array $data
   * @param int $timeToLive
   * @param bool $delayWhileIdle
   * @param string $restrictedPackageName
   * @param bool $dryRun
   */
  public function __construct($collapseKey = '', array $data = array(), $timeToLive = 2419200,
    $delayWhileIdle = false, $restrictedPackageName = '', $dryRun = false) {
      $this->collapseKey = $collapseKey;
      $this->data = $data;
      $this->timeToLive = $timeToLive;
      $this->delayWhileIdle = $delayWhileIdle;
      $this->restrictedPackageName = $restrictedPackageName;
      $this->dryRun = $dryRun;
    }

  /**
   * Sets the collapseKey property.
   *
   * @param string $collapseKey
   */
  public function collapseKey($collapseKey) {
    $this->collapseKey = $collapseKey;
  }

  /**
   * Gets the collapseKey property
   *
   * @return string
   */
  public function getCollapseKey() {
    return $this->collapseKey;
  }

  /**
   * Sets the delayWhileIdle property (default value is {false}).
   *
   * @param bool $delayWhileIdle
   */
  public function delayWhileIdle($delayWhileIdle) {
    $this->delayWhileIdle = $delayWhileIdle;
  }

  /**
   * Gets the delayWhileIdle property
   *
   * @return bool
   */
  public function getDelayWhileIdle() {
    if(isset($this->delayWhileIdle))
      return $this->delayWhileIdle;
    return null;
  }

  /**
   * Sets the dryRun property (default value is {false}).
   *
   * @param bool $dryRun
   */
  public function dryRun($dryRun) {
    $this->dryRun = $dryRun;
  }

  /**
   * Gets the dryRun property
   *
   * @return bool
   */
  public function getDryRun() {
    return $this->dryRun;
  }

  /**
   * Sets the time to live, in seconds.
   *
   * @param int $timeToLive
   */
  public function timeToLive($timeToLive) {
    $this->timeToLive = $timeToLive;
  }

  /**
   * Gets the timeToLive property
   *
   * @return int
   */
  public function getTimeToLive() {
    return $this->timeToLive;
  }

  /**
   * Adds a key/value pair to the payload data.
   *
   * @param string $key
   * @param string $value
   */
  public function addData($key, $value) {
    $this->data[$key] = $value;
  }

  /**
   * Sets the data property
   *
   * @param array $data
   */
  public function data(array $data) {
    $this->data = $data;
  }

  /**
   * Gets the data property
   *
   * @return array
   */
  public function getData() {
    return $this->data;
  }

  /**
   * Sets the restrictedPackageName property.
   *
   * @param string $restrictedPackageName
   */
  public function restrictedPackageName($restrictedPackageName) {
    $this->restrictedPackageName = $restrictedPackageName;
  }

  /**
   * Gets the restrictedPackageName property
   *
   * @return string
   */
  public function getRestrictedPackageName() {
    return $this->restrictedPackageName;
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

    if ($this->collapseKey != '') {
      $message[self::COLLAPSE_KEY] = $this->collapseKey;
    }

    $message[self::DELAY_WHILE_IDLE] = $this->delayWhileIdle;
    $message[self::TIME_TO_LIVE] = $this->timeToLive;
    $message[self::DRY_RUN] = $this->dryRun;

    if ($this->restrictedPackageName != '') {
      $message[self::RESTRICTED_PACKAGE_NAME] = $this->restrictedPackageName;
    }

    if (!is_null($this->data) && count($this->data) > 0) {
      $message[self::DATA] = $this->data;
    }

    return json_encode($message);
  }
}
