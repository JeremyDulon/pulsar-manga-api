<?php


namespace App\MangaPlatform;


class PlatformRegex
{
    /** @var string $regex */
    protected $regex;

    /** @var int $mangaPosition */
    protected $mangaPosition;

    /** @var int $chapterPosition */
    protected $chapterPosition;

    /**
     * @return string
     */
    public function getRegex(): string
    {
        return $this->regex;
    }

    /**
     * @param string $regex
     * @return PlatformRegex
     */
    public function setRegex(string $regex): PlatformRegex
    {
        $this->regex = $regex;
        return $this;
    }

    /**
     * @return int
     */
    public function getMangaPosition(): int
    {
        return $this->mangaPosition;
    }

    /**
     * @param int $mangaPosition
     * @return PlatformRegex
     */
    public function setMangaPosition(int $mangaPosition): PlatformRegex
    {
        $this->mangaPosition = $mangaPosition;
        return $this;
    }

    /**
     * @return int
     */
    public function getChapterPosition(): int
    {
        return $this->chapterPosition;
    }

    /**
     * @param int $chapterPosition
     * @return PlatformRegex
     */
    public function setChapterPosition(int $chapterPosition): PlatformRegex
    {
        $this->chapterPosition = $chapterPosition;
        return $this;
    }
}
