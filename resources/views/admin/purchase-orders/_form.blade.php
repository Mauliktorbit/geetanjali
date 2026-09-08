<div class="form-grid">
<div class="form-group"><label>Supplier</label><select name="supplier_id" class="form-control" required>@foreach($suppliers as $s)<option value="{{ $s->id }}" @selected(old('supplier_id',$item->supplier_id ?? '')==$s->id)>{{ $s->name }}</option>@endforeach</select></div>
<div class="form-group"><label>Warehouse</label><select name="warehouse_id" class="form-control" required>@foreach($warehouses as $w)<option value="{{ $w->id }}" @selected(old('warehouse_id',$item->warehouse_id ?? '')==$w->id)>{{ $w->name }}</option>@endforeach</select></div>
<div class="form-group"><label>Order date</label><input type="date" name="order_date" class="form-control" value="{{ old('order_date', optional($item->order_date ?? null)->format('Y-m-d') ?? now()->toDateString()) }}"></div>
<div class="form-group"><label>Expected</label><input type="date" name="expected_date" class="form-control" value="{{ old('expected_date', optional($item->expected_date ?? null)->format('Y-m-d')) }}"></div>
<div class="form-group"><label>Shipping cost</label><input type="number" step="0.01" name="shipping_cost" class="form-control" value="{{ old('shipping_cost',$item->shipping_cost ?? 0) }}"></div>
<div class="form-group span-2"><label>Notes</label><textarea name="notes" class="form-control">{{ old('notes',$item->notes ?? '') }}</textarea></div>
</div>
<h3>Items</h3>
<table class="data-table"><thead><tr><th>Product</th><th>Qty</th><th>Unit cost</th><th>Tax</th></tr></thead><tbody>
@php $lines = old('items', $item->items ?? [['product_id'=>'','quantity'=>1,'unit_cost'=>0,'tax_amount'=>0]]); @endphp
@foreach($lines as $i=>$line)
@php $line = is_array($line)?$line:$line->toArray(); @endphp
<tr>
<td><select name="items[{{ $i }}][product_id]" class="form-control" required>@foreach($products as $p)<option value="{{ $p->id }}" @selected(($line['product_id']??null)==$p->id)>{{ $p->name }}</option>@endforeach</select></td>
<td><input type="number" name="items[{{ $i }}][quantity]" class="form-control" value="{{ $line['quantity'] ?? 1 }}" min="1"></td>
<td><input type="number" step="0.01" name="items[{{ $i }}][unit_cost]" class="form-control" value="{{ $line['unit_cost'] ?? 0 }}"></td>
<td><input type="number" step="0.01" name="items[{{ $i }}][tax_amount]" class="form-control" value="{{ $line['tax_amount'] ?? 0 }}"></td>
</tr>
@endforeach
</tbody></table>