<?php


namespace App\Utils;


use App\Entity\Chapter;
use App\Entity\Manga;
use App\Entity\MangaPlatform;
use App\Utils\Platform as UtilPlatform;
use DateTime;
use PHPHtmlParser\Dom\Node\Collection;

class Platform
{
    public const PLATFORM_KAKALOT = 'MangaKakalot';
    public const PLATFORM_FOX = 'MangaFox';
    public const PLATFORM_LELSCAN = 'Lelscan';
    public const PLATFORM_SCANFR = 'Scan FR';
    public const PLATFORM_MANGAFREAK = 'MangaFreak';
    public const PLATFORM_MANGAZUKI = 'Mangazuki';

    public const LANGUAGE_EN = 'EN';
    public const LANGUAGE_FR = 'FR';

    public const MANGA_SLUG = 'manga_slug';

    public static function getPlatforms() {
        return [
            [
                'name' => self::PLATFORM_KAKALOT,
                'language' => self::LANGUAGE_EN,
                'baseUrl' => 'https://manganelo.com',
                'mangaPath' => '/manga/' . self::MANGA_SLUG,
                'mangaRegex' => [
                    'regex' => '/\/manga\/((?:[a-z]*_?)*)/',
                    'manga' => 1
                ],
                'chapterRegex' => [
                    'regex' => '\/chapter\/((?:[a-z0-9]*_?)*)\/((?:[a-z0-9]*_?)*)',
                    'manga' => 1,
                    'chapter' => 2
                ],
                'nodes' => [
                    'titleNode' => [
                        'selector' => '.story-info-right h1',
                        'child-index' => 0,
                        'text' => true
                    ],
                    'altTitlesNode' => [
                        'selector' => '.variations-tableInfo .table-value h2',
                        'child-index' => 0,
                        'callback' => function ($el, $parameters) {
                            return array_map(function ($val) {
                                return trim($val);
                            }, explode(';', $el->text));
                        }
                    ],
                    'statusNode' => [
                        'selector' => '.variations-tableInfo .table-value h2',
                        'child-index' => 0,
                        'callback' => function ($el, $parameters) {
                            return $el->text === 'Ongoing' ? Manga::STATUS_ONGOING : Manga::STATUS_ENDED;
                        }
                    ],
                    'mangaImageNode' => [
                        'selector' => '.info-image .img-loading',
                        'child-index' => 0,
                        'callback' => function ($el) {
                            return $el->getAttribute('src');
                        }
                    ],
                    'viewsNode' => [
                        'selector' => '.story-info-right-extent .stre-value',
                        'child-index' => 1,
                        'callback' => function ($el, $parameters) {
                            return $el->text;
                        }
                    ],
                    'lastUpdateNode' => [
                        'selector' => '.story-info-right-extent .stre-value',
                        'child-index' => 0,
                        'callback' => function ($el, $parameters) {
                            return DateTime::createFromFormat('M d,Y - H:i A',$el->text);
                        }
                    ],
                    'descriptionNode' => [
                        'selector' => '.panel-story-info-description',
                        'callback' => function ($el) {
                            return $el->text;
                        }
                    ],
                    'chapterDataNode' => [
                        'selector' => 'ul.row-content-chapter li.a-h',
                        'callback' => function ($el, $parameters) {
                            $offset = $parameters['offset'];
                            $chapterNumber = $parameters['chapterNumber'];
                            $val = [];
                            $chaptersArray = array_reverse($el->toArray());
                            $numberCb = function ($val) {
                                $a = $val->find('a');
                                return explode('_', basename($a->getAttribute('href')))[1];
                            };
                            $chaptersArray = self::filterChapters($chaptersArray, $numberCb, $chapterNumber, $offset);
                            foreach ($chaptersArray as $item) {
                                $a = $item->find('a');
                                $url = $a->getAttribute('href');
                                $val[] = [
                                    'title' => $a->text,
                                    'url' => $url,
                                    'number' => explode('_', basename($url))[1],
                                    'date' => DateTime::createFromFormat('M d,Y H:i', $item->find('.chapter-time')->getAttribute('title'))
                                ];
                            }
                            return $val;
                        }
                    ],
                    'chapterPagesNode' => [
                        'selector' => '.container-chapter-reader img',
                        'callback' => function ($el, $parameters) {
                            /** @var Chapter|null $chapter */
                            $chapter = $parameters['chapter'] ?? null;
                            $val = [];

                            if ($chapter) {
                                $pageNumber = 1;
                                foreach ($el as $item) {
                                    $url = $item->getAttribute('src');
                                    $val[] = [
                                        'number' => $pageNumber,
                                        'url' => $url,
                                        'imageHeaders' => [
                                            'Referer: ' . $chapter->getSourceUrl()
                                        ]
                                    ];
                                    $pageNumber++;
                                }
                            }
                            return $val;
                        }
                    ],
                ]
            ],
            [
                'name' => self::PLATFORM_FOX,
                'language' => self::LANGUAGE_EN
            ],
            [
                'name' => self::PLATFORM_MANGAFREAK,
                'language' => self::LANGUAGE_EN
            ],
            [
                'name' => self::PLATFORM_MANGAZUKI,
                'language' => self::LANGUAGE_EN
            ],
            [
                'name' => self::PLATFORM_LELSCAN,
                'language' => self::LANGUAGE_FR
            ],
            [
                'name' => self::PLATFORM_SCANFR,
                'language' => self::LANGUAGE_FR
            ],
        ];
    }

