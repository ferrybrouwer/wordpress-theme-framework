<?php
  namespace HappyFramework\Abstracts;

  use HappyFramework\Interfaces\IShortcode;

  /**
   * Abstract Class PostType
   *
   * @package HappyFramework\PostTypes
   */
  abstract class AbstractShortcode implements IShortcode
  {
    public $identifier;
    public $label;
    public $attributes = array();
    public $template   = null;
    public $tinymce    = null;

    /**
     * @constructor
     * @param string $identifier
     * @param string $label
     * @param array  $attributes
     */
    public function __construct($identifier, $label, array $attributes = array())
    {
      $this->identifier = $identifier;
      $this->label = $label;
      $this->attributes = $attributes;

      add_shortcode($this->identifier, array($this, 'shortcodeCallback'), 10, 2);
    }

    /**
     * Shortcode callback
     *
     * @param array  $atts
     * @param string $content
     * @return string
     */
    public function shortcodeCallback($atts, $content)
    {
      $params = (array)shortcode_atts($this->attributes, $atts);
      $str = $this->filterBeforeRender($atts, $content);

      // render template if is set
      if ($this->template) {
        ob_start();
        extract($params);
        include(locate_template($this->template));
        $str = ob_get_clean();
      } else {
        $str = $this->render($params, $content);
      }

      // return rendered string
      return $str;
    }

    /**
     * get shortcode output
     *
     * @param array  $atts
     * @param string $content [optional]
     * @return string
     */
    public function render($atts, $content)
    {
      return '';
    }

    /**
     * Before output render shortcode
     *
     * @param array  $atts
     * @param string $content
     * @return string
     */
    public function filterBeforeRender($atts, $content)
    {
      return '';
    }

    /**
     * Add shortcode to Tiny MCE
     *
     * @param string $template
     */
    public function addToTinyMCE($template)
    {
      $this->tinymce = $template;
    }
  }
