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
     * @throws \Exception
     */
    public function handle()
    {
        $this->validateInput();

        $this->createNewPost();

        $this->info("Created new post entry for {$this->option('slug')}");
    }

    /**
     * Generate a post entry and set the post as a draft status
     *
     * @return void
     */
    private function createNewPost(): void
    {
        \FlatFileCms\Models\Article::find($this->option('slug'))
            ->setExtension($this->option('file_type'))
            ->setMatter([
                "post_date" => $this->option('post_date') ?? date('Y-m-d'),
                "description" => "",
                "is_published" => false,
                "is_scheduled" => false
            ])
            ->save();
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
