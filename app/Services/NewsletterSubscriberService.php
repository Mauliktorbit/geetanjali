<?php

namespace App\Services;

use App\Models\NewsletterSubscriber;
use App\Repositories\NewsletterSubscriberRepository;

class NewsletterSubscriberService extends BaseService
{
    public function __construct(
        NewsletterSubscriberRepository $repository,
        protected NotificationService $notifications,
    ) {
        parent::__construct($repository);
    }

    public function subscribe(string $email, string $source = 'website'): NewsletterSubscriber
    {
        $email = strtolower(trim($email));
        $existing = NewsletterSubscriber::query()->where('email', $email)->first();

        if ($existing) {
            if (! $existing->is_active) {
                $existing->update(['is_active' => true, 'source' => $source]);
                $this->notifications->notifyNewSubscriber($existing->fresh());
            }

            return $existing->fresh();
        }

        $subscriber = $this->create([
            'email' => $email,
            'source' => $source,
            'is_active' => true,
        ]);

        $this->notifications->notifyNewSubscriber($subscriber);

        return $subscriber;
    }
}
