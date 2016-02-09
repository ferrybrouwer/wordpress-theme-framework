<?php
namespace HappyFramework\Helpers;

/**
 * Class Formatting
 *
 * @package HappyFramework\Helpers
 */
class Formatting
{
  /**
   * Format string to html
   * This function is an equivalant of the_content
   *
   * @param  string $str
   * @return string
   */
  public static function toHtml($str)
  {
    // replace common plain text characters
    $str = wptexturize($str);

    // convert smilies
    $str = convert_smilies($str);

    // Converts lone & characters into `&#038;` (a.k.a. `&amp;`)
    $str = convert_chars($str);

    // Replaces double line-breaks with paragraph elements.
    $str = wpautop($str);

    // Don't auto-p wrap shortcodes that stand alone
    $str = shortcode_unautop($str);

    // convert shortcodes
    $str = do_shortcode($str);

    // prepend attachment
    $str = prepend_attachment($str);

    // balance tags
    $str = force_balance_tags($str);

    // convert ]]> to html entity
    $str = str_replace(']]>', ']]&gt;', $str);

    // remove empty paragraphes
    $str = preg_replace('/\<p\>[\s]*\<\/p\>/', '', $str);

    return $str;
  }

  /**
   * Format string to attribute string
   *
   * @example data-bind="text: '<?php echo Formatting::toAttributeString($str); ?>'"
   * @param   string $str
   * @return  string|void
   */
  public static function toAttributeString($str)
  {
    return esc_attr(str_replace("'", "\\'", $str));
  }
}
