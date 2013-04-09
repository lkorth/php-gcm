<?php

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