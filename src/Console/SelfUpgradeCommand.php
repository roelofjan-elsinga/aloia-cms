<?php


namespace FlatFileCms\Console;

use Illuminate\Console\Command;

class SelfUpgradeCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'flatfilecms:self:upgrade';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Upgrade to new version of the CMS';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        system("composer require roelofjan-elsinga/flat-file-cms");
    }
}