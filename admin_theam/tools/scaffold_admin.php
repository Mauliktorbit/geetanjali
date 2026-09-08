<?php
/**
 * One-time scaffold generator for the e-commerce admin panel.
 * Run: php tools/scaffold_admin.php
 */

$base = dirname(__DIR__);

function ensureDir(string $path): void
{
    if (! is_dir($path)) {
        mkdir($path, 0755, true);
    }
}

function writeFile(string $path, string $content): void
{
    ensureDir(dirname($path));
    file_put_contents($path, $content);
    echo "Wrote {$path}\n";
}

$modules = [
    // resource => [model, table, searchable, fillable snippet, relations]
    'brands' => [
        'model' => 'Brand',
        'searchable' => ['name', 'slug'],
        'fillable' => ['name','slug','logo','description','seo_title','seo_description','seo_keywords','is_active','sort_order'],
        'casts' => ['is_active' => 'boolean'],
        'softDeletes' => true,
        'fields' => [
            ['name','text',true], ['slug','text',true], ['logo','file',false],
            ['description','textarea',false], ['seo_title','text',false], ['seo_description','textarea',false],
            ['seo_keywords','text',false], ['sort_order','number',false], ['is_active','checkbox',false],
        ],
    ],
    'categories' => [
        'model' => 'Category',
        'searchable' => ['name', 'slug'],
        'fillable' => ['parent_id','name','slug','image','banner','description','seo_title','seo_description','seo_keywords','display_order','is_active'],
        'casts' => ['is_active' => 'boolean'],
        'softDeletes' => true,
        'fields' => [
            ['parent_id','number',false], ['name','text',true], ['slug','text',true],
            ['image','file',false], ['banner','file',false], ['description','textarea',false],
            ['seo_title','text',false], ['seo_description','textarea',false], ['seo_keywords','text',false],
            ['display_order','number',false], ['is_active','checkbox',false],
        ],
    ],
    'attributes' => [
        'model' => 'Attribute',
        'searchable' => ['name', 'slug'],
        'fillable' => ['name','slug','type','is_active','sort_order'],
        'casts' => ['is_active' => 'boolean'],
        'fields' => [
            ['name','text',true], ['slug','text',true], ['type','text',true],
            ['sort_order','number',false], ['is_active','checkbox',false],
        ],
    ],
    'tags' => [
        'model' => 'Tag',
        'searchable' => ['name', 'slug'],
        'fillable' => ['name','slug'],
        'fields' => [['name','text',true], ['slug','text',true]],
    ],
    'tax-rates' => [
        'model' => 'TaxRate',
        'searchable' => ['name', 'hsn_sac'],
        'fillable' => ['name','hsn_sac','cgst','sgst','igst','is_inclusive','is_active'],
        'casts' => ['is_inclusive'=>'boolean','is_active'=>'boolean','cgst'=>'decimal:2','sgst'=>'decimal:2','igst'=>'decimal:2'],
        'fields' => [
            ['name','text',true], ['hsn_sac','text',false], ['cgst','number',false],
            ['sgst','number',false], ['igst','number',false], ['is_inclusive','checkbox',false], ['is_active','checkbox',false],
        ],
    ],
    'shipping-classes' => [
        'model' => 'ShippingClass',
        'searchable' => ['name', 'slug'],
        'fillable' => ['name','slug','description','cost','is_active'],
        'casts' => ['cost'=>'decimal:2','is_active'=>'boolean'],
        'fields' => [
            ['name','text',true], ['slug','text',true], ['description','textarea',false],
            ['cost','number',false], ['is_active','checkbox',false],
        ],
    ],
    'warehouses' => [
        'model' => 'Warehouse',
        'searchable' => ['name', 'code', 'city'],
        'fillable' => ['name','code','address','city','state','country','pincode','phone','priority','is_default','is_active'],
        'casts' => ['is_default'=>'boolean','is_active'=>'boolean'],
        'fields' => [
            ['name','text',true], ['code','text',true], ['address','text',false], ['city','text',false],
            ['state','text',false], ['country','text',false], ['pincode','text',false], ['phone','text',false],
            ['priority','number',false], ['is_default','checkbox',false], ['is_active','checkbox',false],
        ],
    ],
    'suppliers' => [
        'model' => 'Supplier',
        'searchable' => ['name','company_name','email','phone','gstin'],
        'fillable' => ['name','company_name','email','phone','gstin','address','city','state','country','pincode','contact_person','lead_time_days','rating','outstanding','notes','is_active'],
        'casts' => ['rating'=>'decimal:2','outstanding'=>'decimal:2','is_active'=>'boolean'],
        'softDeletes' => true,
        'fields' => [
            ['name','text',true], ['company_name','text',false], ['email','email',false], ['phone','text',false],
            ['gstin','text',false], ['address','text',false], ['city','text',false], ['state','text',false],
            ['country','text',false], ['pincode','text',false], ['contact_person','text',false],
            ['lead_time_days','number',false], ['notes','textarea',false], ['is_active','checkbox',false],
        ],
    ],
    'customer-groups' => [
        'model' => 'CustomerGroup',
        'searchable' => ['name','slug'],
        'fillable' => ['name','slug','discount_percent','pricing_rules','payment_terms_days','moq','shipping_rates','credit_limit','is_active'],
        'casts' => ['discount_percent'=>'decimal:2','pricing_rules'=>'array','shipping_rates'=>'array','credit_limit'=>'decimal:2','is_active'=>'boolean'],
        'fields' => [
            ['name','text',true], ['slug','text',true], ['discount_percent','number',false],
            ['payment_terms_days','number',false], ['moq','number',false], ['credit_limit','number',false], ['is_active','checkbox',false],
        ],
    ],
    'coupons' => [
        'model' => 'Coupon',
        'searchable' => ['code','name'],
        'fillable' => ['code','name','discount_type','discount_value','starts_at','ends_at','usage_limit','usage_count','per_customer_limit','minimum_cart','maximum_discount','included_products','excluded_products','included_categories','customer_groups','new_customers_only','payment_methods','locations','is_stackable','meta','is_active'],
        'casts' => [
            'discount_value'=>'decimal:2','starts_at'=>'datetime','ends_at'=>'datetime','minimum_cart'=>'decimal:2',
            'maximum_discount'=>'decimal:2','included_products'=>'array','excluded_products'=>'array','included_categories'=>'array',
            'customer_groups'=>'array','payment_methods'=>'array','locations'=>'array','meta'=>'array',
            'new_customers_only'=>'boolean','is_stackable'=>'boolean','is_active'=>'boolean',
        ],
        'softDeletes' => true,
        'fields' => [
            ['code','text',true], ['name','text',true], ['discount_type','text',true], ['discount_value','number',true],
            ['starts_at','datetime-local',false], ['ends_at','datetime-local',false], ['usage_limit','number',false],
            ['per_customer_limit','number',false], ['minimum_cart','number',false], ['maximum_discount','number',false],
            ['new_customers_only','checkbox',false], ['is_stackable','checkbox',false], ['is_active','checkbox',false],
        ],
    ],
    'banners' => [
        'model' => 'Banner',
        'searchable' => ['title','type'],
        'fillable' => ['title','type','image','mobile_image','link','content','starts_at','ends_at','sort_order','is_active'],
        'casts' => ['starts_at'=>'datetime','ends_at'=>'datetime','is_active'=>'boolean'],
        'fields' => [
            ['title','text',true], ['type','text',true], ['image','file',false], ['mobile_image','file',false],
            ['link','text',false], ['content','textarea',false], ['starts_at','datetime-local',false],
            ['ends_at','datetime-local',false], ['sort_order','number',false], ['is_active','checkbox',false],
        ],
    ],
    'campaigns' => [
        'model' => 'Campaign',
        'searchable' => ['name','channel','subject'],
        'fillable' => ['name','channel','type','subject','content','segment','status','scheduled_at','sent_at','sent_count','created_by'],
        'casts' => ['segment'=>'array','scheduled_at'=>'datetime','sent_at'=>'datetime'],
        'fields' => [
            ['name','text',true], ['channel','text',true], ['type','text',false], ['subject','text',false],
            ['content','textarea',false], ['status','text',false], ['scheduled_at','datetime-local',false],
        ],
    ],
    'pages' => [
        'model' => 'Page',
        'searchable' => ['title','slug','type'],
        'fillable' => ['title','slug','type','content','seo_title','seo_description','canonical','og_image','is_active'],
        'casts' => ['is_active'=>'boolean'],
        'fields' => [
            ['title','text',true], ['slug','text',true], ['type','text',false], ['content','textarea',false],
            ['seo_title','text',false], ['seo_description','textarea',false], ['canonical','text',false],
            ['og_image','file',false], ['is_active','checkbox',false],
        ],
    ],
    'blogs' => [
        'model' => 'Blog',
        'searchable' => ['title','slug'],
        'fillable' => ['title','slug','image','excerpt','content','seo_title','seo_description','is_published','published_at','author_id'],
        'casts' => ['is_published'=>'boolean','published_at'=>'datetime'],
        'fields' => [
            ['title','text',true], ['slug','text',true], ['image','file',false], ['excerpt','textarea',false],
            ['content','textarea',false], ['seo_title','text',false], ['seo_description','textarea',false],
            ['is_published','checkbox',false], ['published_at','datetime-local',false],
        ],
    ],
    'faqs' => [
        'model' => 'Faq',
        'searchable' => ['question','category'],
        'fillable' => ['question','answer','category','sort_order','is_active'],
        'casts' => ['is_active'=>'boolean'],
        'fields' => [
            ['question','text',true], ['answer','textarea',true], ['category','text',false],
            ['sort_order','number',false], ['is_active','checkbox',false],
        ],
    ],
    'testimonials' => [
        'model' => 'Testimonial',
        'searchable' => ['name','content'],
        'fillable' => ['name','designation','avatar','content','rating','is_active','sort_order'],
        'casts' => ['is_active'=>'boolean'],
        'fields' => [
            ['name','text',true], ['designation','text',false], ['avatar','file',false],
            ['content','textarea',true], ['rating','number',false], ['sort_order','number',false], ['is_active','checkbox',false],
        ],
    ],
    'store-locations' => [
        'model' => 'StoreLocation',
        'searchable' => ['name','city','phone'],
        'fillable' => ['name','address','city','state','pincode','phone','email','latitude','longitude','is_active'],
        'casts' => ['latitude'=>'decimal:7','longitude'=>'decimal:7','is_active'=>'boolean'],
        'fields' => [
            ['name','text',true], ['address','text',true], ['city','text',false], ['state','text',false],
            ['pincode','text',false], ['phone','text',false], ['email','email',false], ['is_active','checkbox',false],
        ],
    ],
    'menus' => [
        'model' => 'Menu',
        'searchable' => ['name','location'],
        'fillable' => ['name','location','is_active'],
        'casts' => ['is_active'=>'boolean'],
        'fields' => [['name','text',true], ['location','text',true], ['is_active','checkbox',false]],
    ],
    'seo-redirects' => [
        'model' => 'SeoRedirect',
        'searchable' => ['from_path','to_path'],
        'fillable' => ['from_path','to_path','status_code','is_active'],
        'casts' => ['is_active'=>'boolean'],
        'fields' => [
            ['from_path','text',true], ['to_path','text',true], ['status_code','number',false], ['is_active','checkbox',false],
        ],
    ],
    'gift-cards' => [
        'model' => 'GiftCard',
        'searchable' => ['code'],
        'fillable' => ['code','initial_balance','balance','customer_id','expires_at','is_active'],
        'casts' => ['initial_balance'=>'decimal:2','balance'=>'decimal:2','expires_at'=>'datetime','is_active'=>'boolean'],
        'fields' => [
            ['code','text',true], ['initial_balance','number',true], ['balance','number',true],
            ['expires_at','datetime-local',false], ['is_active','checkbox',false],
        ],
    ],
    'affiliates' => [
        'model' => 'Affiliate',
        'searchable' => ['name','email','code'],
        'fillable' => ['name','email','phone','code','commission_percent','total_earnings','is_active'],
        'casts' => ['commission_percent'=>'decimal:2','total_earnings'=>'decimal:2','is_active'=>'boolean'],
        'fields' => [
            ['name','text',true], ['email','email',false], ['phone','text',false], ['code','text',true],
            ['commission_percent','number',false], ['is_active','checkbox',false],
        ],
    ],
    'flash-sales' => [
        'model' => 'FlashSale',
        'searchable' => ['title'],
        'fillable' => ['title','starts_at','ends_at','product_ids','discount_percent','is_active'],
        'casts' => ['starts_at'=>'datetime','ends_at'=>'datetime','product_ids'=>'array','discount_percent'=>'decimal:2','is_active'=>'boolean'],
        'fields' => [
            ['title','text',true], ['starts_at','datetime-local',true], ['ends_at','datetime-local',true],
            ['discount_percent','number',false], ['is_active','checkbox',false],
        ],
    ],
    'expenses' => [
        'model' => 'Expense',
        'searchable' => ['title','category','reference'],
        'fillable' => ['category','title','amount','expense_date','payment_method','reference','notes','attachment','created_by'],
        'casts' => ['amount'=>'decimal:2','expense_date'=>'date'],
        'fields' => [
            ['category','text',true], ['title','text',true], ['amount','number',true],
            ['expense_date','date',true], ['payment_method','text',false], ['reference','text',false],
            ['notes','textarea',false], ['attachment','file',false],
        ],
    ],
    'return-reasons' => [
        'model' => 'ReturnReason',
        'searchable' => ['name','type'],
        'fillable' => ['name','type','is_active'],
        'casts' => ['is_active'=>'boolean'],
        'fields' => [['name','text',true], ['type','text',true], ['is_active','checkbox',false]],
    ],
    'shipping-zones' => [
        'model' => 'ShippingZone',
        'searchable' => ['name'],
        'fillable' => ['name','countries','states','cities','pincodes','is_active'],
        'casts' => ['countries'=>'array','states'=>'array','cities'=>'array','pincodes'=>'array','is_active'=>'boolean'],
        'fields' => [['name','text',true], ['is_active','checkbox',false]],
    ],
    'shipping-methods' => [
        'model' => 'ShippingMethod',
        'searchable' => ['name','code','type'],
        'fillable' => ['shipping_zone_id','name','code','type','rate','min_order_amount','free_shipping_threshold','min_weight','max_weight','cod_available','cod_charges','estimated_delivery','is_active'],
        'casts' => ['rate'=>'decimal:2','min_order_amount'=>'decimal:2','free_shipping_threshold'=>'decimal:2','cod_available'=>'boolean','cod_charges'=>'decimal:2','is_active'=>'boolean'],
        'fields' => [
            ['name','text',true], ['code','text',true], ['type','text',true], ['rate','number',false],
            ['cod_available','checkbox',false], ['cod_charges','number',false], ['estimated_delivery','text',false], ['is_active','checkbox',false],
        ],
    ],
    'couriers' => [
        'model' => 'Courier',
        'searchable' => ['name','code','provider'],
        'fillable' => ['name','code','provider','credentials','is_active','settings'],
        'casts' => ['credentials'=>'array','settings'=>'array','is_active'=>'boolean'],
        'fields' => [
            ['name','text',true], ['code','text',true], ['provider','text',false], ['is_active','checkbox',false],
        ],
    ],
    'integrations' => [
        'model' => 'Integration',
        'searchable' => ['name','provider','category'],
        'fillable' => ['name','provider','category','credentials','settings','is_active','is_sandbox'],
        'casts' => ['credentials'=>'array','settings'=>'array','is_active'=>'boolean','is_sandbox'=>'boolean'],
        'fields' => [
            ['name','text',true], ['provider','text',true], ['category','text',true],
            ['is_active','checkbox',false], ['is_sandbox','checkbox',false],
        ],
    ],
    'support-ticket-categories' => [
        'model' => 'SupportTicketCategory',
        'searchable' => ['name'],
        'fillable' => ['name','is_active'],
        'casts' => ['is_active'=>'boolean'],
        'fields' => [['name','text',true], ['is_active','checkbox',false]],
    ],
    'homepage-sections' => [
        'model' => 'HomepageSection',
        'searchable' => ['title','type'],
        'fillable' => ['title','type','settings','sort_order','is_active'],
        'casts' => ['settings'=>'array','is_active'=>'boolean'],
        'fields' => [
            ['title','text',true], ['type','text',true], ['sort_order','number',false], ['is_active','checkbox',false],
        ],
    ],
];

