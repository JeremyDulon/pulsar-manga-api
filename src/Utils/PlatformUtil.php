<?php


namespace App\Utils;

use App\Entity\Platform;
use App\MangaPlatform\Platforms\AbstractPlatform;
use App\MangaPlatform\Platforms\FanFoxPlatform;
use App\MangaPlatform\Platforms\MangaFastPlatform;
use App\MangaPlatform\Platforms\MangaParkPlatform;
use App\MangaPlatform\Platforms\MangaSeePlatform;
use App\MangaPlatform\Platforms\TCBScansPlatform;

class PlatformUtil
{
    public const LANGUAGE_EN = 'EN';
    public const LANGUAGE_FR = 'FR';

    public static function getPlatform(Platform $platformEntity): ?AbstractPlatform
    {
        $platforms = [
            'MangaPark' => new MangaParkPlatform(),
            'MangaFast' => new MangaFastPlatform(),
            'FanFox' => new FanFoxPlatform(),
            'TCBScans' => new TCBScansPlatform(),
            'MangaSee' => new MangaSeePlatform()
        ];

        return $platforms[$platformEntity->getName()] ?? null;
    }

    public static function getMinMaxNumber(int $lastNumber, int $startingNumber = null, int $limit = 0): array
    {
        if ($limit < 0) {
            throw new \Exception('Limit below 0');
        }

        $min = 0;
        $max = $lastNumber;

        if ($startingNumber !== null) {
            $min = $startingNumber;
        }

        if ($limit > 0) {
            $max = $min + $limit;
        }

        return [
            'min' => $min,
            'max' => $max
        ];
    }

    public static function filterIssues(
        array $issueArray,
        array $parameters
    ): array
    {
        if ($issueArray === []) {
            return $issueArray;
        }

        $limit = $parameters['limit'] ?? 0;
        $startingNumber = $parameters['startingNumber'] ?? null;

        usort($issueArray, function ($issueA, $issueB) {
            return $issueA['number'] < $issueB['number'] ? -1 : 1;
        });

        $lastIssueNumber = (int) end($issueArray)['number'];

        [
            'min' => $min,
            'max' => $max
        ] = self::getMinMaxNumber($lastIssueNumber, $startingNumber, $limit);

        return array_filter(
            $issueArray,
            function ($issue) use ($lastIssueNumber, $min, $max) {
                $chNumber = (int) $issue['number'];
                return Functions::in_range(
                    $chNumber,
                    $min,
                    $max
                );
            }
        );
    }
}
