<?php
  namespace HappyFramework;

  /**
   * Class Script
   *
   * @package App
   */
  class Script
  {
    public $stylesheetUri;
    public $scripts              = array();
    public $styles               = array();
    public $localizeScripts      = array();
    public $removeScriptsHandles = array();
    public $removeStyleHandles   = array();

    /**
     * @constructor
     */
    public function __construct()
    {
    }

    /**
     * Add paths in admin head
     *
     * @hook admin_head
     */
    public function addPathsToHead()
    {
      ?>
      <script>
        window.happy = {
          models:  {},
          classes: {},
          paths:   {
            ajaxurl: '<?php echo admin_url('admin-ajax.php'); ?>',
            images:  '<?php echo get_template_directory_uri() ?>/images/',
            js:      '<?php echo get_template_directory_uri() ?>/js/'
          }
        };
      </script>
    <?php
    }

    /**
     * Add scripts
     *
     * @param array $scripts
     * @example:
     *      array(
     *      array(
     *      'handle'    => 'jquery',
     *      'src'       => get_template_directory_uri() . '/js/vendor/jquery.js',
     *      'deps'      => '',
     *      'in_footer' => true
     *      )
     *      )
     */
    public function addScripts(array $scripts)
    {
      $self = $this;
      $this->scripts = $scripts;

      add_action(is_admin() ? 'admin_enqueue_scripts' : 'wp_enqueue_scripts', function () use ($self) {
        if (is_admin() && function_exists('wp_enqueue_media')) {
          wp_enqueue_media();
        }

        foreach ($self->scripts as $script) {
          if (isset($script['src'])) {
            wp_enqueue_script(
              $script['handle'],
              $script['src'],
              (isset($script['deps']) && is_array($script['deps'])) ? $script['deps'] : null,
              null,
              isset($script['in_footer']) ? $script['in_footer'] : true
            );
          } else {
            wp_enqueue_script($script['handle']);
          }
        }

        do_action('scripts_loaded');
      });
    }

    /**
     * Remove scripts (deregister)
     *
     * @param array $handles
     */
    public function removeScripts(array $handles)
    {
      $self = $this;
      $this->removeScriptsHandles = $handles;

      add_action('wp_enqueue_scripts', function () use ($self) {
        foreach ($self->removeScriptsHandles as $handle) {
          wp_dequeue_script($handle);
        }
      }, 99);
    }

    /**
     * Remove styles [deregister]
     *
     * @param  array $handles
     */
    public function removeStyles(array $handles)
    {
      $self = $this;
      $this->removeStyleHandles = $handles;

      add_action('wp_enqueue_scripts', function () use ($self) {
        foreach ($self->removeStyleHandles as $handle) {
          wp_dequeue_style($handle);
        }
      });
    }

    /**
     * Add styles
     *
     * @param array $styles
     */
    public function addStyles(array $styles)
    {
      $self = $this;
      $this->styles = $styles;

      add_action(is_admin() ? 'admin_enqueue_scripts' : 'wp_enqueue_scripts', function () use ($self) {
        foreach ($self->styles as $style) {
          if (isset($style['src'])) {
            wp_enqueue_style(
              $style['handle'],
              $style['src'],
              (isset($style['deps']) && is_array($style['deps'])) ? $style['deps'] : null,
              null
            );
          } else {
            wp_enqueue_style($style['handle']);
          }
        }

        do_action('styles_loaded');
      });
    }

    /**
     * Add localize scripts
     *
     * @param array $scripts
     * @example:
     *      array(
     *      array(
     *      'handle'    =>  'jquery',
     *      'name'      =>  'jqueryParams',
     *      'data'      =>  array('key'=>'value')
     *      )
     *      )
     */
    public function addLocalizeScripts(array $scripts)
    {
      $self = $this;
      $this->localizeScripts = $scripts;

      add_action('scripts_loaded', function () use ($self) {
        foreach ($self->localizeScripts as $localizeScript) {
          wp_localize_script(
            $localizeScript['handle'],
            $localizeScript['name'],
            $localizeScript['data']
          );
        }
      });
    }

    /**
     * Set stylesheet url
     *
     * @param string $stylesheetUri
     */
    public function setStylesheetUri($stylesheetUri)
    {
      $self = $this;
      $this->stylesheetUri = $stylesheetUri;

      add_filter('stylesheet_uri', function () use ($self) {
        return $self->stylesheetUri;
      });
    }
  }
