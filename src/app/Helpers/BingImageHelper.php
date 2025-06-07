<?php

namespace App\Helpers;

class BingImageHelper
{
    public static function getBingImageByKeyword($keyword)
    {
        return "https://th.bing.com/th?q=$keyword&c=7&rs=1&p=0&o=5&dpr=2&pid=1.7&mkt=en-WW&cc=VN&setlang=en&adlt=moderate&t=1";
    }
}