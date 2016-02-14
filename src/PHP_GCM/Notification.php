<?php

namespace PHP_GCM;

/**
 * Used for notification messages. See https://developers.google.com/cloud-messaging/http-server-ref#notification-payload-support
 * for more information about the parameters.
 */
class Notification {

  private $icon;
  private $title;
  private $body;
  private $sound;
  private $badge;
  private $tag;
  private $color;
  private $clickAction;
  private $bodyLocKey;
  private $bodyLocArgs;
  private $titleLocKey;
  private $titleLocArgs;

  public function __construct() {
    $this->sound = 'default';
  }

  /**
   * Required, Android only.
   *
   * @param string Indicates notification icon. On Android: sets value to myicon for drawable resource myicon.
   * @return Notification Returns the instance of this Notification for method chaining.
   */
  public function icon($icon) {
    $this->icon = $icon;
    return $this;
  }

  public function getIcon() {
    return $this->icon;
  }

  /**
   * Optional, Android and iOS.
   *
   * Android sound files must reside in /res/raw/, while iOS sound files can be
   * in the main bundle of the client app or in the Library/Sounds folder of the
   * app’s data container. See the iOS Developer Library for more information.
   *
   * @param string Indicates a sound to play when the device receives the notification.
   * Supports default, or the filename of a sound resource bundled in the app.
   * @return Notification Returns the instance of this Notification for method chaining.
   */
  public function sound($sound) {
    $this->sound = $sound;
    return $this;
  }

  public function getSound() {
    return $this->sound;
  }

  /**
   * Required Android
   * Optional iOS
   *
   * @param string Indicates notification title. This field is not visible on iOS phones and tablets.
   * @return Notification Returns the instance of this Notification for method chaining.
   */
  public function title($title) {
    $this->title = $title;
    return $this;
  }

  public function getTitle() {
    return $this->title;
  }

  /**
   * Optional, Android and iOS.
   *
   * @param string Indicates notification body text.
   * @return Notification Returns the instance of this Notification for method chaining.
   */
  public function body($body) {
    $this->body = $body;
    return $this;
  }

  public function getBody() {
    return $this->body;
  }

  /**
   * Optional, iOS only.
   *
   * @param int Indicates the badge on client app home icon.
   * @return Notification Returns the instance of this Notification for method chaining.
   */
  public function badge($badge) {
    $this->badge = $badge;
    return $this;
  }

  public function getBadge() {
    return $this->badge;
  }

  /**
   * Optional, Android only.
   *
   * @param string Indicates whether each notification message results in a new entry
   * on the notification center on Android. If not set, each request creates a new notification.
   * If set, and a notification with the same tag is already being shown, the new notification
   * replaces the existing one in notification center.
   * @return Notification Returns the instance of this Notification for method chaining.
   */
  public function tag($tag) {
    $this->tag = $tag;
    return $this;
  }

  public function getTag() {
    return $this->tag;
  }

  /**
   * Optional, Android only.
   *
   * @param string Indicates color of the icon, expressed in #rrggbb format
   * @return Notification Returns the instance of this Notification for method chaining.
   */
  public function color($color) {
    $this->color = $color;
    return $this;
  }

  public function getColor() {
    return $this->color;
  }

  /**
   * Optional, Android and iOS.
   *
   * @param string The action associated with a user click on the notification.
   * On Android, if this is set, an activity with a matching intent filter is launched
   * when user clicks the notification. For example, if one of your Activities includes the intent filter:
   *
   * <intent-filter>
   *   <action android:name="OPEN_ACTIVITY_1" />
   *   <category android:name="android.intent.category.DEFAULT" />
   * </intent-filter>
   *
   * Set click_action to OPEN_ACTIVITY_1 to open it.
   * If set, corresponds to category in APNS payload.
   * @return Notification Returns the instance of this Notification for method chaining.
   */
  public function clickAction($clickAction) {
    $this->clickAction = $clickAction;
    return $this;
  }

  public function getClickAction() {
    return $this->clickAction;
  }

  /**
   * Optional, Android and iOS.
   *
   * @param string Indicates the key to the body string for localization.
   * On iOS, this corresponds to "loc-key" in APNS payload.
   * On Android, use the key in the app's string resources when populating this value.
   * @return Notification Returns the instance of this Notification for method chaining.
   */
  public function bodyLocKey($bodyLocKey) {
    $this->bodyLocKey = $bodyLocKey;
    return $this;
  }

  public function getBodyLocKey() {
    return $this->bodyLocKey;
  }

  /**
   * Optional, Android and iOS.
   *
   * @param array Indicates the string value to replace format specifiers in body string for localization.
   * On iOS, this corresponds to "loc-args" in APNS payload.
   * On Android, these are the format arguments for the string resource. For more information, see Formatting strings.
   * @return Notification Returns the instance of this Notification for method chaining.
   */
  public function bodyLocArgs(array $bodyLocArgs) {
    $this->bodyLocArgs = $bodyLocArgs;
    return $this;
  }

  public function getBodyLocArgs() {
    return $this->bodyLocArgs;
  }

  /**
   * Optional, Android and iOS.
   *
   * @param string Indicates the key to the title string for localization.
   * On iOS, this corresponds to "title-loc-key" in APNS payload.
   * On Android, use the key in the app's string resources when populating this value.
   * @return Notification Returns the instance of this Notification for method chaining.
   */
  public function titleLocKey($titleLocKey) {
    $this->titleLocKey = $titleLocKey;
    return $this;
  }

  public function getTitleLocKey() {
    return $this->titleLocKey;
  }

  /**
   * Optional, Android and iOS.
   *
   * @param array Indicates the string value to replace format specifiers in title string for localization.
   * On iOS, this corresponds to "title-loc-args" in APNS payload.
   * On Android, these are the format arguments for the string resource. For more information, see Formatting strings.
   * @return Notification Returns the instance of this Notification for method chaining.
   */
  public function titleLocArgs(array $titleLocArgs) {
    $this->titleLocArgs = $titleLocArgs;
    return $this;
  }

  public function getTitleLocArgs() {
    return $this->titleLocArgs;
  }
}
