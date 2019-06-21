<?php

namespace FlatFileCms\Commands;

use FlatFileCms\Article;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class GenerateArticleFiles extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'article:generate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate markdown files from the articles config file';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $folder_path = config('flatfilecms.articles.data_path');

        $files = File::allFiles($folder_path);

        $articles = Article::raw();

        foreach ($articles as $article) {
            if (! $this->fileExists($files, $article['filename'])) {
                touch($folder_path . '/' . $article['filename']);

                $this->info("Created {$article['filename']}");
            }
        }
    }

    /**
     * Determine if the file already exists
     *
     * @param $files
     * @param $filename
     * @return bool
     */
    private function fileExists($files, $filename): bool
    {
        $foundFile = collect($files)
            ->filter(function ($file) use ($filename) {
                return $file->getFileName() === $filename;
            })
            ->first();

        return !is_null($foundFile);
    }
}
