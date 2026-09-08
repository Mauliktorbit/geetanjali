<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\GiftCardRequest;
use App\Models\GiftCard;
use App\Services\GiftCardService;
use Illuminate\Http\Request;

class GiftCardController extends AdminController
{
    public function __construct(protected GiftCardService $service) {}

    public function index(Request $request)
    {
        $items = $this->service->paginate($request->all());
        return view('admin.gift-cards.index', compact('items'));
    }

    public function create()
    {
        return view('admin.gift-cards.create');
    }

    public function store(GiftCardRequest $request)
    {
        $data = $request->validated();
        foreach (['logo','image','banner','avatar','attachment','og_image','mobile_image'] as $fileField) {
            if ($request->hasFile($fileField)) {
                $data[$fileField] = $request->file($fileField)->store('uploads/gift-cards', 'public');
            }
        }
        $this->service->create($data);
        return $this->success('GiftCard created successfully.', 'admin.gift-cards.index');
    }

    public function show(GiftCard $giftCard)
    {
        return view('admin.gift-cards.show', ['item' => $giftCard]);
    }

    public function edit(GiftCard $giftCard)
    {
        return view('admin.gift-cards.edit', ['item' => $giftCard]);
    }

    public function update(GiftCardRequest $request, GiftCard $giftCard)
    {
        $data = $request->validated();
        foreach (['logo','image','banner','avatar','attachment','og_image','mobile_image'] as $fileField) {
            if ($request->hasFile($fileField)) {
                $data[$fileField] = $request->file($fileField)->store('uploads/gift-cards', 'public');
            }
        }
        $this->service->update($giftCard, $data);
        return $this->success('GiftCard updated successfully.', 'admin.gift-cards.index');
    }

    public function destroy(GiftCard $giftCard)
    {
        $this->service->delete($giftCard);
        return $this->success('GiftCard deleted successfully.');
    }

    public function bulk(Request $request)
    {
        $ids = $request->input('ids', []);
        $action = $request->input('action');
        if (!$ids) {
            return $this->error('Please select at least one item.');
        }
        if ($action === 'delete') {
            $this->service->bulkDelete($ids);
            return $this->success('Selected items deleted.');
        }
        if ($action === 'activate') {
            $this->service->bulkUpdate($ids, ['is_active' => true]);
            return $this->success('Selected items activated.');
        }
        if ($action === 'deactivate') {
            $this->service->bulkUpdate($ids, ['is_active' => false]);
            return $this->success('Selected items deactivated.');
        }
        return $this->error('Invalid bulk action.');
    }
}
