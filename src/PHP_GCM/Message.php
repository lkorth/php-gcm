<?php

namespace PHP_GCM;

class Message {

    private $collapseKey;
    private $delayWhileIdle;
    private $timeToLive;
    private $data;

    /**
     * Message constructor
     *
     * @param string $collapseKey
     * @param bool $delayWhileIdle
     * @param int $timeToLive
     * @param array $data
     */
    public function __construct($collapseKey = '', $delayWhileIdle = false, $timeToLive = -1, array $data = array()) {
        $this->collapseKey = $collapseKey;
        $this->delayWhileIdle = $delayWhileIdle;
        $this->timeToLive = $timeToLive;
        $this->data = $data;
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
        return $this->delayWhileIdle;
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
}