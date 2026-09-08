@extends('admin.layouts.app')
@section('title', 'Add Expense')
@section('content')
<div class="page-header">
    <div>
        <h1>Add Expense</h1>
        @include('admin.components.breadcrumbs', ['items' => [['label'=>'Expenses','url'=>route('admin.expenses.index')], ['label'=>'Create']]])
    </div>
</div>
@include('admin.components.alerts')
<div class="card">
    <form method="POST" action="{{ route('admin.expenses.store') }}" enctype="multipart/form-data" class="form-grid">
        @csrf

            <div class="form-group">
                <label>Category</label>
                <input type="text" name="category" class="form-control" value="{{ old('category', $item->category ?? '') }}" required>
            </div>
            <div class="form-group">
                <label>Title</label>
                <input type="text" name="title" class="form-control" value="{{ old('title', $item->title ?? '') }}" required>
            </div>
            <div class="form-group">
                <label>Amount</label>
                <input type="number" name="amount" class="form-control" value="{{ old('amount', $item->amount ?? '') }}" required>
            </div>
            <div class="form-group">
                <label>Expense Date</label>
                <input type="date" name="expense_date" class="form-control" value="{{ old('expense_date', $item->expense_date ?? '') }}" required>
            </div>
            <div class="form-group">
                <label>Payment Method</label>
                <input type="text" name="payment_method" class="form-control" value="{{ old('payment_method', $item->payment_method ?? '') }}" >
            </div>
            <div class="form-group">
                <label>Reference</label>
                <input type="text" name="reference" class="form-control" value="{{ old('reference', $item->reference ?? '') }}" >
            </div>
            <div class="form-group">
                <label>Notes</label>
                <textarea name="notes" class="form-control" rows="4" >{{ old('notes', $item->notes ?? '') }}</textarea>
            </div>
            <div class="form-group">
                <label>Attachment</label>
                <input type="file" name="attachment" class="form-control" accept="image/*,.pdf">
                @if(!empty($item?->attachment))
                    <div class="mt-2"><img src="{{ asset('storage/' . $item->attachment) }}" alt="" class="thumb-sm"></div>
                @endif
            </div>
        <div class="form-actions">
            <button class="btn btn-primary">Save</button>
            <a href="{{ route('admin.expenses.index') }}" class="btn btn-ghost">Cancel</a>
        </div>
    </form>
</div>
@endsection