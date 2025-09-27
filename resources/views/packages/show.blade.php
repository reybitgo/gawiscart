@extends('layouts.admin')

@section('title', $package->name)

@section('content')
<div class="container-fluid">
    <div class="row mb-3">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="{{ route('dashboard') }}">Home</a>
                    </li>
                    <li class="breadcrumb-item">
                        <a href="{{ route('packages.index') }}">Packages</a>
                    </li>
                    <li class="breadcrumb-item active">{{ $package->name }}</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <img src="{{ $package->image_url }}" alt="{{ $package->name }}"
                                 class="img-fluid rounded shadow-sm mb-3" style="max-height: 400px; width: 100%; object-fit: cover;">
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <h1 class="h3">{{ $package->name }}</h1>
                                @if(isset($package->meta_data['badge']))
                                    <span class="badge bg-info fs-6 mb-2">{{ $package->meta_data['badge'] }}</span>
                                @endif
                                @if($package->quantity_available !== null && $package->quantity_available <= 10)
                                    <span class="badge bg-warning fs-6 mb-2">Limited Stock</span>
                                @endif
                            </div>

                            <p class="lead text-muted">{{ $package->short_description }}</p>

                            <div class="row mb-4">
                                <div class="col-6">
                                    <div class="text-center p-3 bg-primary bg-opacity-10 rounded">
                                        <div class="h2 text-primary mb-0">{{ $package->formatted_price }}</div>
                                        <small class="text-muted">Price</small>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="text-center p-3 bg-success bg-opacity-10 rounded">
                                        <div class="h3 text-success mb-0">{{ number_format($package->points_awarded) }}</div>
                                        <small class="text-muted">Points Awarded</small>
                                    </div>
                                </div>
                            </div>

                            @if($package->quantity_available !== null)
                                <div class="mb-3">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="text-muted">Availability:</span>
                                        @if($package->quantity_available > 0)
                                            <span class="badge bg-success">{{ $package->quantity_available }} in stock</span>
                                        @else
                                            <span class="badge bg-danger">Out of stock</span>
                                        @endif
                                    </div>
                                </div>
                            @endif

                            @if(isset($package->meta_data['duration']))
                                <div class="mb-3">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="text-muted">Duration:</span>
                                        <span class="fw-semibold">{{ $package->meta_data['duration'] }}</span>
                                    </div>
                                </div>
                            @endif

                            <div class="d-grid gap-2 mt-4">
                                @if($package->isAvailable())
                                    <button class="btn btn-primary btn-lg add-to-cart-btn" data-package-id="{{ $package->id }}">
                                        <svg class="icon me-2">
                                            <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-cart') }}"></use>
                                        </svg>
                                        Add to Cart
                                    </button>
                                @else
                                    <button class="btn btn-secondary btn-lg" disabled>
                                        Unavailable
                                    </button>
                                @endif
                                <a href="{{ route('packages.index') }}" class="btn btn-outline-secondary">
                                    <svg class="icon me-2">
                                        <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-arrow-left') }}"></use>
                                    </svg>
                                    Back to Packages
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Package Details</h5>
                </div>
                <div class="card-body">
                    <div class="prose">
                        {!! nl2br(e($package->long_description)) !!}
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            @if(isset($package->meta_data['features']))
                <div class="card">
                    <div class="card-header">
                        <h6 class="card-title mb-0">What's Included</h6>
                    </div>
                    <div class="card-body">
                        <ul class="list-unstyled mb-0">
                            @foreach($package->meta_data['features'] as $feature)
                                <li class="mb-2">
                                    <svg class="icon text-success me-2">
                                        <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-check') }}"></use>
                                    </svg>
                                    {{ $feature }}
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif

            <div class="card {{ isset($package->meta_data['features']) ? 'mt-3' : '' }}">
                <div class="card-header">
                    <h6 class="card-title mb-0">Package Information</h6>
                </div>
                <div class="card-body">
                    <div class="row mb-2">
                        <div class="col-6 text-muted">Price:</div>
                        <div class="col-6 fw-semibold">{{ $package->formatted_price }}</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-6 text-muted">Points:</div>
                        <div class="col-6 fw-semibold">{{ number_format($package->points_awarded) }}</div>
                    </div>
                    @if(isset($package->meta_data['duration']))
                        <div class="row mb-2">
                            <div class="col-6 text-muted">Duration:</div>
                            <div class="col-6 fw-semibold">{{ $package->meta_data['duration'] }}</div>
                        </div>
                    @endif
                    @if(isset($package->meta_data['category']))
                        <div class="row mb-2">
                            <div class="col-6 text-muted">Category:</div>
                            <div class="col-6">
                                <span class="badge bg-secondary">{{ ucfirst($package->meta_data['category']) }}</span>
                            </div>
                        </div>
                    @endif
                    @if($package->quantity_available !== null)
                        <div class="row">
                            <div class="col-6 text-muted">Available:</div>
                            <div class="col-6 fw-semibold">
                                @if($package->quantity_available > 0)
                                    {{ $package->quantity_available }} units
                                @else
                                    Out of stock
                                @endif
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-header">
                    <h6 class="card-title mb-0">Need Help?</h6>
                </div>
                <div class="card-body">
                    <p class="small text-muted mb-3">
                        Have questions about this package? Our support team is here to help.
                    </p>
                    <div class="d-grid">
                        <a href="{{ route('profile.show') }}" class="btn btn-outline-primary btn-sm">
                            <svg class="icon me-2">
                                <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-envelope-closed') }}"></use>
                            </svg>
                            Contact Support
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.add-to-cart-btn {
    transition: all 0.2s ease-in-out;
}

.add-to-cart-btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

.prose {
    line-height: 1.6;
}

.prose p {
    margin-bottom: 1rem;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Add to cart functionality (placeholder for Phase 2)
    const addToCartBtn = document.querySelector('.add-to-cart-btn');

    if (addToCartBtn) {
        addToCartBtn.addEventListener('click', function() {
            const packageId = this.dataset.packageId;

            // Placeholder for cart functionality
            this.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Adding to Cart...';
            this.disabled = true;

            setTimeout(() => {
                this.innerHTML = `
                    <svg class="icon me-2">
                        <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-check') }}"></use>
                    </svg>
                    Added to Cart!
                `;
                this.classList.remove('btn-primary');
                this.classList.add('btn-success');

                // Show success message
                const successAlert = document.createElement('div');
                successAlert.className = 'alert alert-success alert-dismissible fade show mt-3';
                successAlert.innerHTML = `
                    <svg class="icon me-2">
                        <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-check') }}"></use>
                    </svg>
                    Package added to cart successfully!
                    <button type="button" class="btn-close" data-coreui-dismiss="alert"></button>
                `;
                this.parentNode.appendChild(successAlert);

                // Reset after 3 seconds
                setTimeout(() => {
                    this.innerHTML = `
                        <svg class="icon me-2">
                            <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-cart') }}"></use>
                        </svg>
                        Add to Cart
                    `;
                    this.classList.remove('btn-success');
                    this.classList.add('btn-primary');
                    this.disabled = false;

                    // Remove success message
                    if (successAlert && successAlert.parentNode) {
                        successAlert.remove();
                    }
                }, 3000);
            }, 1000);
        });
    }
});
</script>
@endsection