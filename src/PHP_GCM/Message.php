<?php

namespace PHP_GCM;

class Message {

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
}
