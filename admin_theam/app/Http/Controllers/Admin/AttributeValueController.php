<?php

namespace App\Http\Controllers\Admin;

use App\Models\Attribute;
use App\Models\AttributeValue;
use Illuminate\Http\Request;

class AttributeValueController extends AdminController
{
    public function index(Attribute $attribute)
    {
        $items = $attribute->values()->orderBy('sort_order')->paginate(50);

        return view('admin.attribute-values.index', compact('attribute', 'items'));
    }

    public function store(Request $request, Attribute $attribute)
    {
        $data = $request->validate([
            'value' => ['required', 'string', 'max:255'],
            'color_code' => ['nullable', 'string', 'max:20'],
            'sort_order' => ['nullable', 'integer'],
        ]);

        $attribute->values()->create($data);

        return $this->success('Attribute value created.');
    }

    public function update(Request $request, Attribute $attribute, AttributeValue $attributeValue)
    {
        abort_unless($attributeValue->attribute_id === $attribute->id, 404);

        $data = $request->validate([
            'value' => ['required', 'string', 'max:255'],
            'color_code' => ['nullable', 'string', 'max:20'],
            'sort_order' => ['nullable', 'integer'],
        ]);

        $attributeValue->update($data);

        return $this->success('Attribute value updated.');
    }

    public function destroy(Attribute $attribute, AttributeValue $attributeValue)
    {
        abort_unless($attributeValue->attribute_id === $attribute->id, 404);
        $attributeValue->delete();

        return $this->success('Attribute value deleted.');
    }
}
