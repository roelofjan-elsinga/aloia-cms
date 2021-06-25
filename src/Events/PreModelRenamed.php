<?php

namespace AloiaCms\Events;

use AloiaCms\Models\Contracts\ModelInterface;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PreModelRenamed
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public ModelInterface $model;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(ModelInterface $model)
    {
        $this->model = $model;
    }
}