    public static function getPlatform(\App\Entity\Platform $platformEntity) {
        foreach (self::getPlatforms() as $platform) {
            if ($platformEntity->getName() === $platform['name']) {
                return $platform;
            }
        }

        return null;
    }

    public static function getPlatformFromKey($key, $value) {
        foreach (self::getPlatforms() as $platform) {
            if ($platform[$key] === $value) {
                return $platform;
            }
        }

        return null;
    }

    /**
     * @param $url
     *
     * @return array|null
     */
    public static function checkUrl($url) {
        $urlInfo = parse_url($url);
        $baseUrl = Functions::baseUrlInfo($urlInfo);
        $platform = self::getPlatformFromKey('baseUrl', $baseUrl);
        if (preg_match($platform['mangaRegex']['regex'], $urlInfo['path'], $matches)) {
            return [
                'type' => 'manga', // todo: const
                'manga' => $matches[$platform['chapterRegex']['manga']]
            ];
        }

        if (preg_match($platform['chapterRegex']['regex'], $urlInfo['path'], $matches)) {
            return [
                'type' => 'chapter', // todo: const
                'manga' => $matches[$platform['chapterRegex']['manga']],
                'chapter' => $matches[$platform['chapterRegex']['chapter']]
            ];
        }

        return null;
    }

    public static function findPlatformFromUrl($url) {
        $urlInfo = parse_url($url);
        $baseUrl = $urlInfo['scheme'] . '://' . $urlInfo['host'];
        return self::getPlatformFromKey('baseUrl', $baseUrl);
    }

    public static function filterChapters($chapters, $chapterNumberCb, $chapterNumber = null, $offset = 0) {
        $lastChapterNumber = $chapterNumberCb(end($chapters));
        $min = $max = 0;
        $maxChapter = (int) $lastChapterNumber;
        if ($chapterNumber === null) {
            $min = $offset < 0 ? $lastChapterNumber + $offset : 0;
            $max = $offset > 0 ? $offset : $maxChapter;
        } else {
            if ($offset === 0) {
                $min = $chapterNumber;
                $max = $maxChapter;
            } else if ($offset > 0) {
                $min = $chapterNumber;
                $max = $chapterNumber + $offset;
            } else {
                $min = $chapterNumber + $offset;
                $max = $chapterNumber;
            }
        }
        $chapters = array_filter(
            $chapters,
            function ($val) use ($lastChapterNumber, $chapterNumberCb, $min, $max) {
                $chNumber = $chapterNumberCb($val);
                $classicChapter = strpos($chNumber, '.') === false;
                if ($classicChapter) {
                    $chNumber = (int) $chNumber;
                    return Functions::in_range(
                        $chNumber,
                        $min,
                        $max
                    );
                }
                return false;
            }
        );

        return $chapters;
    }
}
