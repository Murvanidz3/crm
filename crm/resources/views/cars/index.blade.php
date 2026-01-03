@extends('layouts.app')

@section('title', 'მთავარი - OneCar CRM')

@section('content')
<div class="container pb-5">
    
    {{-- Success/Error Messages --}}
    @if(session('success'))
        <div class="alert alert-success mb-4">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger mb-4">{{ session('error') }}</div>
    @endif

    {{-- Statistics Cards (Admin & Dealer only) --}}
    @notclient
    <div class="row g-4 mb-5">
        <div class="col-md-3">
            <div class="glass-card p-4 h-100 position-relative overflow-hidden">
                <h6 class="text-muted text-uppercase small mb-2 ls-1">სულ მანქანები</h6>
                <h2 class="fw-bold mb-0 text-white">{{ $stats['total'] }}</h2>
                <i class="bi bi-car-front position-absolute end-0 bottom-0 m-3 fs-1 text-white opacity-10"></i>
            </div>
        </div>
        <div class="col-md-3">
            <div class="glass-card p-4 h-100 position-relative overflow-hidden">
                <h6 class="text-muted text-uppercase small mb-2 ls-1">გზაშია</h6>
                <h2 class="fw-bold mb-0 text-primary">{{ $stats['on_way'] }}</h2>
                <i class="bi bi-tsunami position-absolute end-0 bottom-0 m-3 fs-1 text-primary opacity-25"></i>
            </div>
        </div>
        <div class="col-md-3">
            <div class="glass-card p-4 h-100 position-relative overflow-hidden">
                <h6 class="text-muted text-uppercase small mb-2 ls-1">ჯამური დავალიანება</h6>
                <h2 class="fw-bold mb-0 text-danger">@moneyShort($stats['debt'])</h2>
                <i class="bi bi-wallet2 position-absolute end-0 bottom-0 m-3 fs-1 text-danger opacity-25"></i>
            </div>
        </div>
        
        <div class="col-md-3">
            @admin
                <a href="{{ route('cars.create') }}" class="btn btn-primary w-100 h-100 d-flex flex-column align-items-center justify-content-center glass-card border-0 text-white shadow-lg" style="background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);">
                    <i class="bi bi-plus-circle-fill fs-2 mb-2"></i>
                    <span class="fw-bold">დამატება</span>
                </a>
            @else
                <div class="glass-card p-4 h-100 d-flex flex-column align-items-center justify-content-center opacity-75">
                    <i class="bi bi-shield-lock fs-1 text-muted mb-2"></i>
                    <small class="text-muted text-center" style="font-size: 0.75rem;">დამატებისთვის<br>მიმართეთ ადმინისტრაციას</small>
                </div>
            @endadmin
        </div>
    </div>
    @endnotclient

    {{-- Search & Filter --}}
    <div class="glass-card p-3 mb-4">
        <form method="GET" action="{{ route('dashboard') }}" class="row g-2 align-items-center">
            <div class="col-md-4">
                <div class="input-group">
                    <span class="input-group-text bg-transparent border-secondary border-opacity-25 text-muted">
                        <i class="bi bi-search"></i>
                    </span>
                    <input type="text" 
                           name="search" 
                           class="form-control border-start-0 ps-0" 
                           placeholder="ძებნა: VIN, მოდელი..." 
                           value="{{ request('search') }}">
                </div>
            </div>
            <div class="col-md-3">
                <select name="filter_status" class="form-select border-secondary border-opacity-25" onchange="this.form.submit()">
                    <option value="">- ყველა სტატუსი -</option>
                    @foreach(\App\Enums\CarStatus::toArray() as $status)
                        <option value="{{ $status['value'] }}" {{ request('filter_status') == $status['value'] ? 'selected' : '' }}>
                            {{ $status['label'] }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">ძებნა</button>
            </div>
            @if(request('search') || request('filter_status'))
                <div class="col-md-2">
                    <a href="{{ route('dashboard') }}" class="btn btn-light w-100 text-muted">
                        <i class="bi bi-x-circle"></i> გასუფთავება
                    </a>
                </div>
            @endif
        </form>
    </div>

    {{-- Car List --}}
    <div class="row">
        @forelse($cars as $car)
            <div class="col-12 mb-3">
                <div class="glass-card p-3">
                    <div class="row align-items-center">
                        {{-- Photo --}}
                        <div class="col-md-3 col-lg-2">
                            <div style="height: 120px; overflow: hidden; border-radius: 12px; position: relative;">
                                <a href="{{ route('cars.show', $car) }}" class="d-block h-100 text-decoration-none">
                                    <img src="{{ $car->main_photo_url }}" class="w-100 h-100" style="object-fit: cover;">
                                </a>
                            </div>
                        </div>

                        {{-- Info --}}
                        <div class="col-md-6 col-lg-6">
                            @admin
                                <div class="mb-2">
                                    <span class="badge bg-dark border border-secondary text-info fw-normal" style="font-size: 0.75rem;">
                                        <i class="bi bi-person-circle me-1"></i> {{ $car->owner?->display_name }}
                                    </span>
                                </div>
                            @endadmin

                            <div class="d-flex align-items-center mb-1">
                                <h5 class="fw-bold mb-0 text-white me-2">
                                    {{ $car->make_model }} 
                                    <span class="text-muted fw-normal fs-6">/ {{ $car->year }}</span>
                                </h5>
                                {!! $car->status_badge !!}
                            </div>
                            
                            <div class="d-flex gap-3 text-muted small mb-2 mt-2">
                                <span><i class="bi bi-upc-scan text-primary"></i> {{ $car->vin }}</span>
                                @if($car->container_number)
                                    <span><i class="bi bi-box-seam text-info"></i> {{ $car->container_number }}</span>
                                @endif
                            </div>

                            <div class="d-flex gap-2 mt-3">
                                @if($car->lot_number)
                                    <span class="badge bg-secondary bg-opacity-25 text-light border border-secondary border-opacity-25 fw-normal">
                                        Lot: {{ $car->lot_number }}
                                    </span>
                                @endif
                            </div>
                        </div>

                        {{-- Financials & Actions --}}
                        <div class="col-md-3 col-lg-4 text-end border-start border-secondary border-opacity-25 ps-4">
                            <div class="d-flex gap-2 mb-3">
                                <div class="flex-fill p-1 rounded-3 border border-success border-opacity-25 bg-success bg-opacity-10 text-center">
                                    <small class="d-block text-success opacity-75 text-uppercase" style="font-size: 0.6rem;">გადახდილი</small>
                                    <span class="fw-bold text-success" style="font-size: 1.1rem;">@moneyShort($car->paid_amount)</span>
                                </div>
                                <div class="flex-fill p-1 rounded-3 border border-danger border-opacity-25 bg-danger bg-opacity-10 text-center">
                                    <small class="d-block text-danger opacity-75 text-uppercase" style="font-size: 0.6rem;">დავალიანება</small>
                                    <span class="fw-bold text-danger" style="font-size: 1.1rem;">@moneyShort($car->debt)</span>
                                </div>
                            </div>
                            
                            <div class="d-flex gap-2">
                                <a href="{{ route('cars.show', $car) }}" class="btn btn-sm flex-grow-1 text-white border border-secondary border-opacity-25" style="background: rgba(255,255,255,0.05);">
                                    დეტალები <i class="bi bi-arrow-right-short ms-1"></i>
                                </a>
                                
                                @can('update', $car)
                                    <a href="{{ route('cars.edit', $car) }}" class="btn btn-sm btn-primary">
                                        <i class="bi bi-pencil-fill"></i>
                                    </a>
                                @endcan
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="text-center py-5 glass-card">
                <i class="bi bi-search fs-1 mb-3 d-block text-muted"></i>
                <h5 class="text-muted">მანქანა ვერ მოიძებნა</h5>
                @if(request('search') || request('filter_status'))
                    <a href="{{ route('dashboard') }}" class="btn btn-link text-primary">ფილტრის გასუფთავება</a>
                @endif
            </div>
        @endforelse
    </div>

    {{-- Pagination --}}
    @if($cars->hasPages())
        <div class="d-flex justify-content-center mt-4">
            {{ $cars->links() }}
        </div>
    @endif
</div>
@endsection
