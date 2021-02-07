<?php


namespace App\Utils;


use App\Entity\Chapter;
use App\Entity\Manga;
use App\Entity\Platform;
use DateTime;
use Facebook\WebDriver\Remote\RemoteWebElement;
use Facebook\WebDriver\WebDriverBy;
use PHPHtmlParser\Dom\Node\Collection;
use Symfony\Component\Panther\DomCrawler\Crawler;

class PlatformUtil
{
    public const PLATFORM_KAKALOT = 'MangaKakalot';
    public const PLATFORM_FOX = 'MangaFox';
    public const PLATFORM_LELSCAN = 'Lelscan';
    public const PLATFORM_SCANFR = 'Scan FR';
    public const PLATFORM_MANGAFREAK = 'MangaFreak';
    public const PLATFORM_MANGAZUKI = 'Mangazuki';
    public const PLATFORM_MANGAFAST = 'MangaFast';
    public const PLATFORM_MANGAPARK = 'MangaPark';

    public const LANGUAGE_EN = 'EN';
    public const LANGUAGE_FR = 'FR';

    public const MANGA_SLUG = 'manga_slug';

    public const TITLE_NODE = 'titleNode';
    public const ALT_TITLES_NODE = 'altTitlesNode';
    public const STATUS_NODE = 'statusNode';
    public const MANGA_IMAGE_NODE = 'mangaImageNode';
    public const AUTHOR_NODE = 'authorNode';
    public const VIEWS_NODE = 'viewsNode';
    public const LAST_UPDATE_NODE = 'lastUpdateNode';
    public const DESCRIPTION_NODE = 'descriptionNode';
    public const CHAPTER_DATA_NODE = 'chapterDataNode';
    public const CHAPTER_PAGES_NODE = 'chapterPagesNode';

    public static function getClassPlatforms() {

    }

