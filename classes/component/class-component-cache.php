<?php
  namespace HappyFramework\Components;

  use HappyFramework\Options\Cache as CacheOption;

  class Cache
  {
    protected static $exclude_cache_pages = array();

    public function __construct()
    {
    }

    /**
     * Get fragment cache transient
     *
     * @param string   $key      [identifier of the fragment]
     * @param number   $ttl      [time to live in seconds]
     * @param callable $function [function to cache]
     */
    public static function fragment($key, $ttl, $function)
    {
      global $post;

      // prevent caching when it's not able to cache
      if (!isset($post) || (is_user_logged_in() && is_admin()) || in_array($post->ID, Cache::$exclude_cache_pages) || CacheOption::getOption('enable') !== '1') {
        call_user_func($function);

        return;
      }

      // from this point output is generated from the transient
      $key = apply_filters('fragment_cache_prefix', 'fragment_cache_') . $key;
      $output = get_transient($key);

      if (empty($output)) {
        ob_start();
        call_user_func($function);
        $output = ob_get_clean();
        set_transient($key, $output, $ttl);
      }

      echo $output;
    }

    /**
     * Clear fragment
     *
     * @param   string $key
     */
    public static function clear($key)
    {
      delete_transient(apply_filters('fragment_cache_prefix', 'fragment_cache_') . $key);
    }
  }
