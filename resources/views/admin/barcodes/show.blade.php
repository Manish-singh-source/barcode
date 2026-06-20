@extends('layouts.admin')

@section('content')
<div class="card border-0 shadow-sm rounded-4">
    <div class="card-body p-4 p-xl-5">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
            <div>
                <h1 class="h4 fw-bold mb-1">Barcode Details</h1>
                <p class="text-secondary mb-0">Detailed information for the selected barcode.</p>
            </div>
            <a href="{{ url('/barcodes') }}" class="btn btn-outline-secondary">Back to List</a>
        </div>

        <div class="row g-4">
            <div class="col-lg-6">
                <div class="p-4 bg-light rounded-4 h-100">
                    <dl class="row mb-0">
                        <dt class="col-4 text-secondary">Unique Code</dt>
                        <dd class="col-8 fw-semibold">{{ $barcode->unique_code }}</dd>
                        <dt class="col-4 text-secondary">Format</dt>
                        <dd class="col-8">{{ $barcode->barcode_format?->value ?? $barcode->barcode_format }}</dd>
                        <dt class="col-4 text-secondary">Label</dt>
                        <dd class="col-8">{{ $barcode->custom_label ?: '—' }}</dd>
                        <dt class="col-4 text-secondary">Product</dt>
                        <dd class="col-8">{{ $barcode->product?->name ?: '—' }}</dd>
                        <dt class="col-4 text-secondary">Created</dt>
                        <dd class="col-8">{{ $barcode->created_at?->toDayDateTimeString() ?: '—' }}</dd>
                    </dl>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="p-4 border rounded-4 h-100 text-center">
                    <div class="fw-semibold mb-3">Barcode Content</div>
                    <div class="text-break">{{ $barcode->barcode_data }}</div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection