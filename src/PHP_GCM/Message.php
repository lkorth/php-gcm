<?php
/*
 * Copyright 2013 Luke Korth
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

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
     * @param bool $delayWhileIdle
     * @param bool $dryRun
     * @param int $timeToLive
     * @param array $data
     * @param string $restrictedPackageName
     */
    public function __construct($collapseKey = '', array $data = array(), $timeToLive = -1, $delayWhileIdle = '',
                                $restrictedPackageName = '', $dryRun = false) {
        $this->collapseKey = $collapseKey;

        if($delayWhileIdle != '')
            $this->delayWhileIdle = $delayWhileIdle;

        $this->dryRun = $dryRun;
        $this->timeToLive = $timeToLive;
        $this->data = $data;
        $this->restrictedPackageName = $restrictedPackageName;
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