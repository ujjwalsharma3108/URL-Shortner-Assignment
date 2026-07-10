@if (session('status'))
    <div class="feedback success" role="status">{{ session('status') }}</div>
@endif

@if ($errors->any())
    <div class="feedback error" role="alert">{{ $errors->first() }}</div>
@endif
