<?php
  namespace HappyFramework\Helpers;

  /**
   * Class Browser
   *
   * @package HappyFramework\Helpers
   */
  class Browser
  {
    static $browser;
    static $browsers = array("firefox", "msie", "opera", "chrome", "safari",
      "mozilla", "seamonkey", "konqueror", "netscape",
      "gecko", "navigator", "mosaic", "lynx", "amaya",
      "omniweb", "avant", "camino", "flock", "aol");

    /**
     * Get browser data
     *
     * @return \stdClass
     */
    private static function getBrowserData()
    {
      if (!isset(Browser::$browser)) {
        Browser::$browser = new \stdClass();
        Browser::$browser->agent = strtolower($_SERVER['HTTP_USER_AGENT']);

        foreach (Browser::$browsers as $browser) {
          if (preg_match("#($browser)[/ ]?([0-9.]*)#", Browser::$browser->agent, $match)) {
            Browser::$browser->name = (string)$match[1];
            Browser::$browser->version = (float)$match[2];
            break;
          }
        }
      }

      return Browser::$browser;
    }

    /**
     * Get browser name
     *
     * @return string
     */
    public static function getBrowser()
    {
      return Browser::getBrowserData()->name;
    }

    /**
     * Get browser version
     *
     * @return string
     */
    public static function getVersion()
    {
      return Browser::getBrowserData()->version;
    }
  }
