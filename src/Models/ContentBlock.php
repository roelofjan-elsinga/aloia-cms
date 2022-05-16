<?php

namespace AloiaCms\Models;

use AloiaCms\Models\Contracts\ModelInterface;

class ContentBlock extends Model implements ModelInterface
{
    protected $folder = 'content_blocks';

    /**
     * Serve as an entry for Facade usage
     *
     * @param string $block_name
     * @return string
     */
    public function get(string $block_name): string
    {
        $instance = self::find($block_name);

        return $instance->body();
    }
}
