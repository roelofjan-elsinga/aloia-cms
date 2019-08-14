<?php


namespace FlatFileCms\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;

class PermissionsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'flatfilecms:set-permissions';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Set the correct file permissions for all resource folders';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $resource_paths = [
            Config::get('flatfilecms.pages.file_path'),
            Config::get('flatfilecms.pages.folder_path'),
            Config::get('flatfilecms.articles.file_path'),
            Config::get('flatfilecms.articles.folder_path'),
            Config::get('flatfilecms.content_blocks.folder_path'),
            Config::get('flatfilecms.meta_tags.file_path'),
            Config::get('flatfilecms.taxonomy.file_path'),
            Config::get('flatfilecms.uploaded_files.folder_path'),
        ];

        $user = Config::get('flatfilecms.permissions.user');

        $group = Config::get('flatfilecms.permissions.group');

        foreach($resource_paths as $resource_path) {

            $this->info("Setting owner of \"{$resource_path}\" to {$user}:{$group}");

            system("chown -R {$user}:{$group} {$resource_path}");

        }
    }
}