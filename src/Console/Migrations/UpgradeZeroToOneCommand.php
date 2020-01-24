<?php


namespace FlatFileCms\Console\Migrations;

use FlatFileCms\Writer\FrontMatterCreator;
use FlatFileCms\Models\Page;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;

class UpgradeZeroToOneCommand extends Command
{
    protected $signature = 'flatfilecms:upgrade:0-to-1';

    protected $description = 'Upgrade the files and folder structures from version 0.x to 1.x';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $this->assertCollectionFolderExists();

        $this->writeMetaDataToPages();

        // Write all meta data to pages + front matter
        // Write all meta data to articles + front matter
        // Write all meta data to content blocks + front matter
        // Create the collections folder
        // Move all articles to their respective collections folder
        // Move all pages to their respective collections folder
        // Move all content blocks to their respective collections folder
        // Remove the old
    }

    private function assertCollectionFolderExists()
    {
        $collections_path = Config::get('flatfilecms.collections_path');
        $collections_folder_exists = file_exists($collections_path);

        if ($collections_folder_exists) {
            return;
        }

        if ($this->confirm("The collections folder doesn't exist, do you want to create it at {$collections_path}?")) {
            mkdir($collections_path);
        }
    }

    private function writeMetaDataToPages()
    {
        $page_data = json_decode(file_get_contents(Config::get('flatfilecms.pages.file_path')), true);

        foreach ($page_data as $page) {
            $file_content = file_get_contents(Config::get('flatfilecms.pages.folder_path') . "/{$page['filename']}");

            Page::open(pathinfo($page['filename'], PATHINFO_FILENAME))
                ->setExtension(pathinfo($page['filename'], PATHINFO_EXTENSION))
                ->setMatter($page_data)
                ->setBody($file_content)
                ->save();
        }
    }
}
