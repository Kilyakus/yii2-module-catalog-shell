<?php
namespace kilyakus\shell\directory;

class DirectoryModule extends \kilyakus\modules\components\Module
{
    public $icon;
    
    public $settings = [
        'enableMenu' => true,
        'enableSubmodule' => false,
        'parentSubmodule' => '',
        'enableCategory' => true,
        'categoryThumb' => true,
        'categoryMultiple' => false,
        'enableForumAttach' => false,
        'itemsInFolder' => false,
        'itemThumb' => true,
        'itemSale' => false,
        'enablePhotos' => true,
        'enableVideo' => true,
        'enableMaps' => true,
        'enableComments' => true,
        'enableTags' => true,
        'enableSeasons' => false,
        'enablePermissions' => false,
    ];

    public $redirects = [
        'create' => 'edit',
        'edit' => 'edit',
        'seasons' => 'seasons',
        'photos' => 'photos',
        'videos' => 'videos',
        'maps' => 'maps',
        'comments' => 'comments',
    ];

    public static $installConfig = [
        'title' => [
            'en' => 'Catalog',
            'ru' => 'Каталог',
        ],
        'icon' => 'list-alt',
        'order_num' => 100,
    ];
}