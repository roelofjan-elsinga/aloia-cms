<?php

namespace AloiaCms\Events;

use AloiaCms\Models\Contracts\ModelInterface;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ModelRenameFailed
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public ModelInterface $model;
    public string $new_filename;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(ModelInterface $model, string $new_filename)
    {
        $this->model = $model;
        $this->new_filename = $new_filename;
    }
}
