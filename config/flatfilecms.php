<?php

return [

    'articles' => [

        /*
        * This value represents the JSON file that contains all the meta data about a post
        * */
        'data_file' => resource_path('content/articles.json'),

        /*
         * This value represents the location in which the content files are saved.
         * The filename in the config('flatfilecms.articles.data_file') will search in this folder.
         * */
        'data_path' => resource_path('content/articles'),

        /*
         * This value represents the location in which the post images are found.
         * This is used to generate URL's to display the images
         * */
        'image_path' => 'images/articles'

    ],

    'content_blocks' => [

        'data_path' => resource_path('content/blocks')

    ]

];
