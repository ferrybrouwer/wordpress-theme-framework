<?php
  namespace HappyFramework\Helpers;

  /**
   * Class File
   *
   * @package HappyFramework\Helpers
   */
  class File
  {

    /**
     * Get attachment id of given file path
     *
     * @param string $filePath
     * @return int|boolean
     */
    public static function pathToId($filePath)
    {
      global $wpdb;
      $uploads = wp_upload_dir();
      $filePath = str_replace(array($uploads['basedir'], $uploads['baseurl']), '', $filePath);
      $filePath = preg_replace('/^\//', '', $filePath);
      $attachmentId = $wpdb->get_var($wpdb->prepare("
          SELECT p.`ID` FROM $wpdb->posts p
          LEFT OUTER JOIN $wpdb->postmeta pm
          ON p.`ID` = pm.`post_id`
          WHERE pm.`meta_value` = '%s'
          LIMIT 1
        ", $filePath));

      return $attachmentId ? (int)$attachmentId : false;
    }

    /**
     * Get file path of given attachment id
     *
     * @param int $attachmentId
     * @return bool|string
     */
    public static function idToPath($attachmentId)
    {
      return get_attached_file($attachmentId);
    }

    /**
     * Check if given attachment is an image
     *
     * @param int $attachmentId
     * @return boolean
     */
    public static function isImage($attachmentId)
    {
      return wp_attachment_is_image($attachmentId);
    }

    /**
     * Get url from given attachment id
     *
     * @param int    $attachmentId
     * @param string $size [optional]
     * @return string|bool
     */
    public static function urlFromId($attachmentId, $size = null)
    {
      $attachment = wp_get_attachment_image_src($attachmentId, $size);

      return $attachment ? $attachment[0] : false;
    }

    /**
     * Get meta fields for an image by given attachment id
     *
     * @param int  $attachmentId
     * @param bool $unfiltered
     * @return bool|string
     */
    public static function getImageMetaData($attachmentId, $unfiltered = false)
    {
      return wp_get_attachment_metadata($attachmentId, $unfiltered);
    }

    /**
     * Get file size in bytes from attachment ID or file path
     *
     * @param string|int $file [this could be the path to the file or an attachment id]
     * @return string|boolean
     */
    public static function getBytes($file)
    {
      $bytes = false;
      $file = is_file($file) ? $file : get_attached_file($file);
      if (is_file($file)) {
        $bytes = filesize($file);
      }

      return $bytes;
    }

    /**
     * Convert bytes to formatted size
     *
     * @param int $bytes
     * @return string|boolean
     */
    public static function bytesToSize($bytes)
    {
      $formattedSize = false;
      $sizes = array('Bytes', 'kb', 'Mb', 'Gb', 'Tb');
      $i = intval(floor(log($bytes) / log(1024)));
      if ($bytes > 0) {
        $formattedSize = ($i == 0) ? ($bytes . ' ' . $sizes[$i]) : (round(($bytes / pow(1024, $i)), 1, PHP_ROUND_HALF_UP) . ' ' . $sizes[$i]);
      }

      return $formattedSize;
    }

    /**
     * Get formatted size
     *
     * @param string|int $file [this could be the path to the file or an attachment id]
     * @return string
     */
    public static function getFormattedSize($file)
    {
      $bytes = File::getBytes($file);

      return File::bytesToSize($bytes);
    }

    /**
     * Convert a formatted size into bytes
     *
     * @param string $formattedSize
     * @return int|null
     */
    public static function sizeToBytes($formattedSize)
    {
      $size = null;

      if (is_numeric($formattedSize)) {
        $size = (int)$formattedSize;
      } else {
        $formattedSize = trim($formattedSize);
        $suffix = substr($formattedSize, -1);
        $size = substr($formattedSize, 0, -1);
        switch (strtoupper($suffix)) {
          case 'P':
            $size *= 1024;
          case 'T':
            $size *= 1024;
          case 'G':
            $size *= 1024;
          case 'M':
            $size *= 1024;
          case 'K':
            $size *= 1024;
            break;
        }
      }

      return $size;
    }
  }
