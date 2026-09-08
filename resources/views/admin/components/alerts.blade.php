@once('admin-alerts')
@if (session('success'))
    <div class="alert alert-success" role="alert">
        <div>{{ session('success') }}</div>
        <button type="button" class="alert-close" aria-label="Dismiss">&times;</button>
    </div>
@endif

@if (session('error'))
    <div class="alert alert-error" role="alert">
        <div>{{ session('error') }}</div>
        <button type="button" class="alert-close" aria-label="Dismiss">&times;</button>
    </div>
@endif

@if (session('warning'))
    <div class="alert alert-warning" role="alert">
        <div>{{ session('warning') }}</div>
        <button type="button" class="alert-close" aria-label="Dismiss">&times;</button>
    </div>
@endif

@if (session('info') || session('status'))
    <div class="alert alert-info" role="alert">
        <div>{{ session('info') ?? session('status') }}</div>
        <button type="button" class="alert-close" aria-label="Dismiss">&times;</button>
    </div>
@endif

@if (isset($errors) && $errors->any())
    <div class="alert alert-error" role="alert">
        <div>
            <strong>Please fix the following:</strong>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        <button type="button" class="alert-close" aria-label="Dismiss">&times;</button>
    </div>
@endif
@endonce