    /** @todo en faire une classe */
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
                    self::TITLE_NODE => [
                        'selector' => [
                            '.story-info-right h1' => 0
                        ],
                        'text' => true
                    ],
                    self::ALT_TITLES_NODE => [
                        'selector' => [
                            '.variations-tableInfo .table-value h2' => 0
                        ],
                        'callback' => function ($el, $parameters) {
                            return array_map(function ($val) {
                                return trim($val);
                            }, explode(';', $el->text));
                        }
                    ],
                    self::STATUS_NODE => [
                        'selector' => [
                            '.variations-tableInfo .table-value h2' => 0
                        ],
                        'callback' => function ($el, $parameters) {
                            return $el->text === 'Ongoing' ? Manga::STATUS_ONGOING : Manga::STATUS_ENDED;
                        }
                    ],
                    self::MANGA_IMAGE_NODE => [
                        'selector' => [
                            '.info-image .img-loading' => 0
                        ],
                        'callback' => function ($el) {
                            return $el->getAttribute('src');
                        }
                    ],
                    self::AUTHOR_NODE => [
                        'selector' => [
                            '.variations-tableInfo .a-h' => 0
                        ],
                        'callback' => function ($el) {
                            return $el->text;
                        }
                    ],
                    self::VIEWS_NODE => [
                        'selector' => [
                            '.story-info-right-extent .stre-value' => 1
                        ],
                        'callback' => function ($el, $parameters) {
                            return $el->text;
                        }
                    ],
                    self::LAST_UPDATE_NODE => [
                        'selector' => [
                            '.story-info-right-extent .stre-value' => 0
                        ],
                        'callback' => function ($el, $parameters) {
                            return DateTime::createFromFormat('M d,Y - H:i A',$el->text);
                        }
                    ],
                    self::DESCRIPTION_NODE => [
                        'selector' => [
                            '.panel-story-info-description' => null
                        ],
                        'callback' => function ($el) {
                            return $el->text;
                        }
                    ],
                    self::CHAPTER_DATA_NODE => [
                        'selector' => [
                            'ul.row-content-chapter li.a-h' => null
                        ],
                        'callback' => function ($el, $parameters) {
                            $offset = $parameters['offset'];
                            $chapterNumber = $parameters['chapterNumber'];
                            $val = [];
                            $chaptersArray = array_reverse(array_filter($el->toArray(), function ($node) {
                                $a = $node->find('a');
                                return ctype_digit(explode('_', basename($a->getAttribute('href')))[1]) === true;
                            }));
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
                    self::CHAPTER_PAGES_NODE => [
                        'selector' => [
                            '.container-chapter-reader img' => null
                        ],
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
                'name' => self::PLATFORM_MANGAFAST,
                'language' => self::LANGUAGE_EN,
                'baseUrl' => 'https://mangafast.net',
                'mangaPath' => '/read/' . self::MANGA_SLUG,
                'mangaRegex' => [
                    'regex' => '/\/read\/((?:[a-z]*-?)*)/',
                    'manga' => 1
                ],
                'chapterRegex' => [
                    'regex' => '/\/((?:[A-Za-z0-9]*-?)*)-([0-9]+)',
                    'manga' => 1,
                    'chapter' => 2
                ],
                'nodes' => [
                    self::TITLE_NODE => [
                        'selector' => [
                            '.inftable tr td b' => 0
                        ],
                        'text' => true
                    ],
                    self::STATUS_NODE => [
                        'selector' => [
                            '.inftable tr' => 5,
                            'td' => 1
                        ],
                        'callback' => function ($el, $parameters) {
                            return $el->text === 'Ongoing' ? Manga::STATUS_ONGOING : Manga::STATUS_ENDED;
                        }
                    ],
                    self::ALT_TITLES_NODE => [
                        'selector' => [
                            '.inftable tr' => 1,
                            'td' => 1
                        ],
                        'callback' => function ($el, $parameters) {
                            return [$el->text];
                        }
                    ],
                    self::MANGA_IMAGE_NODE => [
                        'selector' => [
                            '#Thumbnail' => null
                        ],
                        'callback' => function ($el) {
                            return $el->getAttribute('data-src');
                        }
                    ],
                    self::AUTHOR_NODE => [
                        'selector' => [
                            '.inftable tr' => 3,
                            'td' => 1
                        ],
                        'text' => true
                    ],
                    self::DESCRIPTION_NODE => [
                        'selector' => [
                            '.sc p[itemprop=description]' => null
                        ],
                        'callback' => function ($el) {
                            return $el->text;
                        }
                    ],
                    self::CHAPTER_DATA_NODE => [
                        'selector' => [
                            '.lsch tr[itemprop=hasPart]' => null
                        ],
                        'callback' => function ($el, $parameters) {
                            $chaptersArray = array_filter($el->toArray(), function ($node) {
                                $a = $node->find('.jds a');
                                $date = $node->find('.tgs');
                                return ctype_digit($a->find('span[itemprop=issueNumber]')->text) === true && trim($date->text) !== 'Scheduled';
                            });
                            $offset = $parameters['offset'];
                            $chapterNumber = $parameters['chapterNumber'];
                            $val = [];
                            $numberCb = function ($node) {
                                return $node->find('.jds a span')->text;
                            };

                            usort($chaptersArray, function ($a, $b) {
                                return $a->find('span[itemprop=issueNumber]')->text < $b->find('span[itemprop=issueNumber]')->text
                                    ? -1
                                    : 1;
                            });

                            $lastChapterNumber = (int) $numberCb(end($chaptersArray));

                            $chaptersArray = self::filterChapters($chaptersArray, $numberCb, $lastChapterNumber, $offset, $chapterNumber);
                            foreach ($chaptersArray as $chapterNode) {
                                $a = $chapterNode->find('.jds a');
                                $number = $a->find('span[itemprop=issueNumber]')->text;
                                $url = $a->getAttribute('href');
                                $val[] = [
                                    'title' => trim($a->innerText()),
                                    'number' => $number,
                                    'url' => $url,
                                    'date' => DateTime::createFromFormat('Y-m-d', trim($chapterNode->find('.tgs')->text))->setTime(0, 0)
                                ];
                            }
                            return $val;
                        }
                    ],
                    self::CHAPTER_PAGES_NODE => [
                        'selector' => [
                            '.chp2 img' => null
                        ],
                        'callback' => function ($el, $parameters) {
                            /** @var Chapter|null $chapter */
                            $chapter = $parameters['chapter'] ?? null;
                            $val = [];
                            if ($chapter) {
                                $pageNumber = 1;
                                foreach ($el as $item) {
                                    $url = $item->getAttribute('data-src') ?? $item->getAttribute('src');
                                    $val[] = [
                                        'number' => $pageNumber,
                                        'url' => $url
                                    ];
                                    $pageNumber++;
                                }
                            }
                            return $val;
                        }
                    ]
                ]
            ],

            [
                'name' => self::PLATFORM_MANGAPARK,
                'language' => self::LANGUAGE_EN,
                'baseUrl' => 'https://mangapark.net',
                'mangaPath' => '/manga/' . self::MANGA_SLUG,
                'mangaRegex' => [
                    'regex' => '/\/manga\/((?:[a-z]*-?)*)/',
                    'manga' => 1
                ],
                'chapterRegex' => [
                    'regex' => '/\/manga\/((?:[A-Za-z0-9]*-?)*)\/(i[0-9]*\/c[0-9]*)',
                    'manga' => 1,
                    'chapter' => 2
                ],
                'nodes' => [
                    self::TITLE_NODE => [
                        'selector' => '.cover img',
                        'callback' => function ($el) {
                            return $el->getAttribute('title');
                        }
                    ],
                    self::STATUS_NODE => [
                        'selector' => '.attr tr:nth-child(8)',
                        'callback' => function ($el, $parameters) {
                            return $el->getText() === 'Ongoing' ? Manga::STATUS_ONGOING : Manga::STATUS_ENDED;
                        }
                    ],
                    self::ALT_TITLES_NODE => [
                        'selector' => '.attr tr:nth-child(4)',
                        'callback' => function (Crawler $el, $parameters) {
                            return array_map(function ($v) {
                                return trim($v);
                            }, explode(';', $el->getText()));
                        }
                    ],
                    self::MANGA_IMAGE_NODE => [
                        'selector' => '.cover img',
                        'callback' => function ($el) {
                            return $el->getAttribute('src');
                        }
                    ],
                    self::AUTHOR_NODE => [
                        'selector' => '.attr tr:nth-child(5)',
                        'text' => true
                    ],
                    self::DESCRIPTION_NODE => [
                        'selector' => '.summary',
                        'text' => true
                    ],
                    self::CHAPTER_DATA_NODE => [
                        'selector' => '.book-list-1 #stream_1 .chapter',
                        'callback' => function (Crawler $el, $parameters) {
                            $chaptersArray = $el->children('.item')->reduce(function (Crawler $node, $i) {
                                $chapNumber = str_replace('ch.', '', $node->filter('a.ch')->getText());
                                return ctype_digit($chapNumber) === true;
                            })->each(function (Crawler $ch) {
                                $chapterData = $ch->filter('a.ch');
                                $number = str_replace('ch.', '', $chapterData->getText());
                                $url = $ch->filter('.ext em a:nth-child(5)')->getAttribute('href');
                                return [
                                    'title' => 'Chapter ' . $number,
                                    'number' => $number,
                                    'url' => $url,
                                    'date' => new DateTime($ch->filter('.time')->getText())
                                ];
                            });

                            $offset = $parameters['offset'];
                            $chapterNumber = $parameters['chapterNumber'];
                            $numberCb = function ($ch) {
                                return $ch['number'];
                            };

                            usort($chaptersArray, function ($chA, $chB) {
                                return $chA['number'] < $chB['number'] ? -1 : 1;
                            });

                            $lastChapterNumber = (int) $numberCb(end($chaptersArray));

                            $chaptersArray = self::filterChapters($chaptersArray, $numberCb, $lastChapterNumber, $offset, $chapterNumber);
                            return $chaptersArray;
                        }
                    ],
                    self::CHAPTER_PAGES_NODE => [
                        'script_callback' => function ($client, $parameters) {
                            $chPages = $client->executeScript('return _load_pages');
                            /** @var Chapter|null $chapter */
                            $chapter = $parameters['chapter'] ?? null;
                            $val = [];
                            if ($chapter) {
                                foreach ($chPages as $chPage) {
                                    $val[] = [
                                        'number' => $chPage['n'],
                                        'url' => $chPage['u']
                                    ];
                                }
                            }
                            return $val;
                        }
                    ]
                ]
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
            [
                'name' => self::PLATFORM_MANGAFREAK,
                'language' => self::LANGUAGE_EN,
                'baseUrl' => 'https://w11.mangafreak.net',
                'mangaPath' => '/Manga/' . self::MANGA_SLUG,
                'mangaRegex' => [
                    'regex' => '/\/Manga\/((?:[a-z]*_?)*)/',
                    'manga' => 1
                ],
                'chapterRegex' => [
                    'regex' => '/\/Read1_((?:[A-Za-z0-9]*_?)*)_([0-9]+)',
                    'manga' => 1,
                    'chapter' => 2
                ],
                'nodes' => [
                    'titleNode' => [
                        'selector' => '.manga_series_data h5',
                        'child-index' => 0,
                        'text' => true
                    ],
                    'statusNode' => [
                        'selector' => '.manga_series_data div',
                        'child-index' => 1,
                        'callback' => function ($el, $parameters) {
                            return $el->text === 'ON-GOING' ? Manga::STATUS_ONGOING : Manga::STATUS_ENDED;
                        }
                    ],
                    'mangaImageNode' => [
                        'selector' => '.manga_series_image',
                        'child-index' => 0,
                        'callback' => function ($el) {
                            return $el->getAttribute('src');
                        }
                    ],
                    'authorNode' => [
                        'selector' => '.manga_series_data div',
                        'child-index' => 2,
                        'callback' => function ($el) {
                            return $el->text;
                        }
                    ],
                    'descriptionNode' => [
                        'selector' => '.manga_series_description p',
                        'child-index' => 0,
                        'callback' => function ($el) {
                            return $el->text;
                        }
                    ],
                    'chapterDataNode' => [
                        'selector' => '.manga_series_list tr',
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
                                    'date' => DateTime::createFromFormat('M d,Y H:i', $item->find('.chapter-time'))
                                ];
                            }
                            return $val;
                        }
                    ]
                ]
            ]
        ];
    }

    public static function getPlatform(Platform $platformEntity) {
        foreach (self::getPlatforms() as $platform) {
            if ($platformEntity->getName() === $platform['name']) {
                return $platform;
            }
        }

        return null;
    }

    public static function getPlatformFromKey($key, $value) {
        foreach (self::getPlatforms() as $platform) {
            if (array_key_exists($key, $platform) && $platform[$key] === $value) {
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

    public static function filterChapters($chapters, $chapterNumberCb, $lastChapterNumber, $offset = 0, $chapterNumber = null): array
    {
        [
            'min' => $min,
            'max' => $max
        ] = self::getMinMaxChapter($lastChapterNumber, $offset, $chapterNumber);

        $chapters = array_filter(
            $chapters,
            function ($val) use ($lastChapterNumber, $chapterNumberCb, $min, $max) {
                $chNumber = (int) $chapterNumberCb($val);
                return Functions::in_range(
                    $chNumber,
                    $min,
                    $max
                );
            }
        );

        return $chapters;
    }
}
