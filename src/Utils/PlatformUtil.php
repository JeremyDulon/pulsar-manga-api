<?php


namespace App\Utils;

use App\Entity\Platform;
use App\MangaPlatform\AbstractPlatform;
use App\MangaPlatform\Platforms\FanFoxPlatform;
use App\MangaPlatform\Platforms\MangaFastPlatform;
use App\MangaPlatform\Platforms\MangaParkPlatform;

class PlatformUtil
{
    public const LANGUAGE_EN = 'EN';
    public const LANGUAGE_FR = 'FR';

    public static function getPlatforms(): array
    {
        return [
            new MangaParkPlatform(),
            new MangaFastPlatform(),
            new FanFoxPlatform()
        ];
    }

    public static function getPlatform(Platform $platformEntity): ?AbstractPlatform
    {
        foreach (self::getPlatforms() as $platform) {
            /** @var AbstractPlatform $platform */
            if ($platformEntity->getName() === $platform->getName()) {
                return $platform;
            }
        }

        return null;
    }

    public static function getPlatformFromBaseUrl($baseUrl): ?AbstractPlatform
    {
        foreach (self::getPlatforms() as $platform) {
            if ($platform->getBaseUrl() === $baseUrl) {
                return $platform;
            }
        }

        return null;
    }

    public static function findPlatformFromUrl($url): ?AbstractPlatform
    {
        $urlInfo = parse_url($url);
        $baseUrl = $urlInfo['scheme'] . '://' . $urlInfo['host'];
        return self::getPlatformFromBaseUrl($baseUrl);
    }

    /**
     * @param int $lastChapterNumber
     * @param int $offset
     * @param int|null $chapterNumber
     * @return int[]
     */
    public static function getMinMaxChapter(int $lastChapterNumber, int $offset = 0, int $chapterNumber = null): array
    {
        if ($chapterNumber === null) {
            $min = $offset < 0 ? $lastChapterNumber + $offset : 0;
            $max = $offset > 0 ? $offset : $lastChapterNumber;
        } else {
            if ($offset === 0) {
                $min = $chapterNumber;
                $max = $lastChapterNumber;
            } else if ($offset > 0) {
                $min = $chapterNumber;
                $max = $chapterNumber + $offset;
            } else {
                $min = $chapterNumber + $offset;
                $max = $chapterNumber;
            }
        }

        return [
            'min' => $min,
            'max' => $max
        ];
    }

    public static function filterChapters(
        array $chapters,
        array $parameters
    ): array
    {
        $offset = $parameters['offset'];
        $chapterNumber = $parameters['chapterNumber'];

        usort($chapters, function ($chA, $chB) {
            return $chA['number'] < $chB['number'] ? -1 : 1;
        });

        if ($chapters === []) {
            return $chapters;
        }

        $lastChapterNumber = (int) end($chapters)['number'];

        [
            'min' => $min,
            'max' => $max
        ] = self::getMinMaxChapter($lastChapterNumber, $offset, $chapterNumber);

        return array_filter(
            $chapters,
            function ($chapter) use ($lastChapterNumber, $min, $max) {
                $chNumber = (int) $chapter['number'];
                return Functions::in_range(
                    $chNumber,
                    $min,
                    $max
                );
            }
        );
    }
}
