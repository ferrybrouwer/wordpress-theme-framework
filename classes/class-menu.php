<?php
  namespace HappyFramework;

  /**
   * Class Menu
   *
   * @package App
   */
  class Menu
  {
    public $location    = null;
    public $args        = array();
    public $description = null;
    public $walker      = null;
    public $depth       = 1;
    public $id          = null;

    /**
     * Register wp nav menu
     *
     * @param   string $location
     * @param   string $description [optional]
     * @param   array  $args        [optional]
     */
    public function __construct($location, $description = null, array $args = array())
    {
      $this->location = $location;
      $this->args = $args;
      $this->description = $description;

      register_nav_menu($location, $description);
      add_filter('wp_nav_menu', array($this, 'removeUlAttributes'));

      $this->id = $this->getMenuIdFromLocation($this->location);
    }

    /**
     * Get menu id from location
     *
     * @param string $location
     * @return null|int
     */
    private function getMenuIdFromLocation($location)
    {
      $mod = get_theme_mod('nav_menu_locations');

      return !empty($mod[$location]) ? $mod[$location] : null;
    }

    /**
     * Remove attributes from ul element
     * Unnecessary selector reference
     *
     * @param string $html
     * @return string
     */
    public function removeUlAttributes($html)
    {
      return preg_replace('/<ul [^\>]+/', '<ul', $html);
    }

    /**
     * Get default arguments for wp_nav_menu
     *
     * @return array
     */
    private function getDefaultArguments()
    {
      return array(
        'theme_location' => $this->location,
        'container'      => false,
        'depth'          => $this->depth,
        'walker'         => $this->walker
      );
    }

    /**
     * Get html of menu
     *
     * @param   array $args [optional]
     * @return  string
     */
    public function html(array $args = array())
    {
      ob_start();

      $args = array_merge(array(
                            'class'     => null,
                            'data-bind' => null,
                            'id'        => $this->location . '-menu'
                          ), $args); ?>

    <nav
      role="navigation"
      id="<?php echo $args['id'] ?>"
      <?php if ($args['class']): ?>class="<?php echo $args['class'] ?>"<?php endif; ?>
      <?php if ($args['data-bind']): ?>data-bind="<?php echo esc_attr($args['data-bind']) ?>"<?php endif; ?>>
      <?php wp_nav_menu(array_merge($this->getDefaultArguments(), $args)) ?>
      </nav><?php
      return ob_get_clean();
    }

    /**
     * Get menu name
     *
     * @param null|string $default
     * @return null|string
     */
    public function getMenuName($default = null)
    {
      $menu = wp_get_nav_menu_object($this->id);

      return $menu ? (string)$menu->name : $default;
    }
  }
