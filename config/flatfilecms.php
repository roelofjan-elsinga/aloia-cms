<?php

return [

    'pages' => [

        /*
        * This value represents the JSON file that contains all the meta data about a post
        * */
        'file_path' => resource_path('content/pages.json'),

        /*
         * This value represents the location in which the content files are saved.
         * The filename in the config('flatfilecms.articles.file_path') will search in this folder.
         * */
        'folder_path' => resource_path('content/pages'),

        /*
         * This value represents the location in which the post images are found.
         * This is used to generate URL's to display the images
         * */
        'image_path' => 'images/pages'

    ],

    'articles' => [

        /*
        * This value represents the JSON file that contains all the meta data about a post
        * */
        'file_path' => resource_path('content/articles.json'),

        /*
         * This value represents the location in which the content files are saved.
         * The filename in the config('flatfilecms.articles.file_path') will search in this folder.
         * */
        'folder_path' => resource_path('content/articles'),

        /*
         * This value represents the location in which the post images are found.
         * This is used to generate URL's to display the images
         * */
        'image_path' => 'images/articles'

    ],

    'content_blocks' => [

        /*
         * This value represents the folder in which the content blocks can be found.
         * This is used to resolve the content with the BlockFacade
         * */
        'folder_path' => resource_path('content/blocks')

    ],

    'meta_tags' => [

        /*
         * This value represents the path to the file that holds the meta tag data.
         * This is used to generate meta tags for the website
         * */
        'file_path' => resource_path('content/metatags.json')

    ]

];
