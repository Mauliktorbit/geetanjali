<?php

namespace App\Repositories;

use App\Models\NewsletterSubscriber;

class NewsletterSubscriberRepository extends BaseRepository
{
    protected array $searchable = [
        'email',
        'source',
    ];

    public function __construct(NewsletterSubscriber $model)
    {
        parent::__construct($model);
    }
}
