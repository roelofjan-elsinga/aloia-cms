<?php

return [

    /*
    * This value represents the folder locations where the collections are saved
    * */
    'collections_path' => resource_path('content/collections'),

    'pages' => [

        'image_path' => 'images/pages'

    ],

    'articles' => [

        /*
         * This value represents the location in which the post images are found.
         * This is used to generate URL's to display the images
         * */
        'image_path' => 'images/articles',

        /*
         * This value represents the prefix in the URL before the slug of the article.
         * This is used to generate URL's for the articles
         * */
        'url_prefix' => 'articles'

    ],

    'uploaded_files' => [

        /*
         * This value represents the folder in which the uploaded files can be found.
         * */
        'folder_path' => public_path('files')

    ],

    'permissions' => [

        /*
         * This value represents the user the webserver uses to serve the static files
         * */
        'user' => env('FILE_OWNER', 'www-data'),

        /*
         * This value represents the user group the webserver user belongs to
         * */
        'group' => env('FILE_GROUP', 'www-data'),

        /*
         * This value represents any other folder/file path you want to update when setting the file permissions
         * */
        'additional_paths' => []

    ],

];
