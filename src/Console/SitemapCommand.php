<?php

namespace AloiaCms\Console;

use AloiaCms\Models\Contracts\ModelInterface;
use AloiaCms\Models\Traits\Postable;
use AloiaCms\Models\Traits\Updatable;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use SitemapGenerator\SitemapGenerator;

class SitemapCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'aloiacms:sitemap';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate a sitemap for the specified models';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $models = Config::get('aloiacms.seo.sitemap', []);

        $app_url = Config::get('app.url', 'http://locahost:8000');

        $sitemap_path = Config::get('aloiacms.seo.sitemap_path');

        $generator = new SitemapGenerator($app_url);

        foreach ($models as $config) {

            // Determine whether we can use the built-in traits for fetching a "last modified at" date.
            $traits = class_uses_recursive($config['model']);
            $has_update_date = in_array(Updatable::class, $traits);
            $has_post_date = in_array(Postable::class, $traits);

            // Call the all() method to fetch all models of this type
            foreach (call_user_func([$config['model'], 'all']) as $model) {
                $generator->add(
                    $this->replaceIdWithFilename($model, $config['path']),
                    $config['priority'],
                    $this->getLastModifiedAt($has_update_date, $has_post_date, $model),
                    $config['change_frequency'],
                );
            }
        }

        file_put_contents($sitemap_path, $generator->toXML());

        $relative_path = str_replace(base_path() . "/", "", $sitemap_path);

        $this->info("Create a sitemap at: {$relative_path}");
    }

    /**
     *  Get the last modification date, defaults to today's date.
     *
     * @param bool $has_update_date
     * @param bool $has_post_date
     * @param mixed $model
     * @return string
     */
    private function getLastModifiedAt(bool $has_update_date, bool $has_post_date, mixed $model): string
    {
        $last_modified_at = "";

        if ($has_update_date && !is_null($model->getUpdateDate())) {
            $last_modified_at = $model->getUpdateDate()->format('Y-m-d');
        }

        if ($has_post_date && empty($last_modified_at) && !is_null($model->getPostDate())) {
            $last_modified_at = $model->getPostDate()->format('Y-m-d');
        }

        if (!empty($last_modified_at)) {
            return $last_modified_at;
        }

        return date('Y-m-d');
    }

    /**
     * Generate the URL for the given model and path
     *
     * @param ModelInterface $model
     * @param string $path
     * @return string
     */
    protected function replaceIdWithFilename(ModelInterface $model, string $path): string
    {
        return str_replace("{id}", $model->filename(), $path);
    }
}