foreach ($modules as $resource => $cfg) {
    $model = $cfg['model'];
    $fillable = var_export($cfg['fillable'], true);
    $castsArr = $cfg['casts'] ?? [];
    $castsCode = '';
    foreach ($castsArr as $k => $v) {
        $castsCode .= "            '{$k}' => '{$v}',\n";
    }
    $soft = !empty($cfg['softDeletes']) ? "use SoftDeletes;\n    " : '';
    $softImport = !empty($cfg['softDeletes']) ? "use Illuminate\\Database\\Eloquent\\SoftDeletes;\n" : '';

    // Model
    writeFile("{$base}/app/Models/{$model}.php", <<<PHP
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
{$softImport}use Illuminate\Database\Eloquent\Factories\HasFactory;

class {$model} extends Model
{
    use HasFactory;
    {$soft}protected \$fillable = {$fillable};

    protected function casts(): array
    {
        return [
{$castsCode}        ];
    }
}

PHP);

    // Repository
    $searchable = var_export($cfg['searchable'], true);
    writeFile("{$base}/app/Repositories/{$model}Repository.php", <<<PHP
<?php

namespace App\Repositories;

use App\Models\\{$model};

class {$model}Repository extends BaseRepository
{
    protected array \$searchable = {$searchable};

    public function __construct({$model} \$model)
    {
        parent::__construct(\$model);
    }
}

PHP);

    // Service
    writeFile("{$base}/app/Services/{$model}Service.php", <<<PHP
<?php

namespace App\Services;

use App\Repositories\\{$model}Repository;

class {$model}Service extends BaseService
{
    public function __construct({$model}Repository \$repository)
    {
        parent::__construct(\$repository);
    }
}

PHP);

    // Form Request
    $rules = [];
    foreach ($cfg['fields'] as [$name, $type, $required]) {
        $r = [];
        if ($required) $r[] = 'required'; else $r[] = 'nullable';
        if ($type === 'email') $r[] = 'email';
        if ($type === 'number') $r[] = 'numeric';
        if ($type === 'file') $r[] = 'file';
        if ($type === 'checkbox') $r = ['nullable','boolean'];
        $rules[] = "            '{$name}' => '" . implode('|', $r) . "',";
    }
    $rulesCode = implode("\n", $rules);
    writeFile("{$base}/app/Http/Requests/Admin/{$model}Request.php", <<<PHP
<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class {$model}Request extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
{$rulesCode}
        ];
    }

    protected function prepareForValidation(): void
    {
        \$booleans = [];
        foreach (\$this->rules() as \$key => \$rule) {
            if (is_string(\$rule) && str_contains(\$rule, 'boolean')) {
                \$booleans[\$key] = \$this->boolean(\$key);
            }
        }
        if (\$booleans) {
            \$this->merge(\$booleans);
        }
    }
}

