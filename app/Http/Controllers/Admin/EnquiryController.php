<?php

namespace App\Http\Controllers\Admin;

use App\Models\Enquiry;
use App\Models\NewsletterSubscriber;
use App\Services\EnquiryService;
use App\Services\NewsletterSubscriberService;
use Illuminate\Http\Request;

class EnquiryController extends AdminController
{
    public function __construct(
        protected EnquiryService $service,
        protected NewsletterSubscriberService $subscribers,
    ) {}

    public function index(Request $request)
    {
        $tab = $request->query('tab') === 'subscriptions' ? 'subscriptions' : 'enquiries';

        if ($tab === 'subscriptions') {
            $items = $this->subscribers->paginate($request->only(['search']));
        } else {
            $items = $this->service->paginate($request->only(['search', 'status']));
        }

        return view('admin.enquiries.index', [
            'tab' => $tab,
            'items' => $items,
            'statuses' => Enquiry::statuses(),
            'newCount' => Enquiry::query()->where('status', 'new')->count(),
            'subscriberCount' => NewsletterSubscriber::query()->count(),
        ]);
    }

    public function show(Enquiry $enquiry)
    {
        $enquiry->markRead();
        $enquiry->refresh();

        return view('admin.enquiries.show', ['item' => $enquiry]);
    }

    public function destroy(Enquiry $enquiry)
    {
        $this->service->delete($enquiry);

        return $this->success('Enquiry removed.', 'admin.enquiries.index');
    }

    public function destroySubscriber(NewsletterSubscriber $subscriber)
    {
        $this->subscribers->delete($subscriber);

        return $this->success('Subscriber removed.', 'admin.enquiries.index', ['tab' => 'subscriptions']);
    }
}
