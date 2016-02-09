<?php
  namespace HappyFramework;

  /**
   * Class Widgets
   *
   * @package App
   */
  class Widgets
  {
    public function __construct()
    {
    }

    /**
     * Check if given widget is activated
     *
     * @param string $widget_name
     * @return bool
     */
    public static function isActiveWidget($widget_name)
    {
      return in_array($widget_name, get_option('active_plugins', array()));
    }

    /**
     * Get number of widgets in a sidebar
     *
     * @param string $sidebar
     * @return int
     */
    public static function widgetCount($sidebar)
    {
      $sidebars = wp_get_sidebars_widgets();

      return !empty($sidebars[$sidebar]) ? count($sidebars[$sidebar]) : 0;
    }

    /**
     * Register widget area
     *
     * @param array $properties
     */
    public static function registerArea($area)
    {
      add_action('widgets_init', function () use ($area) {
        register_sidebar($area);
      });
    }

    /**
     * Register widget class
     *
     * @param string $className
     */
    public static function registerClass($className)
    {
      add_action('widgets_init', function () use ($className) {
        register_widget($className);
      });
    }

    /**
     * Unregister default WordPress Widgets
     *
     * @hook widgets_init
     */
    public static function unregisterDefaultWidgets()
    {
      add_action('widgets_init', function () {
        unregister_widget('WP_Widget_Pages');
        unregister_widget('WP_Widget_Calendar');
        unregister_widget('WP_Widget_Archives');
        unregister_widget('WP_Widget_Links');
        unregister_widget('WP_Widget_Meta');
        unregister_widget('WP_Widget_Search');
        unregister_widget('WP_Widget_Text');
        unregister_widget('WP_Widget_Categories');
        unregister_widget('WP_Widget_Recent_Posts');
        unregister_widget('WP_Widget_Recent_Comments');
        unregister_widget('WP_Widget_RSS');
        unregister_widget('WP_Widget_Tag_Cloud');
        unregister_widget('WP_Nav_Menu_Widget');
        unregister_widget('Twenty_Eleven_Ephemera_Widget');
      });
    }
  }
