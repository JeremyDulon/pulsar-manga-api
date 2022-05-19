<?php

namespace App\MangaPlatform;

interface PlatformInterface
{
    public function getName();

    public function getLanguage();

    public function getBaseUrl();

    public function getHeaders();

//    public function getMangaRegex();

    public function getTitleNode();

    public function getAltTitlesNode();

    public function getStatusNode();

    public function getMainImageNode();

    public function getAuthorNode();

    public function getViewsNode();

    public function getLastUpdatedNode();

    public function getDescriptionNode();

    public function getComicIssuesDataNode();

    public function getComicPagesNode();
}