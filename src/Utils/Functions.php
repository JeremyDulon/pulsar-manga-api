<?php


namespace App\Utils;


class Functions
{
    /**
     * @param string $text
     * @return string
     */
    public static function slugify(string $text)
    {
        // replace non letter or digits by -
        $text = preg_replace('~[^\pL\d]+~u', '-', $text);

        // transliterate
        $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);

        // remove unwanted characters
        $text = preg_replace('~[^-\w]+~', '', $text);

        // trim
        $text = trim($text, '-');

        // remove duplicate -
        $text = preg_replace('~-+~', '-', $text);

        // lowercase
        $text = strtolower($text);

        if (empty($text)) {
            return 'n-a';
        }

        return $text;
    }

    /**
     * @param string $url
     * @return string
     */
    public static function baseUrl(string $url) {
        $urlInfo = parse_url($url);
        return self::baseUrlInfo($urlInfo);
    }

    /**
     * @param array $urlInfo
     * @return string
     */
    public static function baseUrlInfo(array $urlInfo) {
        return $urlInfo['scheme'] . '://' . $urlInfo['host'];
    }
}
