<?php
  namespace HappyFramework\Interfaces;

  /**
   * Interface IShortcode
   *
   * @package HappyFramework\Interfaces
   */
  interface IShortcode
  {

    /**
     * get shortcode output
     *
     * @param array  $atts
     * @param string $content [optional]
     * @return string
     */
    public function render($atts, $content);

    /**
     * Filter output before render shortcode
     *
     * @param array  $atts
     * @param string $content
     */
    public function filterBeforeRender($atts, $content);
  }
