<?php

namespace App\Services;

use App\Models\Enquiry;
use App\Repositories\EnquiryRepository;

class EnquiryService extends BaseService
{
    public function __construct(
        EnquiryRepository $repository,
        protected NotificationService $notifications,
    ) {
        parent::__construct($repository);
    }

    public function submit(array $data): Enquiry
    {
        $enquiry = $this->create([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'subject' => $data['subject'] ?? null,
            'message' => $data['message'],
            'status' => 'new',
        ]);

        $this->notifications->notifyNewEnquiry($enquiry);

        return $enquiry;
    }
}
