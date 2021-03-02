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
     * @param $input
     * @return string
     */
    public static function formatMilliseconds($input): string
    {
        $ms = $input % 1000;
        $input = floor($input / 1000);

        $seconds = $input % 60;
        $input = floor($input / 60);

        $minutes = $input % 60;
        $input = floor($input / 60);

        $hours = $input % 24;
        $input = floor($input / 24);

        return sprintf( '%02dh %02dm %02ds %02dms', $hours, $minutes, $seconds, $ms);
    }

    /**
     * @param string $url
     * @return string
     */
    // todo: slugify fn name
    public static function baseUrl(string $url) {
        $urlInfo = parse_url($url);
        return self::baseUrlInfo($urlInfo);
    }

    /**
     * @param array $urlInfo
     * @return string
     */
    // todo: slugify fn name
    public static function baseUrlInfo(array $urlInfo) {
        return $urlInfo['scheme'] . '://' . $urlInfo['host'];
    }

    /**
     * Determines if $number is between $min and $max
     *
     * @param integer $number The number to test
     * @param integer $min The minimum value in the range
     * @param integer $max The maximum value in the range
     * @param boolean $inclusive Whether the range should be inclusive or not
     * @return boolean              Whether the number was in the range
     */
    public static function in_range(int $number, int $min, int $max, bool $inclusive = true)
    {
        if (is_int($number) && is_int($min) && is_int($max))
        {
            return $inclusive
                ? ($number >= $min && $number <= $max)
                : ($number > $min && $number < $max) ;
        }

        return FALSE;
    }
}
