<?php
  namespace HappyFramework\Helpers;

  /**
   * Class Html
   *
   * @package HappyFramework\Helpers
   */
  class Html
  {
    /**
     * Minify
     *
     * @param string $str
     * @return string
     */
    public static function minify($str)
    {
      $search = array(
        '/\>[^\S ]+/s',  // strip whitespaces after tags, except space
        '/[^\S ]+\</s',  // strip whitespaces before tags, except space
        '/(\s)+/s'       // shorten multiple whitespace sequences
      );
      $replace = array(
        '>',
        '<',
        '\\1'
      );

      return preg_replace($search, $replace, $str);
    }
  }
