<?php

it('should create a new folder if it doesn\'t exist', function () {
    $test_tutorials_root_path = \Illuminate\Support\Facades\Config::get('aloiacms.collections_path') . '/test-tutorials';

    expect(file_exists($test_tutorials_root_path))->toBe(false);

    \AloiaCms\Writer\FolderCreator::forPath($test_tutorials_root_path);

    expect(file_exists($test_tutorials_root_path))->toBe(true);
})->only();
