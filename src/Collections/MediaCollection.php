<?php

namespace AloiaCms\Collections;

use Illuminate\Support\Collection;

class MediaCollection extends Collection
{
    public function onlyFullSize()
    {
        return $this
            ->filter(function (\SplFileInfo $image) {
                return strpos($image->getFilename(), '_w300') === false;
            });
    }
}
