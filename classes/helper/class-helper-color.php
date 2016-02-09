<?php

  namespace HappyFramework\Helpers;

  /**
   * Class Color
   *
   * @package HappyFramework\Helpers
   */
  class Color
  {
    /**
     * Check if given value is a hexadecimal value
     *
     * @param string $hex
     * @return boolean
     */
    public static function isHex($hex)
    {
      return preg_match('/^#[0-9a-fA-F]{3,6}$/', $hex);
    }

    /**
     * Get lighten color of hexadecimal
     *
     * @param   string $hex
     * @param   int    $percentage [optional, default 10%]
     * @return  string
     */
    public static function hexLighten($hex, $percentage = 10)
    {
      $hex = Color::sanitizeHex($hex);
      $hsl = Color::hexToHsl($hex);

      // manipulate hsl values
      $hsl['L'] = ($hsl['L'] * 100) + $percentage;
      $hsl['L'] = ($hsl['L'] > 100) ? 1 : $hsl['L'] / 100;

      return '#' . Color::hslToHex($hsl);
    }

    /**
     * Returns a darken color
     *
     * @param   string $hex
     * @param   int    $percentage
     * @return  string
     */
    public static function hexDarken($hex, $percentage = 10)
    {
      $hex = Color::sanitizeHex($hex);
      $hsl = Color::hexToHsl($hex);

      // manipulate hsl values
      $hsl['L'] = ($hsl['L'] * 100) - $percentage;
      $hsl['L'] = ($hsl['L'] < 0) ? 0 : $hsl['L'] / 100;

      return '#' . Color::hslToHex($hsl);
    }

    /**
     * Returns the complementary color
     *
     * @param   string $hex
     * @return  string
     */
    public static function complementaryHex($hex)
    {
      $hex = Color::sanitizeHex($hex);
      $hsl = Color::hexToHsl($hex);

      // adjust Hue 180 degrees
      $hsl['H'] += ($hsl['H'] > 180) ? -180 : 180;

      return '#' . Color::hslToHex($hsl);
    }

    /**
     * Returns corresponding RGB value from given HUE
     *
     * @param   int $v1
     * @param   int $v2
     * @param   int $vH
     * @return  int
     */
    public static function hueToRgb($v1, $v2, $vH)
    {
      if ($vH < 0) {
        $vH += 1;
      }
      if ($vH > 1) {
        $vH -= 1;
      }
      if ((6 * $vH) < 1) {
        return ($v1 + ($v2 - $v1) * 6 * $vH);
      }
      if ((2 * $vH) < 1) {
        return $v2;
      }
      if ((3 * $vH) < 2) {
        return ($v1 + ($v2 - $v1) * ((2 / 3) - $vH) * 6);
      }

      return $v1;
    }

    /**
     * Returns the equivalent HEX string of a given HSL associative array
     *
     * @param   array $hsl
     * @return  string
     * @throws  \Exception
     */
    public static function hslToHex($hsl = array())
    {
      // validate hsl array
      if (empty($hsl) || !isset($hsl['H']) || !isset($hsl['S']) || !isset($hsl['L'])) {
        throw new \Exception('Param was not an HSL array');
      }

      list($H, $S, $L) = array($hsl['H'] / 360, $hsl['S'], $hsl['L']);

      if ($S == 0) {
        $r = $L * 255;
        $g = $L * 255;
        $b = $L * 255;
      } else {
        if ($L < 0.5) {
          $var_2 = $L * (1 + $S);
        } else {
          $var_2 = ($L + $S) - ($S * $L);
        }
        $var_1 = 2 * $L - $var_2;
        $r = round(255 * Color::hueToRgb($var_1, $var_2, $H + (1 / 3)));
        $g = round(255 * Color::hueToRgb($var_1, $var_2, $H));
        $b = round(255 * Color::hueToRgb($var_1, $var_2, $H - (1 / 3)));
      }

      // convert to hex
      $r = dechex($r);
      $g = dechex($g);
      $b = dechex($b);

      // make sure we get 2 digits for decimals
      $r = (strlen('' . $r) === 1) ? '0' . $r : $r;
      $g = (strlen('' . $g) === 1) ? '0' . $g : $g;
      $b = (strlen('' . $b) === 1) ? '0' . $b : $b;

      return $r . $g . $b;
    }

    /**
     * Returns a HSL array equivalent of HEX string
     *
     * @param   string $hex
     * @return  array
     */
    public static function hexToHsl($hex)
    {
      $hex = Color::sanitizeHex($hex);

      // Convert HEX to DEC
      $R = hexdec($hex[0] . $hex[1]);
      $G = hexdec($hex[2] . $hex[3]);
      $B = hexdec($hex[4] . $hex[5]);

      $HSL = array();

      $var_R = ($R / 255);
      $var_G = ($G / 255);
      $var_B = ($B / 255);

      $var_Min = min($var_R, $var_G, $var_B);
      $var_Max = max($var_R, $var_G, $var_B);
      $del_Max = $var_Max - $var_Min;

      $L = ($var_Max + $var_Min) / 2;

      if ($del_Max == 0) {
        $H = 0;
        $S = 0;
      } else {

        if ($L < 0.5) {
          $S = $del_Max / ($var_Max + $var_Min);
        } else {
          $S = $del_Max / (2 - $var_Max - $var_Min);
        }

        $del_R = ((($var_Max - $var_R) / 6) + ($del_Max / 2)) / $del_Max;
        $del_G = ((($var_Max - $var_G) / 6) + ($del_Max / 2)) / $del_Max;
        $del_B = ((($var_Max - $var_B) / 6) + ($del_Max / 2)) / $del_Max;

        $H = null;
        if ($var_R == $var_Max) {
          $H = $del_B - $del_G;
        } else if ($var_G == $var_Max) {
          $H = (1 / 3) + $del_R - $del_B;
        } else if ($var_B == $var_Max) {
          $H = (2 / 3) + $del_G - $del_R;
        }

        $H = $H < 0 ? $H++ : $H;
        $H = $H > 1 ? $H-- : $H;
      }

      $HSL['H'] = ($H * 360);
      $HSL['S'] = $S;
      $HSL['L'] = $L;

      return $HSL;
    }

    /**
     * Convert HEX value to RGB
     *
     * @param   string $hex
     * @return  array
     */
    public static function hexToRgb($hex)
    {
      $hex = Color::sanitizeHex($hex);

      // convert HEX to DEC
      $R = hexdec($hex[0] . $hex[1]);
      $G = hexdec($hex[2] . $hex[3]);
      $B = hexdec($hex[4] . $hex[5]);

      $RGB['R'] = $R;
      $RGB['G'] = $G;
      $RGB['B'] = $B;

      return $RGB;
    }

    /**
     * Convert RGB to HEX
     *
     * @param   array $rgb
     * @return  string
     * @throws  \Exception
     */
    public static function rgbToHex($rgb = array())
    {
      // validate rgb
      if (empty($rgb) || !isset($rgb['R']) || !isset($rgb['G']) || !isset($rgb['B'])) {
        throw new \Exception('Param was not an RGB array');
      }

      // convert RGB to HEX
      $hex[0] = dechex($rgb['R']);
      $hex[1] = dechex($rgb['G']);
      $hex[2] = dechex($rgb['B']);

      return implode('', $hex);
    }

    /**
     * Sanitize hex color value
     *
     * @param   string $hex
     * @return  string
     * @throws \Exception
     */
    public static function sanitizeHex($hex)
    {
      // strip # sign is present
      $color = str_replace("#", "", $hex);

      // make sure it's 6 digits
      if (strlen($color) === 3) {
        $color = $color[0] . $color[0] . $color[1] . $color[1] . $color[2] . $color[2];
      } else if (strlen($color) !== 6) {
        throw new \Exception('HEX color needs to be 6 or 3 digits long');
      }

      return $color;
    }
  }
