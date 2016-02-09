<?php
  /**
   * TinyMCE template
   *
   * @var string $editor_id
   * @var string $template
   * @var string $type
   */
  use HappyFramework\Abstracts\AbstractTheme as Theme; ?>

<?php /* --  submit- and cancel button -- */ ?>
<div id="cta-buttons">
  <button class="btn btn-primary" role="button"><?php _e('Insert shortcode', Theme::$domain) ?></button>
  <a href="#" class="btn btn-default" role="button" onclick="window.closeWindow();"><?php _e('Close', Theme::$domain) ?></a>
</div>

</form><!-- #tiny_mce_popup -->

<script>
  /**
   * @param   jQuery        $
   * @param   underscore    _
   * @param   knockout      ko
   */
  (function ($, _, ko) {
    var $form  = $(window.document.querySelector('form#tiny_mce_popup')),
        editor = window.parent.tinyMCE.get('<?php echo $editor_id ?>');

    // style select elements for all `select` elements in popup
    _.each(window.document.querySelectorAll('select'), function (select) {
      $(select).select2({containerCss: {"min-width": "150px"}});
    });

    /**
     * Submit form
     * @param {object} event
     */
    $form.on('submit', function (event) {
      event.preventDefault();
      var shortcode_id = _.isEmpty(shortcode_id) ? '<?php echo $type ?>' : shortcode_id;
      var attributes = window.stringifyFormParams($form);
      var shortcode = window.getShortcode(shortcode_id, attributes);

      // insert in editor and close window
      editor.insertContent(shortcode);
      window.closeWindow();
    });

    /**
     * Close editors window popup
     * @return {void}
     */
    window.closeWindow = function () {
      editor.windowManager.close();
    };

    /**
     * Stringify form params
     * @return {string}
     */
    window.stringifyFormParams = function ($form) {
      var str = '';
      _.each($form.serializeArray(), function (obj) {
        if (obj.value != '-1' && !_.isEmpty(obj.value)) {
          var attribute = obj.name + '="' + obj.value.replace(/([\]\[\"])/g, '\\$1') + '"';
          str += _.isEmpty(str) ? attribute : ' ' + attribute;
        }
      });
      return str;
    };

    /**
     * Sanitize attribute value
     * @param   {string} value
     * @return  {string}
     */
    window.sanitizeAttributeValue = function (value) {
      return (value && !_.isEmpty(value)) ? value.replace(/([\]\[\"\']+)/g, '\\\\$1') : '';
    };

    /**
     * Get shortcode which can be overridden
     * @return {string}
     */
    window.getShortcode = window.getShortcode || function (shortcode_id, attributes) {
        return '[' + shortcode_id + (!_.isEmpty(attributes) ? ' ' + attributes : '' ) + ']';
      };

    /**
     * Set height of the iframe to height of content `body`
     * @return {void}
     */
    window.adjustIframeHeight = function () {
      var s  = window.parent.document.querySelector('iframe[src*="<?php echo $template ?>"]'),
          $p = $(s).parent(),
          $c = $(s).parents('.mce-container').first(),
          h  = Math.min($(window.parent).height() - 100, $(window.document.body).height()) + 36;

      // set height
      $p.add($c).height(h);

      // set offset top
      var top = ($(window.parent).height() - (h + 36)) / 2;
      $c.css('top', top + 'px');
    };

    // adjust height on init
    window.adjustIframeHeight();

  })(jQuery, window.parent._, window.parent.ko);
</script>
</body>
</html>
