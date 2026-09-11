<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Services\StorefrontCatalogService;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\View\View;

class SearchController extends Controller
{
    public function __construct(private readonly StorefrontCatalogService $catalog) {}

    public function index(Request $request): View
    {
        $query = trim(preg_replace('/\s+/', ' ', (string) $request->input('q', '')) ?? '');

        if ($query !== '' && mb_strlen($query) >= 2) {
            $paginator = $this->catalog->paginateSearch($query);
            $products = $paginator->through(fn ($product) => $this->catalog->toCard($product));
        } else {
            $products = new LengthAwarePaginator([], 0, StorefrontCatalogService::PER_PAGE, 1, [
                'path' => $request->url(),
                'query' => $request->query(),
            ]);
        }

        $tooShort = $query !== '' && mb_strlen($query) < 2;

        return view('frontend.search.index', [
            'query' => $query,
            'tooShort' => $tooShort,
            'products' => $products,
            'breadcrumb' => [
                ['label' => 'Home', 'url' => route('home')],
                ['label' => 'Search', 'url' => null],
            ],
        ]);
    }
}
