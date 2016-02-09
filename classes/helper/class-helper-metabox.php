<?php

  namespace HappyFramework\Helpers;

  use HappyFramework\Abstracts\AbstractTheme;

  /**
   * Class Metabox
   *
   * @package HappyFramework\Helpers
   */
  class Metabox
  {
    const POSITION_SIDE        = 'side';
    const POSITION_NORMAL      = 'normal';
    const POSITION_ADVANCED    = 'advanced';
    const PRIORITY_LOW         = 'low';
    const PRIORITY_DEFAULT     = 'default';
    const PRIORITY_HIGH        = 'high';
    const TYPE_MULTIPLE_FIELDS = 'multipleFields';
    const TYPE_EDITOR          = 'editor';
    const TYPE_INPUTFIELD      = 'inputfield';
    const TYPE_SELECTBOX       = 'selectbox';
    const TYPE_CHECKBOX        = 'checkbox';
    const TYPE_RADIO           = 'radio';
    const TYPE_TEXT            = 'text';
    const TYPE_TEXTAREA        = 'textarea';
    const TYPE_EMAIL           = 'email';
    const TYPE_NUMBER          = 'number';
    const TYPE_WRAPPER_START   = 'wrapStart';
    const TYPE_WRAPPER_END     = 'wrapEnd';
    const TYPE_TITLE           = 'title';
    const TYPE_IMAGE           = 'image';
    const TYPE_FILE            = 'file';
    const TYPE_CUSTOM          = 'custom';
    const TYPE_COLOR_PICKER    = 'colorPicker';
    const TYPE_DATE_PICKER     = 'datePicker';

    public function __construct()
    {
      // add iris javascript in admin for colorpicker functionality
      add_action('admin_enqueue_scripts', function () {
        wp_enqueue_script('iris');
        wp_enqueue_script('jquery-ui-datepicker');
        wp_enqueue_script('underscore');
      });

      add_action('save_post', array('HappyFramework\Helpers\Metabox', 'savePostMeta'));
    }

    public static function savePostMeta($post_id)
    {
      if (!empty($_POST['postmeta']) && is_array($_POST['postmeta'])) {

        // first check for values
        // checkfor values are set in Metabox::checkbox methods
        if (!empty($_POST['postmeta']['checkfor'])) {
          foreach ($_POST['postmeta']['checkfor'] as $name => $value) {
            if (!array_key_exists($name, $_POST['postmeta'])) {
              delete_post_meta($post_id, $name);
            }
          }
        }

        // update post meta data for current post
        foreach ($_POST['postmeta'] as $key => $value) {
          update_post_meta($post_id, $key, $value);
        }
      }
    }

    /**
     * Print multiple fields in Metabox
     *
     * @param   \WP_Post $post
     * @param   array    $args
     * @return  string
     * @example
     *              array(
     *              array('type' => Metabox::TYPE_WRAPPER_START),
     *              array(
     *              'type' => Metabox::TYPE_TITLE,
     *              'label' => 'An introduction',
     *              ),
     *              array(
     *              'type' => Metabox::TYPE_TEXT,
     *              'name' => '_title',
     *              'label' => 'Title',
     *              'placeholder' => 'Fill in a title',
     *              'description' => 'When leaved empty, title will be hidden',
     *              ),
     *              array('type' => Metabox::TYPE_WRAPPER_END),
     *              )
     */
    public static function multipleFields($post, array $args)
    {
      $title = $args['title'];
      $fields = $args['args'];

      foreach ($fields as $key => $field) {
        switch ($field['type']) {
          case Metabox::TYPE_TEXT:
          case Metabox::TYPE_EMAIL:
          case Metabox::TYPE_NUMBER:
          case Metabox::TYPE_INPUTFIELD:
            Metabox::inputfield($post, $field);
            break;
          case Metabox::TYPE_TEXTAREA:
            Metabox::textarea($post, $field);
            break;
          case Metabox::TYPE_EDITOR:
            Metabox::editor($post, $field);
            break;
          case Metabox::TYPE_WRAPPER_START:
            Metabox::wrapStart();
            break;
          case Metabox::TYPE_WRAPPER_END:
            Metabox::wrapEnd();
            break;
          case Metabox::TYPE_TITLE:
            Metabox::title($post, $field);
            break;
          case Metabox::TYPE_CHECKBOX:
            Metabox::checkbox($post, $field);
            break;
          case Metabox::TYPE_IMAGE:
            Metabox::image($post, $field);
            break;
          case Metabox::TYPE_FILE:
            Metabox::file($post, $field);
            break;
          case Metabox::TYPE_SELECTBOX:
            Metabox::selectbox($post, $field);
            break;
          case Metabox::TYPE_RADIO:
            Metabox::radioblocks($post, $field);
            break;
          case Metabox::TYPE_COLOR_PICKER:
            Metabox::colorPicker($post, $field);
            break;
          case Metabox::TYPE_DATE_PICKER:
            Metabox::datePicker($post, $field);
            break;
          case Metabox::TYPE_CUSTOM:
            Metabox::custom($post, $field);
            break;
        }
      }
    }

    /**
     * Add wrapper start
     *
     * @return string
     */
    public static function wrapStart()
    {
      ?><div class="metabox-wrapper" xmlns="http://www.w3.org/1999/html"><?php
    }

    /**
     * Add wrapper end
     *
     * @return string
     */
    public static function wrapEnd()
    {
      ?></div><?php
    }

    /**
     * Add a title
     *
     * @param   \WP_Post $post
     * @param   array    $args
     * @return  string
     * @example:
     *            array(
     *            'type' => Metabox::TYPE_TITLE,
     *            'label' => 'This is the title',
     *            'description' => 'A short description of the title'  // [optional]
     *            )
     */
    public static function title($post, array $args)
    {
      $args = !empty($args['args']) ? $args['args'] : $args; ?>
      <?php if (!empty($args['label'])): ?><h3 class="metabox-title"><?php echo $args['label'] ?></h3><?php endif; ?>
      <?php if (!empty($args['description'])): ?>
      <span class="description"><?php echo $args['description'] ?></span>
    <?php endif;
    }

    /**
     * Add a color picker
     *
     * @param   \WP_Post $post
     * @param   array    $args
     * @return  string
     * @example
     *            array(
     *            'type' => Metabox::TYPE_COLOR_PICKER,
     *            'name' => '_color',
     *            'label' => 'Choose a color',
     *            'colors' => array('#ff0000', '#00ff00', '#0000ff') // [optional, when not provided, all colors can be picked]
     *            'default' => '#ff0000' // [optional, default #ffffff],
     *            'iris_options' => array(), // [optional]
     *            'description' => 'Select a color will affect the background color of the site'
     *            )
     */
    public static function colorPicker($post, array $args)
    {
      $id = 'color_picker_' . uniqid();
      $args = !empty($args['args']) ? $args['args'] : $args; ?>
      <div class="metabox-field">
      <?php if (!empty($args['label'])): ?><label><?php echo $args['label'] ?></label><?php endif; ?>
      <input
        type="text"
        name="postmeta[<?php echo esc_attr($args['name']) ?>]"
        id="<?php echo $id ?>"
        <?php if (array_key_exists('placeholder', $args)): ?>placeholder="<?php echo esc_attr($args['placeholder']) ?>"<?php endif; ?>
        value="<?php echo get_post_meta($post->ID, $args['name'], true) ?: (!empty($args['default']) ? $args['default'] : '') ?>"
        />
      <?php if (!empty($args['description'])): ?>
      <span class="description"><?php echo $args['description'] ?></span>
    <?php endif; ?>

      <?php /* print javascript inline colorpicker */ ?>
      <?php ob_start(); ?>
      <script type="application/javascript">
        (function ($) {
          'use strict';

          $(document).ready(function () {
            var irisSettings = {};

            <?php /* Add colors to iris settings */ ?>
            <?php if (!empty($args['colors'])): ?>
            irisSettings['palettes'] = JSON.parse('<?php echo json_encode($args['colors']) ?>');
            <?php endif; ?>

            <?php /* Merge iris settings */ ?>
            <?php if (!empty($args['iris_options'])): ?>
            irisSettings = _.extend(irisSettings, JSON.parse('<?php echo json_encode($args['iris_options']) ?>'));
            <?php endif; ?>

            <?php /* attach the iris instance */ ?>
            $('input#<?php echo $id ?>').iris(irisSettings);
          });
        })(jQuery);
      </script>
      <?php $script = ob_get_clean();
      echo trim(preg_replace('#[\r\n]+|[\s]{2,}#', '', $script)) ?>
      </div><?php
    }

    /**
     * Print a datepicker field
     *
     * @param   \WP_Post $post
     * @param   array    $args
     * @return  string
     * @example
     *            array(
     *            'type' => Metabox::TYPE_DATE_PICKER,
     *            'name' => '_date',
     *            'label' => 'Choose a date',
     *            'placeholder' => 'Choose a date',
     *            'description' => 'When leaved empty, no date will be printed',
     *            'datepicker_options' => array()
     *            )
     */
    public static function datePicker($post, array $args)
    {
      $id = 'date_picker_' . uniqid();
      $args = !empty($args['args']) ? $args['args'] : $args; ?>

      <div class="metabox-field">
      <?php if (!empty($args['label'])): ?><label><?php echo $args['label'] ?></label><?php endif; ?>
      <input
        type="text"
        name="postmeta[<?php echo esc_attr($args['name']) ?>]"
        id="<?php echo $id ?>"
        <?php if (array_key_exists('placeholder', $args)): ?>placeholder="<?php echo esc_attr($args['placeholder']) ?>"<?php endif; ?>
        value="<?php echo get_post_meta($post->ID, $args['name'], true) ?: (!empty($args['default']) ? $args['default'] : '') ?>"
        />
      <?php if (!empty($args['description'])): ?>
      <span class="description"><?php echo $args['description'] ?></span>
    <?php endif; ?>

      <?php /* print javascript inline colorpicker */ ?>
      <?php ob_start(); ?>
      <script type="application/javascript">
        (function ($) {
          'use strict';

          $(document).ready(function () {
            var datepickerSettings = {
              monthNames:      JSON.parse('<?php echo json_encode(Date::getMonthNames()) ?>'),
              monthNamesShort: JSON.parse('<?php echo json_encode(Date::getMonthShortNames()) ?>'),
              dayNames:        JSON.parse('<?php echo json_encode(Date::getDayNames()) ?>'),
              dayNamesShort:   JSON.parse('<?php echo json_encode(Date::getDayShortNames()) ?>'),
              dayNamesMin:     JSON.parse('<?php echo json_encode(Date::getDayShortNames()) ?>'),
              dateFormat:      'dd-mm-yy',
              firstDay:        1
            };

            <?php /* Merge datepicker settings */ ?>
            <?php if (!empty($args['datepicker_options'])): ?>
            datepickerSettings = _.extend(datepickerSettings, JSON.parse('<?php echo json_encode($args['datepicker_options']) ?>'));
            <?php endif; ?>

            $('input#<?php echo $id ?>').datepicker(datepickerSettings);
          });
        })(jQuery);
      </script>
      <?php $script = ob_get_clean();
      echo trim(preg_replace('#[\r\n]+|[\s]{2,}#', '', $script)) ?>
      </div><?php
    }

    /**
     * Print an inputfield
     *
     * @param   \WP_Post $post
     * @param   array    $args
     * @return  string
     * @example:
     *            array(
     *            'name' => '_email',
     *            'type' => 'email',
     *            'label' => 'Your email address', // [optional]
     *            'placeholder' => 'xx@xxx.xx', // [optional]
     *            'description' => 'This email address will not be visible in the frontend' // [optional],
     *            'attributes' => array('maxlength' => 20)
     *            )
     */
    public static function inputfield($post, array $args)
    {
      $args = !empty($args['args']) ? $args['args'] : $args; ?>
      <div class="metabox-field">
      <?php if (!empty($args['label'])): ?><label><?php echo $args['label'] ?></label><?php endif; ?>
      <input
        type="<?php echo $args['type'] ?>"
        name="postmeta[<?php echo esc_attr($args['name']) ?>]"
        <?php if (array_key_exists('placeholder', $args)): ?>placeholder="<?php echo esc_attr($args['placeholder']) ?>"<?php endif; ?>
        value="<?php echo !empty($args['value']) ? $args['value'] : get_post_meta($post->ID, $args['name'], true) ?>"
      <?php foreach ((!empty($args['attributes']) ? $args['attributes'] : array()) as $attributeName => $attributeValue): ?>
      <?php echo $attributeName ?>="<?php echo $attributeValue ?>"
    <?php endforeach; ?> />
      <?php if (!empty($args['description'])): ?>
      <span class="description"><?php echo $args['description'] ?></span>
    <?php endif; ?>
      </div><?php
    }

    /**
     * Print a textarea
     *
     * @param \WP_Post $post
     * @param array    $args
     * @return string
     * @example:
     *            array(
     *            'name' => '_message',
     *            'label' => 'Enter your message', // [optional]
     *            'placeholder' => 'Type here your message', // [optional]
     *            'description' => 'This message will not be visible in the frontend' // [optional],
     *            'attributes' => array('cols' => 20)
     *            )
     */
    public static function textarea($post, array $args)
    {
      $args = !empty($args['args']) ? $args['args'] : $args; ?>
      <div class="metabox-field">
      <?php if (!empty($args['label'])): ?><label><?php echo $args['label'] ?></label><?php endif; ?>
      <textarea
      <?php if (!isset($args['attributes']) && !isset($args['attributes']['cols'])): ?>cols="50"<?php endif; ?>
      <?php if (!isset($args['attributes']) && !isset($args['attributes']['rows'])): ?>rows="5"<?php endif; ?>
      name="postmeta[<?php echo esc_attr($args['name']) ?>]"
      <?php if (array_key_exists('placeholder', $args)): ?>placeholder="<?php echo esc_attr($args['placeholder']) ?>"<?php endif; ?>
      <?php foreach ((!empty($args['attributes']) ? $args['attributes'] : array()) as $attributeName => $attributeValue): ?>
      <?php echo $attributeName ?>="<?php echo $attributeValue ?>"
    <?php endforeach; ?>>
      <?php echo !empty($args['value']) ? $args['value'] : get_post_meta($post->ID, $args['name'], true) ?>
      </textarea>
      <?php if (!empty($args['description'])): ?>
      <span class="description"><?php echo $args['description'] ?></span>
    <?php endif; ?>
      </div><?php
    }

    /**
     * Print editor field
     *
     * @param   \WP_Post $post
     * @param   array    $args
     * @return  string
     * @example:
     *            array(
     *            'type' => Metabox::TYPE_EDITOR,
     *            'name' => '_content',
     *            'label' => 'Content', // [optional]
     *            'quicktags' => true,  // [optional]
     *            'media_buttons' => true, // [optional]
     *            'description' => 'You can use shortcode [example] in this editor field' // [optional]
     *            )
     */
    public static function editor($post, array $args)
    {
      $args = !empty($args['args']) ? $args['args'] : $args; ?>
      <div class="metabox-field">
      <?php if (!empty($args['label'])): ?><label><?php echo $args['label'] ?></label><?php endif; ?>
      <?php
      wp_editor(
        !empty($args['value']) ? $args['value'] : get_post_meta($post->ID, $args['name'], true),
        'metabox_editor_' . uniqid(),
        array(
          'textarea_name' => 'postmeta[' . $args['name'] . ']',
          'tinymce'       => array('height' => 150),
          'quicktags'     => (isset($args['quicktags']) && $args['quicktags'] === false) ? false : true,
          'media_buttons' => (isset($args['media_buttons']) && $args['media_buttons'] === false) ? false : true,
        )
      ); ?>
      <?php if (!empty($args['description'])): ?>
      <span class="description"><?php echo $args['description'] ?></span>
    <?php endif; ?>
      </div><?php
    }

    /**
     * Print checkbox field
     *
     * @param   \WP_Post $post
     * @param   array    $args
     * @return  string
     * @example:
     *            array(
     *            'type' => Metabox::TYPE_CHECKBOX,
     *            'name' => '_show_label',
     *            'label' => 'Show label',
     *            'description' => 'Show label in the frontend' // [optional]
     *            )
     */
    public static function checkbox($post, array $args)
    {
      $args = !empty($args['args']) ? $args['args'] : $args;
      $id = 'metabox_checkbox_' . uniqid(); ?>
      <div class="metabox-field">
      <label for="<?php echo esc_attr($id) ?>" class="no-after">
        <input name="postmeta[checkfor][<?php echo $args['name'] ?>]" type="hidden" value="1"/>
        <input type="checkbox" name="postmeta[<?php echo esc_attr($args['name']) ?>]" value="1"
               id="<?php echo esc_attr($id) ?>" <?php checked(get_post_meta($post->ID, $args['name'], true), '1') ?> />
        <?php echo $args['label'] ?>
      </label>
      <?php if (!empty($args['description'])): ?>
      <span class="description"><?php echo $args['description'] ?></span>
    <?php endif; ?>
      </div><?php
    }

    /**
     * Print radio blocks
     *
     * @param   \WP_Post $post
     * @param   array    $args
     * @return  string
     * @example:
     *                  array(
     *                  'type' => Metabox::TYPE_RADIO,
     *                  'values' => array(
     *                  'value_1' => 'label 1',
     *                  'value_2' => 'label 2',
     *                  'value_3' => 'label 3',
     *                  ),
     *                  'default' => 'value_2',
     *                  'label' => 'Select a render method', // [optional]
     *                  'description' => 'Select a value will affect rendering the page' // [optional]
     *                  )
     */
    public static function radioblocks($post, array $args)
    {
      $args = !empty($args['args']) ? $args['args'] : $args;
      $current = get_post_meta($post->ID, $args['name'], true);
      if ($current === '') {
        $current = !empty($args['default']) ? $args['default'] : key($args['values']);
      }
      ?>
      <div class="metabox-field">
      <?php if (!empty($args['label'])): ?><label><?php echo $args['label'] ?></label><?php endif; ?>
      <?php foreach ($args['values'] as $value => $text): ?>
      <label for="radio_<?php echo sanitize_html_class($value) ?>">
        <input type="radio" name="postmeta[<?php echo esc_attr($args['name']) ?>]"
               id="radio_<?php echo sanitize_html_class($value) ?>" <?php checked(get_post_meta($post->ID, $args['name'], true), $value) ?>
               value="<?php echo esc_attr($value) ?>"/>
        <?php echo $text ?>
      </label>
    <?php endforeach; ?>
      <?php if (!empty($args['description'])): ?>
      <span class="description"><?php echo $args['description'] ?></span>
    <?php endif; ?>
      </div><?php
    }

    /**
     * Add custom html
     *
     * @note   : You should provide a custom save_post hook to save data, this method allows you to print your callback
     * @param   \WP_Post $post
     * @param   array    $args
     * @return  string
     * @example:
     *                  array(
     *                  'type' => Metabox::TYPE_CUSTOM,
     *                  'label' => 'some custom html' // [optional],
     *                  'callback' => function(){
     *                  echo '<span>some html content</span>';
     *                  }
     *                  )
     */
    public static function custom($post, array $args)
    {
      $args = !empty($args['args']) ? $args['args'] : $args;
      ?>
      <div class="metabox-field">
      <?php if (!empty($args['label'])): ?><label><?php echo $args['label'] ?></label><?php endif; ?>
      <?php if (!empty($args['callback'])): call_user_func($args['callback']); endif; ?>
      </div><?php
    }

    /**
     * Print image field
     *
     * @param   \WP_Post $post
     * @param   array    $args
     * @return  string
     * @example
     *            array(
     *            'type' => Metabox::TYPE_IMAGE,
     *            'label' => 'Image',
     *            'name' => 'sidebanner_image',
     *            'preview_size' => 'post-thumbnail',
     *            'update_size' => 'post-thumbnail',
     *            'frame_title' => 'Select an image',
     *            'frame_button_label' => 'Use image',
     *            'delete_label' => 'Delete image',
     *            'description' => 'The image is only visible when display type is set to Show an image and a button',
     *            )
     */
    public static function image($post, array $args)
    {
      $args = !empty($args['args']) ? $args['args'] : $args;
      $image_data = get_post_meta($post->ID, $args['name'], true);
      $attachment = $image_data && !empty($image_data['attachment']) ? $image_data['attachment'] : null;
      $name = 'postmeta[' . $args['name'] . ']';

      // attachment
      $attachment_id_value = $attachment && !empty($attachment['id']) ? $attachment['id'] : '';
      $attachment_url_value = $attachment && !empty($attachment['url']) ? $attachment['url'] : '';

      // translations
      $delete_label = !empty($args['delete_label']) ? $args['delete_label'] : __('Delete selected image', AbstractTheme::$domain);
      $frame_title = !empty($args['frame_title']) ? $args['frame_title'] : __('Select Image', AbstractTheme::$domain);
      $frame_button_label = !empty($args['frame_button_label']) ? $args['frame_button_label'] : __('Use this image', AbstractTheme::$domain);

      // preview
      $preview_url = !empty($attachment_id_value) && !empty($args['preview_size']) ? wp_get_attachment_image_src($attachment_id_value, $args['preview_size']) : $attachment_url_value;
      if (is_array($preview_url) && count($preview_url) > 0) {
        $preview_url = $preview_url[0];
      } ?>

      <div class="metabox-field">
        <?php if (!empty($args['label'])): ?><label><?php echo $args['label'] ?></label><?php endif; ?>

        <div class="media-library-instance" data-bind="
                with: $root.medialibrary.newInstance.bind($data, {
                    previewUrl:             '<?php echo !empty($preview_url) ? $preview_url : '' ?>',
                    attachmentUrl:          '<?php echo $attachment_url_value ?>',
                    attachmentId:           '<?php echo $attachment_id_value ?>',
                    <?php if (!empty($args['preview_size'])): ?>imageSizePreview: '<?php echo $args['preview_size'] ?>',<?php endif; ?>
                    <?php if (!empty($args['update_size'])): ?>imageSizeAttachment: '<?php echo $args['update_size'] ?>',<?php endif; ?>
                    frameTitle:             '<?php echo Formatting::toAttributeString($frame_title) ?>',
                    frameButtonText:        '<?php echo Formatting::toAttributeString($frame_button_label) ?>'
                })
            ">

          <?php // image preview
          ?>
          <div class="media-library-preview-container" data-bind="visible: showPreview">
            <img src="" alt="" data-bind="attr: {src: previewUrl}"/>
          </div>

          <?php // delete button
          ?>
          <a class="button media-library-remove" href="#" data-bind="visible: showDeleteButton, click: removeAttachment">
            <?php echo $delete_label ?>
          </a>

          <?php // select button
          ?>
          <a class="button media-library-add" href="#" data-bind="visible: showSelectButton, click: addAttachment">
            <?php _e('Select image', AbstractTheme::$domain) ?>
          </a>

          <?php // hidden input values
          ?>
          <input name="postmeta[<?php echo esc_attr($args['name']) ?>][attachment][id]" type="hidden" data-bind="value: attachmentId"/>
          <input name="postmeta[<?php echo esc_attr($args['name']) ?>][attachment][url]" type="hidden" data-bind="value: attachmentUrl"/>

          <?php // description
          ?>
          <?php if (!empty($args['description'])): ?>
            <span class="description"><?php echo $args['description'] ?></span>
          <?php endif; ?>
        </div>
      </div><!-- .metabox-field --><?php
    }

    /**
     * Print select file box
     *
     * @param       $post
     * @param array $args
     * @return string
     * @example
     *            array(
     *            'type' => Metabox::TYPE_FILE,
     *            'label' => 'Select pdf file',
     *            'mime_type' => 'pdf',
     *            'name' => '_document',
     *            'frame_title' => 'Select a pdf file',
     *            'frame_button_label' => 'Use selected pdf file',
     *            'delete_label' => 'Unset pdf file',
     *            'description' => 'You can select a pdf file here',
     *            )
     */
    public static function file($post, array $args)
    {
      $args = !empty($args['args']) ? $args['args'] : $args;
      $data = get_post_meta($post->ID, $args['name'], true);
      $attachment = $data && !empty($data['attachment']) ? $data['attachment'] : null;
      $name = 'postmeta[' . $args['name'] . ']';

      // attachment
      $attachment_id = $attachment && !empty($attachment['id']) ? $attachment['id'] : '';
      $attachment_url = $attachment && !empty($attachment['url']) ? $attachment['url'] : '';

      // filename
      $filename = $attachment && !empty($attachment['filename']) ? $attachment['filename'] : '';

      // translations
      $delete_label = !empty($args['delete_label']) ? $args['delete_label'] : __('Delete current selected image', AbstractTheme::$domain);
      $frame_title = !empty($args['frame_title']) ? $args['frame_title'] : __('Select Image', AbstractTheme::$domain);
      $frame_button_label = !empty($args['frame_button_label']) ? $args['frame_button_label'] : __('Use this image', AbstractTheme::$domain); ?>

      <div class="metabox-field">
        <?php if (!empty($args['label'])): ?><label><?php echo $args['label'] ?></label><?php endif; ?>

        <div class="media-library-instance" data-bind="
                with: $root.medialibrary.newInstance.bind($data, {
                    type:                   '<?php echo !empty($args['mime_type']) ? $args['mime_type'] : 'post' ?>',
                    attachmentId:           '<?php echo $attachment_id ?>',
                    attachmentUrl:          '<?php echo $attachment_url ?>',
                    frameTitle:             '<?php echo Formatting::toAttributeString($frame_title) ?>',
                    frameButtonText:        '<?php echo Formatting::toAttributeString($frame_button_label) ?>',
                    fileName:               '<?php echo Formatting::toAttributeString($filename) ?>'
                })
            ">
          <?php // filename
          ?>
          <div class="media-library-preview-container" data-bind="visible: showFilename">
            <input name="<?php echo esc_attr($name . '[attachment][filename]') ?>" type="hidden" data-bind="value: fileName"/>
            <span class="filename-preview" data-bind="text: fileName"></span>
          </div>

          <?php // delete button
          ?>
          <a class="button media-library-remove" href="#" data-bind="visible: showDeleteButton, click: removeAttachment">
            <?php echo $delete_label ?>
          </a>

          <?php // select button
          ?>
          <a class="button media-library-add" href="#" data-bind="visible: showSelectButton, click: addAttachment">
            <?php _e('Select file', AbstractTheme::$domain) ?>
          </a>

          <?php // hidden input values
          ?>
          <input name="<?php echo esc_attr($name . '[attachment][id]'); ?>" type="hidden" data-bind="value: attachmentId"/>
          <input name="<?php echo esc_attr($name . '[attachment][url]'); ?>" type="hidden" data-bind="value: attachmentUrl"/>

          <?php // description
          ?>
          <?php if (!empty($args['description'])): ?>
            <span class="description"><?php echo $args['description'] ?></span>
          <?php endif; ?>
        </div>
      </div><!-- .metabox-field --><?php
    }

    /**
     * Print select field
     *
     * @param   \WP_Post $post
     * @param   array    $args
     * @return  string
     * @example:
     *                  array(
     *                  'type' => Metabox::TYPE_SELECTBOX,
     *                  'name' => '_rendering_method',
     *                  'options' => array(
     *                  'option_value_1' => 'option value 1',
     *                  'option_value_2' => 'option value 2',
     *                  'option_value_3' => 'option value 3',
     *                  )
     *                  'label' => 'Select a method', // [optional],
     *                  'description' => 'When there is no method selected, nothing will be rendered' // [optional]
     *                  )
     */
    public static function selectbox($post, array $args)
    {
      $args = !empty($args['args']) ? $args['args'] : $args; ?>
      <div class="metabox-field">
      <?php if (!empty($args['label'])): ?><label><?php echo $args['label'] ?></label><?php endif; ?>
      <select name="postmeta[<?php echo esc_attr($args['name']) ?>]">
        <?php foreach ($args['options'] as $option_value => $option_label): ?>
          <option value="<?php echo esc_attr($option_value) ?>" <?php selected($option_value, get_post_meta($post->ID, $args['name'], true)) ?>>
            <?php echo $option_label ?>
          </option>
        <?php endforeach; ?>
      </select>
      <?php if (!empty($args['description'])): ?>
      <span class="description"><?php echo $args['description'] ?></span>
    <?php endif; ?>
      </div><?php
    }
  }
