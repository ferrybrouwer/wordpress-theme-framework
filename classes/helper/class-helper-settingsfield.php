<?php

  namespace HappyFramework\Helpers;

  use HappyFramework\Abstracts\AbstractTheme as Theme;


  /**
   * Class Settingsfield
   * Generate settings fields
   *
   * @package HappyFramework\Helpers
   * @author  Ferry Brouwer <ferry@happy-online.nl>
   */
  class Settingsfield
  {

    /**
     * Print media library image selection field
     *
     * @param array $args
     * @example
     *          array(
     *            'optionKey' => 'header_options',
     *            'name' => 'banner',
     *            'preview_size' => 'post-thumbnail',
     *            'update_size' => 'post-thumbnail',
     *            'frame_title' => 'Select an image',
     *            'frame_button_label' => 'Use image',
     *            'delete_label' => 'Delete image',
     *            'description' => 'The image is only visible when display type is set to Show an image and a button',
     *          )
     */
    public static function image(array $args)
    {
      $optionKey = $args['optionKey'];
      $optionData = get_option($optionKey);

      $attachment = $optionData && !empty($optionData[$args['name']]) ? $optionData[$args['name']] : null;
      $attachment = $attachment && !empty($attachment['attachment']) ? $attachment['attachment'] : null;

      // attachment
      $attachment_id_name = $optionKey . '[' . $args['name'] . '][attachment][id]';
      $attachment_url_name = $optionKey . '[' . $args['name'] . '][attachment][url]';
      $attachment_id_value = $attachment && !empty($attachment['id']) ? $attachment['id'] : '';
      $attachment_url_value = $attachment && !empty($attachment['url']) ? $attachment['url'] : '';

      // translations
      $delete_label = !empty($args['delete_label']) ? $args['delete_label'] : __('Delete current selected image', Theme::$domain);
      $frame_title = !empty($args['frame_title']) ? $args['frame_title'] : __('Select Image', Theme::$domain);
      $frame_button_label = !empty($args['frame_button_label']) ? $args['frame_button_label'] : __('Use this image', Theme::$domain);

      // preview
      $preview_url = !empty($attachment_id_value) && !empty($args['preview_size']) ? wp_get_attachment_image_src($attachment_id_value, $args['preview_size']) : $attachment_url_value;
      if (is_array($preview_url) && count($preview_url) > 0) {
        $preview_url = $preview_url[0];
      } ?>
      <div class="metabox-field">
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
            <?php _e('Delete selected image', Theme::$domain) ?>
          </a>

          <?php // select button
          ?>
          <a class="button media-library-add" href="#" data-bind="visible: showSelectButton, click: addAttachment">
            <?php _e('Select image', Theme::$domain) ?>
          </a>

          <?php // hidden input values
          ?>
          <input name="<?php echo esc_attr($attachment_id_name); ?>" type="hidden" data-bind="value: attachmentId"/>
          <input name="<?php echo esc_attr($attachment_url_name); ?>" type="hidden" data-bind="value: attachmentUrl"/>

          <?php // description
          ?>
          <?php if (!empty($args['description'])): ?>
            <span class="description">
                    <?php echo $args['description'] ?>
                </span>
          <?php endif; ?>
        </div>
      </div><!-- .metabox-field --><?php
    }

    /**
     * Callback function from 'add_settings_field'
     * Output wordpress tiny mce editor
     *
     * @param  array $args
     * @example:
     *              array(
     *                'optionKey' => 'header_options',
     *                'name' => 'editor',
     *                'description' => 'Leave blank if you dont know',
     *                'editor' => array()
     *              )

     */
    public static function editor(array $args)
    {
      // option values
      $options = (array)get_option($args['optionKey']);
      $name = (string)$args['optionKey'] . '[' . $args['name'] . ']';
      $value = (string)!empty($options[$args['name']]) ? $options[$args['name']] : '';
      $editor_args = (array)((isset($args['editor']) && is_array($args['editor'])) ? $args['editor'] : array());
      $editor_args['textarea_name'] = (string)$name;

      // print the editor
      wp_editor($value, sanitize_key($name), $editor_args);

      // add description if provided
      if (!empty($args['description'])): ?><span class="inputfield description"><?php echo $args['description']; ?></span><?php endif;
    }

    /**
     * Callback function from 'add_settings_field'
     * Output a text input field
     *
     * @param array $args
     * @example:
     *          array(
     *            'optionKey' => 'header_options',
     *            'name' => 'phone',
     *            'placeholder' => 'Vul je telefoonnummer in',
     *            'type' => 'tel',
     *            'description' => 'Vul je telefoonnummer in',
     *            'attributes' => array(
     *              'maxlength' => '10'
     *            )
     *          )
     */
    public static function textfield(array $args)
    {
      $options = (array)get_option($args['optionKey']);
      $name = (string)$args['optionKey'] . '[' . $args['name'] . ']';
      $value = (string)!empty($options[$args['name']]) ? $options[$args['name']] : '';
      $type = (string)!empty($args['type']) ? $args['type'] : 'text';
      $attributes = (array)!empty($args['attributes']) ? $args['attributes'] : array(); ?>

      <input type="<?php echo esc_attr($type) ?>" name="<?php echo esc_attr($name) ?>" value="<?php echo esc_attr($value) ?>"
        <?php if (!empty($args['placeholder'])): ?>placeholder="<?php echo $args['placeholder'] ?>"<?php endif; ?>
        <?php foreach ($attributes as $attributeName => $attributeValue): ?>
          <?php echo $attributeName ?>="<?php echo $attributeValue ?>"
        <?php endforeach; ?>/>

      <?php if (!empty($args['description'])): ?><span class="inputfield description"><?php echo $args['description']; ?></span><?php endif;
    }

    /**
     * Callback function from 'add_settings_field'
     * Output a textarea field
     *
     * @param array $args
     * @example:
     *          array(
     *            'optionKey' => 'header_options',
     *            'name' => 'message',
     *            'placeholder' => 'Vul je bericht in',
     *            'description' => 'Vul je bericht in'
     *          )
     */
    public static function textarea(array $args)
    {
      $options = (array)get_option($args['optionKey']);
      $name = (string)$args['optionKey'] . '[' . $args['name'] . ']';
      $value = (string)!empty($options[$args['name']]) ? $options[$args['name']] : ''; ?>

      <textarea name="<?php echo esc_attr($name) ?>" id="" cols="30" rows="5"
                <?php if (!empty($args['placeholder'])): ?>placeholder="<?php echo $args['placeholder'] ?>" <?php endif; ?>><?php echo $value ?></textarea>

      <?php if (!empty($args['description'])): ?><span class="inputfield description"><?php echo $args['description']; ?></span><?php endif;
    }

    /**
     * Callback function from 'add_settings_field'
     * Output a dropdown pages field
     *
     * @param array $args
     * @example:
     *          array(
     *           'optionKey' => 'header_options',
     *           'name' => 'page',
     *           'optionNone' => ' --- Select --- ',
     *           'description' => 'Selecteer een pagina'
     *          )
     */
    public static function dropdownpages(array $args)
    {
      $options = (array)get_option($args['optionKey']);
      $name = (string)$args['optionKey'] . '[' . $args['name'] . ']';
      $value = (string)!empty($options[$args['name']]) ? $options[$args['name']] : '';

      wp_dropdown_pages(
        array(
          'name'              => $name,
          'selected'          => $value,
          'show_option_none'  => !empty($args['optionNone']) ? $args['optionNone'] : '',
          'option_none_value' => 0
        )
      );

      if (!empty($args['description'])): ?><span class="inputfield description"><?php echo $args['description'] ?></span><?php endif;
    }

    /**
     * Callback function from 'add_settings_field'
     * Output a select element field
     *
     * @param array $args
     * @example:
     *              array(
     *                'optionKey' => 'header_options',
     *                'name' => 'food',
     *                'optionNone' => ' --- Select --- ',
     *                'options => array(
     *                  'value 1' => 'label 1',
     *                  'value 2' => 'label 2',
     *                )
     *                'description => 'Select a food',
     *              )
     */
    public static function select(array $args)
    {
      $options = (array)get_option($args['optionKey']);
      $name = (string)$args['optionKey'] . '[' . $args['name'] . ']';
      $value = (string)!empty($options[$args['name']]) ? $options[$args['name']] : ''; ?>

      <select name="<?php echo esc_attr($name) ?>" id="">
        <?php if (!empty($args['optionNone'])): ?><option value="none"><?php echo $args['optionNone'] ?></option><?php endif; ?>

        <?php foreach ($args['options'] as $_key => $_value): ?>
          <option value="<?php echo esc_attr($_key) ?>" <?php selected($value, $_key) ?>><?php echo $_value ?></option>
        <?php endforeach; ?>
      </select><?php

      if (!empty($args['description'])): ?><span class="inputfield description"><?php echo $args['description'] ?></span><?php endif;
    }

    /**
     * Callback function from 'add_settings_field'
     * Output a checkbox element wrapped inside a label
     *
     * @param array $args
     * @example:
     *          array(
     *            'optionKey' => 'header_options',
     *            'name' => 'show_banner'
     *          )
     */
    public static function checkbox(array $args)
    {
      $options = (array)get_option($args['optionKey']);
      $name = (string)$args['optionKey'] . '[' . $args['name'] . ']';
      $value = (string)!empty($options[$args['name']]) ? $options[$args['name']] : ''; ?>

      <label for="<?php echo esc_attr($name) ?>">
        <input type="checkbox" name="<?php echo esc_attr($name) ?>" id="<?php echo esc_attr($name) ?>" value="1" <?php checked($value, '1') ?> />
        <?php echo $args['label'] ?>
      </label>

      <?php if (!empty($args['description'])): ?><span class="inputfield description"><?php echo $args['description'] ?></span><?php endif;
    }

    /**
     * Callback function from `add_settings_field`
     * Output a colorpicker input field
     *
     * @param array $args
     * @example:
     *          array(
     *            'optionKey' => 'header_options',
     *            'name' => 'background_color',
     *            'placeholder' => 'Select a color',
     *            'colors' => array('ff0000', '00ff00', '0000ff') // add color palettes,
     *            'default' => '#ff0000' // default selected color,
     *            'irisOptions' => array() // see http://automattic.github.io/Iris/,
     *            'description' => 'The color will affect the background'
     *          )
     */
    public static function colorpicker(array $args)
    {
      $options = (array)get_option($args['optionKey']);
      $name = (string)$args['optionKey'] . '[' . $args['name'] . ']';
      $value = (string)!empty($options[$args['name']]) ? $options[$args['name']] : '';
      $id = 'color_picker_' . uniqid(); ?>

      <input id="<?php echo esc_attr($id) ?>" type="text" name="<?php echo esc_attr($name) ?>" value="<?php echo esc_attr($value) ?>"
             <?php if (!empty($args['placeholder'])): ?>placeholder="<?php echo $args['placeholder'] ?>" <?php endif; ?> />

      <?php if (!empty($args['description'])): ?><span class="inputfield description"><?php echo $args['description'] ?></span><?php endif; ?>

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

            <?php /* Set default color when is provided and value is empty */ ?>
            <?php if (!empty($args['default']) && empty($value)): ?>
            irisSettings['color'] = '<?php echo Color::sanitizeHex($args['default']) ?>';
            <?php endif; ?>

            <?php /* Merge iris settings */ ?>
            <?php if (!empty($args['irisOptions'])): ?>
            irisSettings = _.extend(irisSettings, JSON.parse('<?php echo json_encode($args['irisOptions']) ?>'));
            <?php endif; ?>

            <?php /* attach the iris instance */ ?>
            $('input#<?php echo $id ?>').iris(irisSettings);
          });
        })(jQuery);
      </script><?php

      $script = ob_get_clean();
      echo trim(preg_replace('#[\r\n]+|[\s]{2,}#', '', $script));
    }

    /**
     * Add html as settings field
     *
     * @param array $args
     * @example:
     *          array(
     *            'html' => '<code>This is just an example</code>'
     *          )
     */
    public static function html(array $args)
    {
      echo (string)$args['html'];
    }
  }
