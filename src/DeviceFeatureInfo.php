<?php
/**
 * DeviceFeatureInfo.php
 */

namespace XQ;

/**
 * Provides basic information about the client device, as provided by javascript feature detection and stored in a
 * cookie.  If the cookie cannot be found (IE - Cookies or Javascript are disabled), some of the information is
 * detected from the UserAgent (which is not preferred).
 *
 * Class DeviceFeatureInfo
 *
 * @author  Aaron Jones <aaron@jonesiscoding.com>
 * @package XQ/Detect;
 */
class DeviceFeatureInfo
{

  /** @var array                  Full info from d.js */
  protected $_detect = array();

  protected $defaultWidth = 1024;
  protected $defaultHeight = 768;

// region ///////////////////////////////////////////////// Getters/Setters

  /**
   * Retrieves the full results of the current client detection, or a specific parameter.  Available parameters are
   * hidpi, width, height, speed, modern, touch, and cookies.
   *
   * @param string|null $item The parameter you wish to detect
   *
   * @return string|array|bool          The detected parameter, or if $item was not given, the array of all parameters.
   */
  public function get($item = null)
  {
    if (empty($this->_detect)) {
      $this->detectParse();
    }

    if ($item) {
      if (isset($this->_detect[$item])) {
        if ($item == "hidpi") {
          return $this->isHiDPI();
        } else {
          return $this->_detect[$item];
        }
      } else {
        return false;
      }
    }

    return $this->_detect;
  }

  /**
   * Returns the maximum width available in the user's device.  Please note that this does not indicate the current
   * size of the user's browser, but rather the maximum size it could be if they maximized the window.  It does not
   * account for things like scroll bars, etc.
   *
   * @return string
   */
  public function getDeviceMaxWidth()
  {
    return ($this->get('width')) ? $this->get('width') : $this->defaultWidth;
  }

  /**
   * Returns the maximum height available in the user's device.  Please note that this does not indicate the current
   * size of the user's browser, but rather the maximum size it could be if they maximized the window.  It does not
   * account for things like scroll bars, etc.
   *
   * @return int
   */
  public function getDeviceMaxHeight()
  {
    return ($this->get('height')) ? $this->get('height') : $this->defaultHeight;
  }

  /**
   * If the device is reporting a battery below 30% using the HTML5 Battery API.
   *
   * @return bool
   */
  public function isLowBattery()
  {
    return ($this->get('low_battery')) ? true : false;
  }

  /**
   * If the browser is reporting a 3G, 2G, or sub 1Mbit connection.  This uses the Network Information API.  The W3C
   * stopped work on this specification on 4/10/2014.  As some browser still support it, we'll use it if available.
   *
   * @return bool
   */
  public function isLowSpeed()
  {
    return $this->get('low_speed');
  }

  /**
   * If the browser is reporting a metered connection.  This uses the Network Information API.  The W3C stopped work on
   * this specification on 4/10/2014.  As some browser still support it, we'll use it if available.
   *
   * @return bool
   */
  public function isMetered()
  {
    return $this->get('metered');
  }

  /**
   * Determines if the user is using a client that utilizes a pixel density higher than 1, such as a hidpi windows
   * machine, a retina display mac, or a 2k/4k/5k monitor set to a scaled resolution.  This is useful for determining
   * what size images to send to the user, as 'normal' images are automatically scaled to double resolution on such
   * machines.  That can lead to some pretty ugly web images.
   *
   * @return  bool  TRUE if hidpi display, FALSE if not.
   */
  public function isHiDPI()
  {
    return $this->get('hidpi');
  }

  /**
   * Through some basic Javascript feature detection, it is evaluated whether or not the browser can support HTML4 or
   * HTML5.  Clients that cannot support either are determined to be "not modern".
   *
   * @return  bool TRUE by default, or FALSE if the browser is known to not support HTML4 or HTML5.
   */
  public function isModern()
  {
    return (($this->get('browser') != "fallback" && $this->get('browser') != "baseline")) ? true : false;
  }

  public function isFallback()
  {
    return ($this->get('browser') == "fallback") ? true : false;
  }

  public function isBaseline()
  {
    return ($this->get('browser') == "baseline") ? true : false;
  }

  /**
   * Through some basic JavaScript feature detection, it is determined whether the device being used has a touch screen.
   * Please note that the presence of a touch screen does not mean that the device is mobile, nor does it mean the user
   * uses the touch screen.  A good example of this would be a Windows 8.x laptop with a touch screen, in which the user
   * favors the mouse for input.
   *
   * @return  bool  FALSE by default, or TRUE if the device indicates that it supports touch capabilities.
   */
  public function isTouch()
  {
    return $this->get('touch');
  }

// endregion ////////////////////////////////////////////// End Getters/Setters

  /**
   * Alias for former method of getting detection information.
   * @deprecated
   * @return array|bool|string
   */
  public function getDetect()
  {
    return $this->get();
  }

  /**
   * Parses the cookie left by d.js.  If the cookie is not set due to Javascript being disabled, or cookies being
   * being blocked, all values are left at their (permissive) defaults, seen at the top of this class.
   */
  private function detectParse()
  {
    $cookies = false;
    if (isset($_COOKIE['d'])) {
      $x = json_decode($_COOKIE['d'], true);

      if (!is_null($x)) {

        // Convert Boolean values from strings
        foreach ($x as $k => $v) {
          if ($v == "true") {
            $x[$k] = true;
          }
          if ($v == "false") {
            $x[$k] = false;
          }
        }

        $this->_detect = $x;
        $this->_detect['cookies'] = true;

      }
    } else {
      if (count($_COOKIE) > 0) {
        $cookies = true;
      }
    }

    // Backup Method
    if (empty($this->_detect)) {

    }

    // Defaults
    if (empty($this->_detect)) {
      $this->_detect = array(
        'hidpi' => false,
        'width' => 1024,
        'height' => 768,
        'low_speed' => false,
        'metered' => false,
        'browser' => 'modern',
        'low_battery' => false,
        'touch' => false,
        'android' => false,
        'ios' => false
      );

      $this->_detect['cookies'] = $cookies;
    }

  }

}