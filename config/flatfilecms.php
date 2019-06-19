<?php

return [

    /*
     * This value represents the JSON file that contains all the meta data about a post
     * */
    'article_data_file' => resource_path('content/articles/flatfilecms.json'),

    /*
     * This value represents the location in which the content files are saved.
     * The filename in the config('flatfilecms.article_data_file') will search in this folder.
     * */
    'article_data_path' => resource_path('content/articles'),

    /*
     * This value represents the location in which the post images are found.
     * This is used to generate URL's to display the images
     * */
    'article_image_path' => 'images/articles'

];
