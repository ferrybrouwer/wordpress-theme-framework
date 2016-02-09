<?php
  namespace HappyFramework;

  // post type classes
  require_once 'classes/post-type/class-posttype-interface.php';
  require_once 'classes/post-type/class-posttype-abstract.php';

  // shortcode classes
  require_once 'classes/shortcodes/class-shortcode-interface.php';
  require_once 'classes/shortcodes/class-shortcode-abstract.php';

  // tinymce classes
  require_once 'classes/tinymce/class-tinymce-submenu.php';

  // theme bases classes
  require_once 'classes/theme/class-theme-interface.php';
  require_once 'classes/theme/class-theme-abstract.php';

  // option classes
  require_once 'classes/option/class-option-menu-item-interface.php';
  require_once 'classes/option/class-option-menu-item-abstract.php';
  require_once 'classes/option/class-option-menu-sub-item.php';
  require_once 'classes/option/class-option-settings-section-interface.php';
  require_once 'classes/option/class-option-settings-section.php';
  require_once 'classes/option/class-option-cache.php';

  // miscellaneous classes
  require_once 'classes/class-ajax.php';
  require_once 'classes/class-script.php';
  require_once 'classes/class-widget.php';
  require_once 'classes/class-menu.php';
  require_once 'classes/class-taxonomy-meta.php';

  // helper classes
  require_once 'classes/helper/class-helper-date.php';
  require_once 'classes/helper/class-helper-metabox.php';
  require_once 'classes/helper/class-helper-settingsfield.php';
  require_once 'classes/helper/class-helper-color.php';
  require_once 'classes/helper/class-helper-taxonomy.php';
  require_once 'classes/helper/class-helper-log.php';
  require_once 'classes/helper/class-helper-file.php';
  require_once 'classes/helper/class-helper-html.php';
  require_once 'classes/helper/class-helper-browser.php';
  require_once 'classes/helper/class-helper-formatting.php';

  // component classes
  require_once 'classes/component/class-component-cache.php';

  /**
   * Class HappyFramework
   *
   * @package HappyFramework
   */
  class HappyFramework
  {

    /**
     * @constructor
     */
    protected function __construct()
    {
      add_action('admin_enqueue_scripts', array($this, 'addAdminScripts'), 10);
      add_action('admin_head', array($this, 'addAdminNamespace'), 99);
      add_action('admin_footer', array($this, 'addAdminBindModels'), 99);
    }

    /**
     * Create admin namespace
     * Create namespace if is not already defined
     */
    public function addAdminNamespace()
    {
      ?>
      <script type="text/javascript">
        if (typeof happy === 'undefined' || typeof happy.models === 'undefined') {
          var happy = {models: {}};
        }
      </script><?php
    }

    /**
     * Add admin scripts
     */
    public function addAdminScripts()
    {
      // load underscore if is not enqueued yet
      if (!wp_script_is('underscore', 'enqueued')) {
        wp_enqueue_script('underscore', '//cdnjs.cloudflare.com/ajax/libs/underscore.js/1.8.3/underscore-min.js', array(), '1.8.3', true);
      }

      // load underscore extensions
      if (!wp_script_is('underscore-extensions', 'enqueued')) {
        wp_enqueue_script(
          'underscore-extensions',
          get_template_directory_uri() . '/happy-framework/js/underscore-extensions.js',
          array('underscore'),
          false, true
        );
      }

      // load knockout if is not enqueued yet
      if (!wp_script_is('knockout', 'enqueued')) {
        wp_enqueue_script('knockout', '//cdnjs.cloudflare.com/ajax/libs/knockout/3.3.0/knockout-min.js', array(), '3.3.0', true);
      }

      // laod medialibrary
      if (!wp_script_is('happyframework-media-library', 'enqueued')) {
        wp_enqueue_script(
          'happyframework-media-library',
          get_template_directory_uri() . '/happy-framework/js/knockout-admin-medialibrary.js',
          array('knockout', 'underscore', 'media-models'),
          false,
          true
        );
      }
    }

    /**
     * Bind admin knockout models
     */
    public function addAdminBindModels()
    {
      ?>
      <script type="text/javascript">
        jQuery(function ($) {
          if (_.isUndefined(ko.dataFor(document.querySelector('html')))) {
            ko.applyBindings(window.happy.models, document.querySelector('html'));
          }
        });
      </script><?php
    }

    /**
     * Get instance of Framework (Singleton)
     *
     * @return static
     */
    final static public function getInstance()
    {
      static $instance = null;

      return $instance ?: $instance = new static;
    }

    /**
     * Magic method prevent cloning of the instance of the Singleton instance
     */
    final private function __clone()
    {
    }

    /**
     * Magic method prevent unserializing of the Singleton instance.
     */
    final private function __wakeup()
    {
    }
  }

  /**
   * Get main instance of the Heras Theme
   *
   * @return Heras_Theme
   */
  function HappyFramework()
  {
    return HappyFramework::getInstance();
  }

  HappyFramework();