PHP);

    // Controller
    $routeName = str_replace('-', '.', $resource);
    // keep resource as hyphen for route names: admin.brands
    $routePrefix = 'admin.' . $resource;
    $viewPath = 'admin.' . $resource;
    $var = lcfirst($model);
    $pluralVar = str_replace('-', '_', $resource);

    writeFile("{$base}/app/Http/Controllers/Admin/{$model}Controller.php", <<<PHP
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\\{$model}Request;
use App\Models\\{$model};
use App\Services\\{$model}Service;
use Illuminate\Http\Request;

class {$model}Controller extends AdminController
{
    public function __construct(protected {$model}Service \$service) {}

    public function index(Request \$request)
    {
        \$items = \$this->service->paginate(\$request->all());
        return view('{$viewPath}.index', compact('items'));
    }

    public function create()
    {
        return view('{$viewPath}.create');
    }

    public function store({$model}Request \$request)
    {
        \$data = \$request->validated();
        foreach (['logo','image','banner','avatar','attachment','og_image','mobile_image'] as \$fileField) {
            if (\$request->hasFile(\$fileField)) {
                \$data[\$fileField] = \$request->file(\$fileField)->store('uploads/{$resource}', 'public');
            }
        }
        \$this->service->create(\$data);
        return \$this->success('{$model} created successfully.', '{$routePrefix}.index');
    }

    public function show({$model} \${$var})
    {
        return view('{$viewPath}.show', ['item' => \${$var}]);
    }

    public function edit({$model} \${$var})
    {
        return view('{$viewPath}.edit', ['item' => \${$var}]);
    }

    public function update({$model}Request \$request, {$model} \${$var})
    {
        \$data = \$request->validated();
        foreach (['logo','image','banner','avatar','attachment','og_image','mobile_image'] as \$fileField) {
            if (\$request->hasFile(\$fileField)) {
                \$data[\$fileField] = \$request->file(\$fileField)->store('uploads/{$resource}', 'public');
            }
        }
        \$this->service->update(\${$var}, \$data);
        return \$this->success('{$model} updated successfully.', '{$routePrefix}.index');
    }

    public function destroy({$model} \${$var})
    {
        \$this->service->delete(\${$var});
        return \$this->success('{$model} deleted successfully.');
    }

    public function bulk(Request \$request)
    {
        \$ids = \$request->input('ids', []);
        \$action = \$request->input('action');
        if (!\$ids) {
            return \$this->error('Please select at least one item.');
        }
        if (\$action === 'delete') {
            \$this->service->bulkDelete(\$ids);
            return \$this->success('Selected items deleted.');
        }
        if (\$action === 'activate') {
            \$this->service->bulkUpdate(\$ids, ['is_active' => true]);
            return \$this->success('Selected items activated.');
        }
        if (\$action === 'deactivate') {
            \$this->service->bulkUpdate(\$ids, ['is_active' => false]);
            return \$this->success('Selected items deactivated.');
        }
        return \$this->error('Invalid bulk action.');
    }
}

PHP);

    // Views - index
    $columns = array_slice(array_column($cfg['fields'], 0), 0, 5);
    $th = '';
    $td = '';
    foreach ($columns as $col) {
        $label = ucwords(str_replace('_', ' ', $col));
        $th .= "                    <th>{$label}</th>\n";
        if ($col === 'is_active') {
            $td .= "                    <td>@include('admin.components.status-badge', ['status' => \$item->is_active ? 'active' : 'inactive'])</td>\n";
        } else {
            $td .= "                    <td>{{ \$item->{$col} }}</td>\n";
        }
    }

    $formFields = '';
    foreach ($cfg['fields'] as [$name, $type, $required]) {
        $label = ucwords(str_replace('_', ' ', $name));
        $req = $required ? 'required' : '';
        if ($type === 'textarea') {
            $formFields .= <<<BLADE

            <div class="form-group">
                <label>{$label}</label>
                <textarea name="{$name}" class="form-control" rows="4" {$req}>{{ old('{$name}', \$item->{$name} ?? '') }}</textarea>
            </div>
BLADE;
        } elseif ($type === 'checkbox') {
            $formFields .= <<<BLADE

            <div class="form-group form-check">
                <label><input type="checkbox" name="{$name}" value="1" {{ old('{$name}', \$item->{$name} ?? true) ? 'checked' : '' }}> {$label}</label>
            </div>
BLADE;
        } elseif ($type === 'file') {
            $formFields .= <<<BLADE

            <div class="form-group">
                <label>{$label}</label>
                <input type="file" name="{$name}" class="form-control" accept="image/*,.pdf">
                @if(!empty(\$item?->{$name}))
                    <div class="mt-2"><img src="{{ asset('storage/' . \$item->{$name}) }}" alt="" class="thumb-sm"></div>
                @endif
            </div>
BLADE;
        } else {
            $inputType = in_array($type, ['email','number','date','datetime-local']) ? $type : 'text';
            $formFields .= <<<BLADE

            <div class="form-group">
                <label>{$label}</label>
                <input type="{$inputType}" name="{$name}" class="form-control" value="{{ old('{$name}', \$item->{$name} ?? '') }}" {$req}>
            </div>
BLADE;
        }
    }

    writeFile("{$base}/resources/views/{$viewPath}/index.blade.php", <<<BLADE
@extends('admin.layouts.app')

@section('title', '{$model}s')

@section('content')
<div class="page-header">
    <div>
        <h1>{$model}s</h1>
        <p class="subtitle">Manage {$resource}</p>
    </div>
    <div class="page-actions">
        <a href="{{ route('{$routePrefix}.create') }}" class="btn btn-primary">Add {$model}</a>
    </div>
</div>

@include('admin.components.alerts')

<div class="card">
    <form method="GET" class="filters-bar">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search..." class="form-control">
        <select name="status" class="form-control">
            <option value="">All status</option>
            <option value="1" @selected(request('status')==='1')>Active</option>
            <option value="0" @selected(request('status')==='0')>Inactive</option>
        </select>
        <select name="sort" class="form-control">
            <option value="created_at">Newest</option>
            <option value="name">Name</option>
        </select>
        <button class="btn btn-secondary">Filter</button>
    </form>

    <form method="POST" action="{{ route('{$routePrefix}.bulk') }}" id="bulk-form">
        @csrf
        <div class="bulk-bar">
            <select name="action" class="form-control" required>
                <option value="">Bulk action</option>
                <option value="activate">Activate</option>
                <option value="deactivate">Deactivate</option>
                <option value="delete">Delete</option>
            </select>
            <button class="btn btn-secondary" onclick="return confirm('Apply bulk action?')">Apply</button>
        </div>

        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th><input type="checkbox" data-check-all></th>
{$th}                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                @forelse(\$items as \$item)
                    <tr>
                        <td><input type="checkbox" name="ids[]" value="{{ \$item->id }}"></td>
{$td}                        <td class="actions">
                            <a href="{{ route('{$routePrefix}.edit', \$item) }}" class="btn btn-sm">Edit</a>
                            <a href="{{ route('{$routePrefix}.show', \$item) }}" class="btn btn-sm btn-ghost">View</a>
                            <button form="delete-{{ \$item->id }}" class="btn btn-sm btn-danger" onclick="return confirm('Delete this item?')">Delete</button>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="20">@include('admin.components.empty-state', ['title' => 'No {$resource} found', 'text' => 'Create your first {$model} to get started.'])</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </form>

    @foreach(\$items as \$item)
    <form id="delete-{{ \$item->id }}" method="POST" action="{{ route('{$routePrefix}.destroy', \$item) }}" class="d-none">@csrf @method('DELETE')</form>
    @endforeach

    <div class="pagination-wrap">{{ \$items->links('admin.components.pagination') }}</div>
</div>
@endsection
BLADE);

    writeFile("{$base}/resources/views/{$viewPath}/create.blade.php", <<<BLADE
@extends('admin.layouts.app')
@section('title', 'Add {$model}')
@section('content')
<div class="page-header">
    <div>
        <h1>Add {$model}</h1>
        @include('admin.components.breadcrumbs', ['items' => [['label'=>'{$model}s','url'=>route('{$routePrefix}.index')], ['label'=>'Create']]])
    </div>
</div>
@include('admin.components.alerts')
<div class="card">
    <form method="POST" action="{{ route('{$routePrefix}.store') }}" enctype="multipart/form-data" class="form-grid">
        @csrf
{$formFields}
        <div class="form-actions">
            <button class="btn btn-primary">Save</button>
            <a href="{{ route('{$routePrefix}.index') }}" class="btn btn-ghost">Cancel</a>
        </div>
    </form>
</div>
@endsection
BLADE);

    $editFormFields = str_replace("\$item->{$name} ?? ''", "\$item->{$name} ?? ''", $formFields);
    writeFile("{$base}/resources/views/{$viewPath}/edit.blade.php", <<<BLADE
@extends('admin.layouts.app')
@section('title', 'Edit {$model}')
@section('content')
<div class="page-header">
    <div>
        <h1>Edit {$model}</h1>
        @include('admin.components.breadcrumbs', ['items' => [['label'=>'{$model}s','url'=>route('{$routePrefix}.index')], ['label'=>'Edit']]])
    </div>
</div>
@include('admin.components.alerts')
<div class="card">
    <form method="POST" action="{{ route('{$routePrefix}.update', \$item) }}" enctype="multipart/form-data" class="form-grid">
        @csrf
        @method('PUT')
{$formFields}
        <div class="form-actions">
            <button class="btn btn-primary">Update</button>
            <a href="{{ route('{$routePrefix}.index') }}" class="btn btn-ghost">Cancel</a>
        </div>
    </form>
</div>
@endsection
BLADE);

    writeFile("{$base}/resources/views/{$viewPath}/show.blade.php", <<<BLADE
@extends('admin.layouts.app')
@section('title', '{$model} Details')
@section('content')
<div class="page-header">
    <div>
        <h1>{$model} Details</h1>
    </div>
    <div class="page-actions">
        <a href="{{ route('{$routePrefix}.edit', \$item) }}" class="btn btn-primary">Edit</a>
        <a href="{{ route('{$routePrefix}.index') }}" class="btn btn-ghost">Back</a>
    </div>
</div>
<div class="card">
    <div class="detail-grid">
        @foreach(\$item->getAttributes() as \$key => \$value)
            @if(!in_array(\$key, ['updated_at']))
            <div class="detail-item">
                <span class="label">{{ ucwords(str_replace('_',' ', \$key)) }}</span>
                <span class="value">{{ is_array(\$value) || is_object(\$value) ? json_encode(\$value) : \$value }}</span>
            </div>
            @endif
        @endforeach
    </div>
</div>
@endsection
BLADE);
}

echo "Scaffold complete for " . count($modules) . " modules.\n";
