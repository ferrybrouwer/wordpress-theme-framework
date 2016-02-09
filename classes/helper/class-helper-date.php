<?php
  namespace HappyFramework\Helpers;

  /**
   * Class Date
   *
   * @package HappyFramework\Helpers
   */
  class Date
  {

    /**
     * Get translated month names
     *
     * @return array
     */
    public static function getMonthNames()
    {
      global $wp_locale;
      $months = array();
      foreach ($wp_locale->month as $month) {
        $months[] = ucfirst($month);
      }

      return $months;
    }

    /**
     * Get translated short month names
     *
     * @return array
     */
    public static function getMonthShortNames()
    {
      global $wp_locale;
      $months = array();
      foreach ($wp_locale->weekday_abbrev as $month) {
        $months[] = ucfirst($month);
      }

      return $months;
    }

    /**
     * Get date of last edited post
     *
     * @return null|string
     */
    public static function getLastEditedPostDate()
    {
      global $wpdb;

      $query = "
            SELECT `post_modified` FROM " . $wpdb->posts . " p
            WHERE p.`post_status` = 'publish'
            AND p.`post_type` != 'nav_menu_item'
            ORDER BY p.`post_modified` DESC
            LIMIT 1
        ";

      return $wpdb->get_var($query);
    }

    /**
     * Get index of the first day of the week
     *
     * @return int
     */
    public static function getFirstDayOfTheWeekIndex()
    {
      return get_option('start_of_week') ?: 0;
    }

    /**
     * Get translated day names
     *
     * @return array
     */
    public static function getDayNames()
    {
      global $wp_locale;
      $days = array();
      foreach ($wp_locale->weekday as $day) {
        $days[] = ucfirst($day);
      }

      return $days;
    }

    /**
     * Get translated short day names
     *
     * @return array
     */
    public static function getDayShortNames()
    {
      global $wp_locale;
      $days = array();
      foreach ($wp_locale->weekday_abbrev as $day) {
        $days[] = ucfirst($day);
      }

      return $days;
    }

    /**
     * Get translated first letters of days in week
     *
     * @return array
     */
    public static function getDayFirstCharacters()
    {
      global $wp_locale;
      $days = array();
      foreach ($wp_locale->weekday_initial as $day) {
        $days[] = ucfirst($day);
      }

      return $days;
    }

    /**
     * Get age from given birthdate
     *
     * @param string $birthdate [format dd-mm-YYYY]
     * @return int
     */
    public static function getAgeFromBirthdate($birthdate)
    {
      return floor((time() - strtotime($birthdate)) / YEAR_IN_SECONDS);
    }

    /**
     * Get publish date of post
     *
     * @param int $post_id [optional]
     * @return null|string
     */
    public static function getPublishDatePost($post_id = null)
    {
      global $post;
      $post = $post_id ? get_post($post_id) : $post;

      return is_a($post, 'WP_Post') ? $post->post_date : null;
    }
  }
