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

/**
 * Exception thrown when GCM returned an error due to an invalid request.
 * <p>
 * This is equivalent to GCM posts that return an HTTP error different of 200.
 */
class InvalidRequestException extends \Exception {

    private $status;
    private $description;

    public function __construct($status, $description = '') {
        $this->status = $status;
        $this->description = $description;

        parent::__construct($description, $status, null);
    }

    public function __toString() {
        return __CLASS__ . ': HTTP Status Code: ' . $this->status . ' (' . $this->description . ')';
    }

    /**
     * Gets the HTTP Status Code.
     *
     * @return int
     */
    public function getHttpStatusCode() {
        return $this->status;
    }

    /**
     * Gets the error description.
     *
     * @return string
     */
    public function getDescription() {
        return $this->description;
    }
}