<?php
namespace HappyFramework\Options;

use HappyFramework\Abstracts\AbstractOptionMenuItem;
use HappyFramework\Abstracts\AbstractTheme as Theme;

class InlineScripts extends AbstractOptionMenuItem
{
    public static $okey = 'inline_scripts';
    public static $oslug = 'inline-scripts';

    public function __construct()
    {
        parent::__construct(__('Inline Scripts', Theme::$domain), self::$okey, self::$oslug);

        // @todo: finish this
    }
}