<?php

namespace App\Http\Controllers\Admin;

use App\Models\Enquiry;
use App\Services\EnquiryService;
use Illuminate\Http\Request;

class EnquiryController extends AdminController
{
    public function __construct(protected EnquiryService $service) {}

    public function index(Request $request)
    {
        $items = $this->service->paginate($request->all());

        return view('admin.enquiries.index', compact('items'));
    }

    public function show(Enquiry $enquiry)
    {
        if ($enquiry->status === 'new') {
            $this->service->update($enquiry, [
                'status' => 'read',
                'read_at' => now(),
            ]);
            $enquiry->refresh();
        }

        return view('admin.enquiries.show', ['item' => $enquiry]);
    }

    public function update(Request $request, Enquiry $enquiry)
    {
        $data = $request->validate([
            'status' => 'required|in:'.implode(',', Enquiry::STATUSES),
            'admin_notes' => 'nullable|string|max:5000',
        ]);

        if ($data['status'] === 'read' && ! $enquiry->read_at) {
            $data['read_at'] = now();
        }

        if ($data['status'] === 'replied' && ! $enquiry->replied_at) {
            $data['replied_at'] = now();
        }

        $this->service->update($enquiry, $data);

        return $this->success('Enquiry updated successfully.');
    }

    public function destroy(Enquiry $enquiry)
    {
        $this->service->delete($enquiry);

        return $this->success('Enquiry deleted successfully.', 'admin.enquiries.index');
    }

    public function bulk(Request $request)
    {
        $ids = $request->input('ids', []);
        $action = $request->input('action');

        if (! $ids) {
            return $this->error('Please select at least one enquiry.');
        }

        if ($action === 'delete') {
            $this->service->bulkDelete($ids);

            return $this->success('Selected enquiries deleted.');
        }

        if (in_array($action, Enquiry::STATUSES, true)) {
            $payload = ['status' => $action];
            if ($action === 'read') {
                $payload['read_at'] = now();
            }
            if ($action === 'replied') {
                $payload['replied_at'] = now();
            }
            $this->service->bulkUpdate($ids, $payload);

            return $this->success('Selected enquiries updated.');
        }

        return $this->error('Invalid bulk action.');
    }
}
