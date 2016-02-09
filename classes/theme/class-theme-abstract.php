<?php

  namespace HappyFramework\Abstracts;

  use HappyFramework\Components;
  use HappyFramework\Helpers;
  use HappyFramework\Interfaces\ITheme;
  use HappyFramework\Options;
  use HappyFramework\Widgets;

  abstract class AbstractTheme implements ITheme
  {
    public static $menus      = array();
    public static $domain;
    public static $themeId;
    public static $partialPath;
    public static $adminTemplateDir;
    public static $scripts    = array();
    public static $options    = array();
    public static $components = array();
    public static $postTypes  = array();
    public static $shortcodes = array();
    public static $imageSizes = array();

    public $metabox;
    public $breadcrumbs;
    public $tinyShortcodes;

    protected function __construct()
    {
      $this->metabox = new Helpers\Metabox;

      // when switch themes or update options, flush rewrite rules
      add_action('after_switch_theme', array('HappyFramework\Abstracts\AbstractTheme', 'flushRewriteRules'));
      add_action('update_option', array('HappyFramework\Abstracts\AbstractTheme', 'flushRewriteRules'));

      // remove empty paragraphes from the_content hook
      add_filter('the_content', array('HappyFramework\Abstracts\AbstractTheme', 'contentRemoveEmptyParagraph'));

      // set default options
      $this->setOptions(
        array(
          'cache' => new Options\Cache,
        )
      );

      // set default components
      $this->setComponents(
        array(
          'cache' => new Components\Cache,
        )
      );

      // invoke default methods
      add_action('theme_textdomain_loaded', array($this, 'initWidgets'));

      $this->initMenu();
      $this->initThemeSupport();
      $this->setImageSizes();
    }

    /**
     * Get instance of Theme (Singleton)
     *
     * @return static
     */
    final static public function getInstance()
    {
      static $instance = null;

      return $instance ?: $instance = new static;
    }

    /**
     * Get post type instance
     *
     * @param string $post_type
     * @return AbstractPostType|null
     */
    public static function getPostTypeInstance($post_type)
    {
      $instance = null;
      foreach (self::$postTypes as $postType) {
        if ($postType->type === $post_type) {
          $instance = $postType;
          break;
        }
      }

      return $instance;
    }

    /**
     * Get post type instance by class name
     *
     * @param string $class_name
     * @return null
     */
    public static function getPostTypeInstanceByClassName($class_name)
    {
      $instance = null;
      foreach (self::$postTypes as $postType) {
        if (is_a($postType, $class_name)) {
          $instance = $postType;
          break;
        }
      }

      return $instance;
    }

    /**
     * Get post type instance by identifier
     * Setted by self::setPostTypes array key
     *
     * @param string $identifier
     * @return null|PostTypes
     */
    public function getPostTypeInstanceByIdentifier($identifier)
    {
      return !empty(self::$postTypes[$identifier]) ? self::$postTypes[$identifier] : null;
    }

    /**
     * Get option classs instance by class name
     *
     * @param string $class_name
     * @return AbstractOptionMenuItem
     */
    public static function getOptionInstanceByClassName($class_name)
    {
      $instance = null;
      foreach (self::$options as $option) {
        if (is_a($option, $class_name)) {
          $instance = $option;
          break;
        }
      }

      return $instance;
    }

    /**
     * Get option class instance by identifier
     * Setted by self::setOptions array key
     *
     * @param string $identifier
     * @return null|Options
     */
    public static function getOptionInstanceByIdentifier($identifier)
    {
      return !empty(self::$options[$identifier]) ? self::$options[$identifier] : null;
    }

    /**
     * Get component instance by class name
     * Setted by self::setComponents array key
     *
     * @param string $class_name
     * @return \stdClass
     */
    public static function getComponentInstanceByClassName($class_name)
    {
      $instance = null;
      foreach (self::$components as $component) {
        if (is_a($component, $class_name)) {
          $instance = $component;
          break;
        }
      }

      return $instance;
    }

    /**
     * Get component class instance by identifier
     *
     * @param string $identifier
     * @return null|Components
     */
    public function getComponentInstanceByIdentifier($identifier)
    {
      return !empty(self::$components[$identifier]) ? self::$components[$identifier] : null;
    }

    /**
     * Flush rewrite rules
     *
     * @hook after_switch_theme
     */
    public static function flushRewriteRules()
    {
      flush_rewrite_rules(true);
    }

    /**
     * Filter default content
     * Instead of using wpautop which removes all p tags we
     * only filter out the empty paragraphe tags
     *
     * @hook    the_content
     * @param   string $content
     * @return  string
     */
    public static function contentRemoveEmptyParagraph($content)
    {
      $content = preg_replace('#<p>(\s|&nbsp;)*+(<br\s*/*>)?(\s|&nbsp;)*</p>#i', '', $content);
      $content = strtr($content, array('<p>[' => '[', ']</p>' => ']', ']<br />' => ']'));

      return $content;
    }

    /**
     * Initialize
     *
     * @param string $themeId
     * @param string $domain
     * @param string $pathLanguages
     */
    protected function init($themeId, $domain, $pathLanguages)
    {
      AbstractTheme::$domain = $domain;
      AbstractTheme::$themeId = $themeId;
      $this->addTextDomain($domain, $pathLanguages);
    }

    /**
     * Load text domain
     *
     * @param string $domain
     * @param string $path
     */
    protected function addTextDomain($domain, $path)
    {
      load_theme_textdomain($domain, $path);
      do_action('theme_textdomain_loaded');
    }

    /**
     * Set components after theme textdomain is loaded
     *
     * @param array $components
     */
    public function setComponents(array $components)
    {
      self::$components = array_merge(self::$components, $components);
    }

    /**
     * Set options
     *
     * @param array $options
     */
    public function setOptions(array $options)
    {
      self::$options = array_merge(self::$options, $options);
    }

    /**
     * Set shortcodes
     *
     * @param array $shortcodes
     */
    public function setShortcodes(array $shortcodes)
    {
      self::$shortcodes = array_merge(self::$shortcodes, $shortcodes);

      // check if there are shortcodes which should be added to tinymce
      $tinymceItems = array();
      foreach (self::$shortcodes as $shortcode) {
        if ($shortcode->tinymce) {
          $tinymceItems[] = array(
            'label'    => $shortcode->label,
            'id'       => $shortcode->identifier,
            'template' => $shortcode->tinymce,
          );
        }
      }

      // initialize tinymce submenu `shortcodes`
      if (count($tinymceItems) > 0) {
        $this->tinyShortcodes = new TinyMceSubmenu(
          'happy_shortcodes',
          __('Shortcodes', self::$domain),
          $tinymceItems
        );
      }
    }

    /**
     * Set tinymce color palette
     *
     * @param array $colors
     */
    public function setTinyMceColorPalette(array $colors)
    {
      // check if we have to swap key values
      $value = reset($colors);
      if (Helpers\Color::isHex($value)) {
        $colors = array_flip($colors);
      }

      // generate tinymce colors arg
      $tinymce_colors = '';
      foreach ($colors as $colorValue => $colorName) {
        $tinymce_colors .= '"' . preg_replace('/^#/', '', $colorValue) . '", "' . $colorName . '"';
        if (end($colors) !== $colorName) {
          $tinymce_colors .= ',';
        }
      }

      // add colors to tinymce before the object has initialized
      add_filter('tiny_mce_before_init', function ($init) use ($tinymce_colors) {
        $init['textcolor_map'] = '[' . $tinymce_colors . ']';

        return $init;
      });
    }

    /**
     * Set post types
     *
     * @param array $postTypes
     */
    public function setPostTypes(array $postTypes)
    {
      self::$postTypes = array_merge(self::$postTypes, $postTypes);
    }

    /**
     * Set scripts
     *
     * @param array $scripts
     */
    public function setScripts(array $scripts)
    {
      self::$scripts = array_merge(self::$scripts, $scripts);
    }

    /**
     * Set admin templates path
     *
     * @param string $path
     */
    public function setAdminTemplatesPath($path)
    {
      self::$adminTemplateDir = str_replace(get_template_directory() . '/', '', preg_replace('#[\/]?$#', '', $path));
    }

    /**
     * Set path to partials
     * When partials are placed in a seperate directory, this method enables you to use `get_template_part('content', 'test')`
     * instead of `get_template_part('dirname/content', 'test')`
     *
     * @param string $path
     */
    public function setPartialsPath($path)
    {
      $path = AbstractTheme::$partialPath = str_replace(get_template_directory() . '/', '', preg_replace('#[\/]?$#', '', $path));
      foreach (array('content', 'component', 'shortcode', 'sidebar', 'option') as $type) {
        add_action('get_template_part_' . $type, function ($slug, $name) use ($path) {
          if (!file_exists(get_template_directory() . "/{$slug}-{$name}.php")) {
            $templates[] = "{$path}/{$slug}-{$name}.php";
            locate_template($templates, true, false);
          }
        }, 10, 2);
      }

      // also search the partials folder for `content-searchform`
      add_filter('get_search_form', function ($form_output) use ($path) {
        if (file_exists(get_template_directory() . "/{$path}/content-searchform.php")) {
          ob_start();
          include(locate_template("{$path}/content-searchform.php"));
          $form_output = ob_get_clean();
        }

        return $form_output;
      });
    }

    /**
     * Get or output template part
     *
     * @param   string    $file
     * @param   array     $args
     * @param   bool|true $output Output html or return string
     * @return  string|null
     */
    public static function templatePart($file, array $args = array(), $output = true)
    {
      $html = null;

      // format file with extension
      $file = !preg_match('#\.php$#', $file) ? $file . '.php' : $file;

      // check if file exist, if so include template with passed arguments
      if (!file_exists(get_template_directory() . '/' . AbstractTheme::$partialPath . '/' . $file)) {
        Helpers\Log::log(sprintf(__('file %s does not exist in partials directory directory', self::$domain), $file));
        trigger_error(sprintf(__('file %s does not exist in partials directory', self::$domain), $file) . '. ' . Helpers\Log::getLastDebugTraceLine());
      } else {
        ob_start();
        extract($args);
        include(locate_template(AbstractTheme::$partialPath . '/' . $file));
        $html = ob_get_clean();
      }

      // echo the output when is enabled
      if ($output) {
        echo $html;
      }

      return $html;
    }

    /**
     * Get or output admin template
     *
     * @param   string       $file
     * @param   array        $args
     * @param   boolean|true $output Output html or return string
     * @return  null|string
     */
    public static function adminTemplate($file, array $args = array(), $output = true)
    {
      $html = null;

      // format file with extension
      $file = !preg_match('#\.php$#', $file) ? $file . '.php' : $file;

      // check if file exist, if so include template with passed arguments
      if (!file_exists(get_template_directory() . '/' . self::$adminTemplateDir . '/' . $file)) {
        Helpers\Log::log(sprintf(__('file %s does not exist in admin template directory', self::$domain), $file));
        trigger_error(sprintf(__('file %s does not exist in admin template directory', self::$domain), $file) . '. ' . Helpers\Log::getLastDebugTraceLine());
      } else {
        ob_start();
        extract($args);
        include(locate_template(self::$adminTemplateDir . '/' . $file));
        $html = ob_get_clean();
      }

      // echo the output when is enabled
      if ($output) {
        echo $html;
      }

      return $html;
    }

    /**
     * Get or output partial template
     *
     * @param string $file
     * @param array  $args
     * @param bool   $output
     * @return string
     */
    public static function partialTemplate($file, array $args = array(), $output = true)
    {
      $html = null;

      // format file with extension
      $file = !preg_match('#\.php$#', $file) ? $file . '.php' : $file;

      // check if file exist, if so include template with passed arguments
      if (!file_exists(get_template_directory() . '/' . AbstractTheme::$partialPath . '/' . $file)) {
        Helpers\Log::log(sprintf(__('file %s does not exist in given partials template directory', self::$domain), $file));
        trigger_error(sprintf(__('file %s does not exist in partials template directory', self::$domain), $file) . '. ' . Helpers\Log::getLastDebugTraceLine());
      } else {
        ob_start();
        extract($args);
        include(locate_template(AbstractTheme::$partialPath . '/' . $file));
        $html = ob_get_clean();
      }

      // echo the output when is enabled
      if ($output) {
        echo $html;
      }

      return $html;
    }

    /**
     * Register wp nav menu
     *
     * @param   string $location
     * @param   string $description [optional]
     * @param   array  $args        [optional]
     * @return \HappyFramework\Menu
     */
    public function registerMenu($location, $description = null, array $args = array())
    {
      return AbstractTheme::$menus[$location] = new \HappyFramework\Menu($location, $description, $args);
    }

    /**
     * Get registered menu
     *
     * @param $location
     * @return \HappyFramework\Menu
     */
    public static function getRegisteredMenu($location)
    {
      return AbstractTheme::$menus[$location];
    }

    /**
     * Get menu html
     *
     * @param   string $location
     * @param   array  $args
     * @return  string
     */
    public static function getMenuHtml($location, array $args = array())
    {
      /* @var \HappyFramework\Menu $menu */
      $menu = AbstractTheme::getRegisteredMenu($location);

      return $menu->html($args);
    }

    /**
     * Initialize theme support
     */
    public function initThemeSupport()
    {
      add_theme_support('automatic-feed-links');
      add_theme_support('html5', array('search-form', 'comment-form', 'comment-list'));
      add_theme_support('post-thumbnails');
    }

    /**
     * Add image size
     *
     * @param string $id
     * @param string $name
     * @param int    $width
     * @param int    $height
     * @param bool   $crop
     */
    public function addImageSize($id, $name, $width = 0, $height = 0, $crop = false)
    {
      // store image size
      if (!in_array($id, self::$imageSizes)) {
        array_push(self::$imageSizes, $id);
      }

      // add image size
      add_image_size($id, $width, $height, $crop);

      // add image size to image size names choose
      add_filter('image_size_names_choose', function ($sizes) use ($id, $name) {
        return array_merge($sizes, array(
          $id => $name
        ));
      });
    }

    /**
     * Set thumnbnail size
     *
     * @param int $width
     * @param int $height
     */
    public function setThumbnailSize($width, $height)
    {
      // set thumbnail size
      set_post_thumbnail_size($width, $height);

      // add thumbnail size to image size names choose
      add_filter('image_size_names_choose', function ($sizes) {
        return array_merge($sizes, array(
          'post-thumbnail' => __('Post Thumbnail', AbstractTheme::$domain),
        ));
      });
    }

    /**
     * Remove width and height attributes for images
     * Useful for responsive websites
     */
    public function removeImageWidthHeightAttributes()
    {
      add_filter('post_thumbnail_html', function ($html) {
        return preg_replace('/(width|height)="\d*"\s/', "", $html);
      });
      add_filter('image_send_to_editor', function ($html) {
        return preg_replace('/(width|height)="\d*"\s/', "", $html);
      });
    }

    /**
     * Initialize widgets
     */
    public function initWidgets()
    {
      Widgets::unregisterDefaultWidgets();
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
