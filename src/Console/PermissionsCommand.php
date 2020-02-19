<?php


namespace AloiaCms\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;

class PermissionsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'aloiacms:set-permissions {--use-sudo}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Set the correct file permissions for all resource folders';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $resource_paths = [
            Config::get('aloiacms.pages.file_path'),
            Config::get('aloiacms.pages.folder_path'),
            Config::get('aloiacms.articles.file_path'),
            Config::get('aloiacms.articles.folder_path'),
            Config::get('aloiacms.content_blocks.folder_path'),
            Config::get('aloiacms.meta_tags.file_path'),
            Config::get('aloiacms.taxonomy.file_path'),
            Config::get('aloiacms.uploaded_files.folder_path'),
        ];

        $resource_paths = array_merge(
            $resource_paths,
            Config::get('aloiacms.permissions.additional_paths')
        );

        $user = Config::get('aloiacms.permissions.user');

        $group = Config::get('aloiacms.permissions.group');

        $sudo_prefix = $this->option('use-sudo') ? 'sudo ' : '';

        foreach ($resource_paths as $resource_path) {
            $this->info("Setting owner of \"{$resource_path}\" to {$user}:{$group}");

            exec("{$sudo_prefix}chown -R {$user}:{$group} {$resource_path}");
        }
    }
}
