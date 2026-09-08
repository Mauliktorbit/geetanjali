<?php

namespace App\Http\Controllers\Admin;

use App\Models\ProductQuestion;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProductQuestionController extends AdminController
{
    public function index(Request $request)
    {
        $query = ProductQuestion::with(['product', 'customer', 'assignee'])->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('question', 'like', "%{$search}%")
                    ->orWhere('customer_name', 'like', "%{$search}%");
            });
        }

        $items = $query->paginate(20)->withQueryString();
        $staff = User::where('is_staff', true)->where('is_active', true)->orderBy('name')->get(['id', 'name']);

        return view('admin.product-questions.index', compact('items', 'staff'));
    }

    public function answer(Request $request, ProductQuestion $productQuestion)
    {
        $request->validate(['answer' => ['required', 'string']]);

        $productQuestion->update([
            'answer' => $request->input('answer'),
            'answered_by' => Auth::id(),
            'answered_at' => now(),
            'status' => 'answered',
            'notify_customer' => $request->boolean('notify_customer', true),
            'is_faq' => $request->boolean('is_faq'),
        ]);

        return $this->success('Answer saved.');
    }

    public function publish(ProductQuestion $productQuestion)
    {
        $productQuestion->update(['status' => 'published']);

        return $this->success('Question published.');
    }

    public function hide(ProductQuestion $productQuestion)
    {
        $productQuestion->update(['status' => 'hidden']);

        return $this->success('Question hidden.');
    }

    public function assign(Request $request, ProductQuestion $productQuestion)
    {
        $request->validate(['assigned_to' => ['required', 'exists:users,id']]);
        $productQuestion->update(['assigned_to' => $request->input('assigned_to')]);

        return $this->success('Question assigned.');
    }
}
