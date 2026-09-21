<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\PolicyPageRequest;
use App\Services\PolicyPageService;

class PolicyPageController extends AdminController
{
    public function __construct(private readonly PolicyPageService $policies) {}

    public function edit(string $policy)
    {
        $definition = $this->policies->definition($policy);
        $item = $this->policies->findOrCreate($policy);
        $payload = $this->policies->formPayload($item, $definition);
        $previewUrl = route($definition['preview_route']);
        $sectionRows = max(count($payload['sections']) + 1, 4);

        return view('admin.policies.edit', [
            'item' => $item,
            'policy' => $policy,
            'definition' => $definition,
            'payload' => $payload,
            'previewUrl' => $previewUrl,
            'sectionRows' => $sectionRows,
        ]);
    }

    public function update(PolicyPageRequest $request, string $policy)
    {
        $definition = $this->policies->definition($policy);
        $this->policies->updatePolicy($policy, $request->validated());

        return $this->success($definition['title'].' updated.', 'admin.policies.edit', ['policy' => $policy]);
    }
}
