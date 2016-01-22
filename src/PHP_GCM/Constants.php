<?php

namespace PHP_GCM;

/**
 * Constants used in GCM service communication.
 */
final class Constants {

  /**
   * Too many messages sent by the sender. Retry after a while.
   */
  public static $ERROR_QUOTA_EXCEEDED = 'QuotaExceeded';

  /**
   * Too many messages sent by the sender to a specific device.
   * Retry after a while.
   */
  public static $ERROR_DEVICE_QUOTA_EXCEEDED = 'DeviceQuotaExceeded';

  /**
   * Missing registration_id.
   * Sender should always add the registration_id to the request.
   */
  public static $ERROR_MISSING_REGISTRATION = 'MissingRegistration';

  /**
   * Bad registration_id. Sender should remove this registration_id.
   */
  public static $ERROR_INVALID_REGISTRATION = 'InvalidRegistration';

  /**
   * The sender_id contained in the registration_id does not match the
   * sender_id used to register with the GCM servers.
   */
  public static $ERROR_MISMATCH_SENDER_ID = 'MismatchSenderId';

  /**
   * The user has uninstalled the application or turned off notifications.
   * Sender should stop sending messages to this device and delete the
   * registration_id. The client needs to re-register with the GCM servers to
   * receive notifications again.
   */
  public static $ERROR_NOT_REGISTERED = 'NotRegistered';

  /**
   * The payload of the message is too big, see the limitations.
   * Reduce the size of the message.
   */
  public static $ERROR_MESSAGE_TOO_BIG = 'MessageTooBig';

  /**
   * Collapse key is required. Include collapse key in the request.
   */
  public static $ERROR_MISSING_COLLAPSE_KEY = 'MissingCollapseKey';

  /**
   * A particular message could not be sent because the GCM servers were not
   * available. Used only on JSON requests, as in plain text requests
   * unavailability is indicated by a 503 response.
   */
  public static $ERROR_UNAVAILABLE = 'Unavailable';

  /**
   * A particular message could not be sent because the GCM servers encountered
   * an error. Used only on JSON requests, as in plain text requests internal
   * errors are indicated by a 500 response.
   */
  public static $ERROR_INTERNAL_SERVER_ERROR = 'InternalServerError';

  /**
   * Time to Live value passed is less than zero or more than maximum.
   */
  public static $ERROR_INVALID_TTL= 'InvalidTtl';
}
