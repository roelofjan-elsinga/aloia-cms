<?php

namespace FlatFileCms\Console;

use Carbon\Carbon;
use FlatFileCms\Article;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;

class NewArticle extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'flatfilecms:new:article {--slug=} {--post_date=} {--file_type=md}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new article entry with the additional requirements';

    protected $tasks = [];

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->validateInput();

        $post_entry = $this->generatePostEntry();

        $posts = $this->appendPostToMetaData($post_entry);

        $this->savePostsEntryToFile($posts);

        $this->createPostFile();

        $this->info("Created new post entry for {$this->option('slug')}");
    }

    /**
     * Generate a post entry and set the post as a draft status
     *
     * @return array
     */
    private function generatePostEntry(): array
    {
        return [
            "filename" => "{$this->option('slug')}.{$this->option('file_type')}",
            "postDate" => $this->option('post_date') ?? date('Y-m-d'),
            "description" => "",
            "isPublished" => false,
            "isScheduled" => false
        ];
    }

    /**
     * Append the newly generated post meta data to the meta data collection
     *
     * @param array $post_entry
     * @return Collection
     */
    private function appendPostToMetaData(array $post_entry): Collection
    {
        return Article::raw()->push($post_entry);
    }

    /**
     * Save the posts to the meta data file
     *
     * @param Collection $posts
     */
    private function savePostsEntryToFile(Collection $posts)
    {
        Article::update($posts);
    }

    /**
     * Create the article file from the meta data information
     */
    private function createPostFile(): void
    {
        $this->call('flatfilecms:article:generate');
    }

    /**
     * Validate the command input
     * @throws \Exception
     */
    private function validateInput()
    {
        if (! $this->option('slug')) {
            throw new \Exception("You need to submit a slug using --slug");
        }

        if ($this->option('post_date')) {
            $this->validateDateInput();
        }
    }

    /**
     * Validate the date input to be the correct format
     * @throws \Exception
     */
    private function validateDateInput()
    {
        try {
            Carbon::createFromFormat('Y-m-d', $this->option('post_date'));
        } catch (\Exception $exception) {
            throw new \Exception("You need to submit the date with the following format: Y-m-d");
        }
    }
}
