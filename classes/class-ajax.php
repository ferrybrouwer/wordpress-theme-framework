<?php
  namespace HappyFramework;

  class Ajax
  {
    /**
     * Register ajax callback
     *
     * @param int           $id
     * @param null|callable $callback
     * @param bool          $no_private
     * @param bool          $json
     */
    public static function register($id, $callback = null, $no_private = false, $json = true)
    {
      add_action('wp_ajax_' . $id, function () use ($json, $callback) {
        Ajax::callback($callback, $json);
      });

      if ($no_private) {
        add_action('wp_ajax_nopriv_' . $id, function () use ($json, $callback) {
          Ajax::callback($callback, $json);
        });
      }
    }

    /**
     * Ajax callback format
     *
     * @param   callable $callback
     * @param   bool     $json
     */
    public static function callback($callback, $json = true)
    {
      if ($json) {
        header('Content-Type: application/json');
        echo json_encode(call_user_func($callback));
      } else {
        echo call_user_func($callback);
      }
      die();
    }
  }
