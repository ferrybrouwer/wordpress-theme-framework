<?php
  namespace HappyFramework\Interfaces;

  interface ITheme
  {
    /**
     * Set components
     *
     * @param array $components
     */
    public function setComponents(array $components);

    /**
     * Set options
     *
     * @param array $options
     */
    public function setOptions(array $options);

    /**
     * Set post types
     *
     * @param array $postTypes
     */
    public function setPostTypes(array $postTypes);

    /**
     * Set shortcodes
     *
     * @param array $shortcodes
     */
    public function setShortcodes(array $shortcodes);

    /**
     * Set scripts
     *
     * @param array $scripts
     */
    public function setScripts(array $scripts);

    /**
     * Register menu
     *
     * @param string      $location
     * @param string|null $description
     * @param array       $args
     * @return \HappyFramework\Menu
     */
    public function registerMenu($location, $description = null, array $args = array());

    /**
     * Get registered menu
     *
     * @param $location
     * @return \HappyFramework\Menu
     */
    public static function getRegisteredMenu($location);

    /**
     * Get menu html by location
     *
     * @param string $location
     * @param array  $args
     * @return string
     */
    public static function getMenuHtml($location, array $args = array());

    /**
     * Initialize menus
     */
    public function initMenu();

    /**
     * Initialize theme support

     */
    public function initThemeSupport();

    /**
     * Set image sizes
     */
    public function setImageSizes();

    /**
     * Initialize widgets
     */
    public function initWidgets();

    /**
     * Add image size
     *
     * @param string $id
     * @param string $name
     * @param int    $width
     * @param int    $height
     * @param bool   $crop
     */
    public function addImageSize($id, $name, $width = 0, $height = 0, $crop = false);

    /**
     * Set thumnbnail size
     *
     * @param int $width
     * @param int $height
     */
    public function setThumbnailSize($width, $height);

    /**
     * Set admin templates path
     *
     * @param string $path
     */
    public function setAdminTemplatesPath($path);

    /**
     * Set path to partials
     *
     * @param string $path
     */
    public function setPartialsPath($path);
  }
