<?php

namespace App\Repositories;

use App\Models\NewsletterSubscriber;

class NewsletterSubscriberRepository extends BaseRepository
{
    protected array $searchable = [
        'email',
        'source',
    ];

    protected array $sortable = [
        'date' => 'created_at',
    ];

    public function __construct(NewsletterSubscriber $model)
    {
        parent::__construct($model);
    }
}
