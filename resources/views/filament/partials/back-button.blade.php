@php
    $path = trim(request()->path(), '/');
    $isEdit = str_ends_with($path, 'edit');
    $isCreate = str_ends_with($path, 'create');
@endphp

@if ($isEdit || $isCreate)
    @php
        $previous = url()->previous();
        $fallback = $isCreate
            ? url(preg_replace('#/create$#', '', request()->url()))
            : url(preg_replace('#/[^/]+/edit$#', '', request()->url()));

        $backUrl = ($previous && $previous !== url()->current()) ? $previous : $fallback;
    @endphp

    <a href="{{ $backUrl }}"
       style="display:inline-flex;align-items:center;justify-content:center;gap:6px;padding:8px 14px;border-radius:8px;background:#f3f4f6;color:#374151;border:1px solid #e5e7eb;font-size:13px;font-weight:600;text-decoration:none;">
        رجوع
    </a>
@endif
