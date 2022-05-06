<?php

return [

    /*
     * This value represents the folder locations where the collections are saved
     * */
    'collections_path' => storage_path('app/collections'),

    /*
     * The seo key allows you to customize certain SEO specific generator features
     * */
    'seo' => [

        /*
         * Specify the path at which you'd like the sitemap to be generated
         */

        'sitemap_path' => public_path('sitemap.xml'),

        /*
         * Specify which models you want to include in your sitemap, and at which URL they can be found
         */

        'sitemap' => [

            [
                'model' => \AloiaCms\Models\Page::class,
                'path' => '/{id}',
                'priority' => 0.9,
                'change_frequency' => \AloiaCms\Seo\Sitemap\ChangeFrequency::Monthly,
            ],

            [
                'model' => \AloiaCms\Models\Article::class,
                'path' => '/articles/{id}',
                'priority' => 0.8,
                'change_frequency' => \AloiaCms\Seo\Sitemap\ChangeFrequency::Weekly,
            ]

        ],

    ]

];
