<?php


namespace FlatFileCms\Console\Migrations;

use FlatFileCms\Models\Article;
use FlatFileCms\Models\ContentBlock;
use FlatFileCms\Models\MetaTag;
use FlatFileCms\Taxonomy\Taxonomy;
use FlatFileCms\Writer\FrontMatterCreator;
use FlatFileCms\Models\Page;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;

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
        // Create the collections folder
        $this->assertCollectionFolderExists();

        // Write all meta data to pages + front matter
        // Move all pages to their respective collections folder
        $this->writeMetaDataToPages();

        // Write all meta data to articles + front matter
        // Move all articles to their respective collections folder
        $this->writeMetaDataToArticles();

        // Write all meta data to content blocks + front matter
        // Move all content blocks to their respective collections folder
        $this->writeMetaDataToContentBlocks();

        // Write all meta data to meta tags + front matter
        $this->writeMetaDataToMetaTags();

        // Apply taxonomy as URL in pages
        $this->writeTaxonomyToPages();
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
        if (!file_exists(Config::get('flatfilecms.pages.file_path'))) {
            return;
        }

        $page_data = json_decode(file_get_contents(Config::get('flatfilecms.pages.file_path')), true);

        foreach ($page_data as $page) {
            $file_content = file_get_contents(Config::get('flatfilecms.pages.folder_path') . "/{$page['filename']}");

            Page::find(pathinfo($page['filename'], PATHINFO_FILENAME))
                ->setExtension(pathinfo($page['filename'], PATHINFO_EXTENSION))
                ->setMatter($page)
                ->setBody($file_content)
                ->save();
        }
    }

    private function writeMetaDataToArticles()
    {
        if (!file_exists(Config::get('flatfilecms.articles.file_path'))) {
            return;
        }

        $page_data = json_decode(file_get_contents(Config::get('flatfilecms.articles.file_path')), true);

        foreach ($page_data as $page) {
            $file_content = file_get_contents(Config::get('flatfilecms.articles.folder_path') . "/{$page['filename']}");

            Article::find(pathinfo($page['filename'], PATHINFO_FILENAME))
                ->setExtension(pathinfo($page['filename'], PATHINFO_EXTENSION))
                ->setMatter($page)
                ->setBody($file_content)
                ->save();
        }
    }

    private function writeMetaDataToContentBlocks()
    {
        if (!file_exists(Config::get('flatfilecms.content_blocks.folder_path'))) {
            return;
        }

        $content_blocks = File::allFiles(Config::get('flatfilecms.content_blocks.folder_path'));

        foreach ($content_blocks as $block) {
            $path_name = $block->getPathname();

            $file_content = file_get_contents($path_name);

            ContentBlock::find(pathinfo($path_name, PATHINFO_FILENAME))
                ->setExtension(pathinfo($path_name, PATHINFO_EXTENSION))
                ->setMatter(['identifier' => pathinfo($path_name, PATHINFO_FILENAME)])
                ->setBody($file_content)
                ->save();
        }
    }

    private function writeMetaDataToMetaTags()
    {
        if (!file_exists(Config::get('flatfilecms.meta_tags.file_path'))) {
            return;
        }

        $page_data = json_decode(file_get_contents(Config::get('flatfilecms.meta_tags.file_path')), true);

        foreach ($page_data as $filename => $page) {
            MetaTag::find($filename)
                ->setExtension('md')
                ->setMatter($page)
                ->save();
        }
    }

    private function writeTaxonomyToPages()
    {
        $pages = \FlatFileCms\Page::all();

        foreach ($pages as $page) {
            $slug = $page->slug(true);

            $file_name = pathinfo($page->filename(), PATHINFO_FILENAME);

            Page::find($file_name)
                ->addMatter('url', "/{$slug}")
                ->save();
        }
    }
}
