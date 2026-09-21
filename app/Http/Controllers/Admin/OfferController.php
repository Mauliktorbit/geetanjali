<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\OfferRequest;
use App\Models\Offer;
use App\Models\OfferCategory;
use App\Services\OfferService;
use Illuminate\Http\Request;

class OfferController extends AdminController
{
    public function __construct(protected OfferService $service) {}

    public function index(Request $request)
    {
        $items = $this->service->paginate($request->only(['search', 'status', 'category']));

        return view('admin.offers.index', [
            'items' => $items,
            'categories' => $this->categories(),
        ]);
    }

    public function create()
    {
        return view('admin.offers.create', $this->formData());
    }

    public function store(OfferRequest $request)
    {
        $this->service->save($this->payload($request));

        return $this->success('Offer published. Customers can see it on the Offers page.', 'admin.offers.index');
    }

    public function show(Offer $offer)
    {
        $offer->load('coupon');

        return view('admin.offers.show', ['item' => $offer]);
    }

    public function edit(Offer $offer)
    {
        return view('admin.offers.edit', $this->formData(['item' => $offer]));
    }

    public function update(OfferRequest $request, Offer $offer)
    {
        $this->service->save($this->payload($request), $offer);

        return $this->success('Offer updated.', 'admin.offers.index');
    }

    public function destroy(Offer $offer)
    {
        $this->service->delete($offer);

        return $this->success('Offer removed.', 'admin.offers.index');
    }

    /**
     * @return array<string, mixed>
     */
    private function payload(OfferRequest $request): array
    {
        $data = $request->validated();

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('uploads/offers', 'public');
        } elseif ($request->boolean('remove_image')) {
            $data['image'] = null;
        } else {
            unset($data['image']);
        }

        return $data;
    }

    /**
     * @return \Illuminate\Support\Collection<int, OfferCategory>
     */
    private function categories()
    {
        return OfferCategory::query()->ordered()->get();
    }

    /**
     * @param  array<string, mixed>  $extra
     * @return array<string, mixed>
     */
    private function formData(array $extra = []): array
    {
        return array_merge([
            'categories' => $this->categories(),
        ], $extra);
    }
}
