@extends('layouts.app')

@section('title', 'საფულე - OneCar CRM')

@section('content')
<div class="container pb-5">
    
    {{-- Messages --}}
    @if(session('success'))
        <div class="alert alert-success mb-4">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger mb-4">{{ session('error') }}</div>
    @endif

    <div class="row g-4">
        {{-- Balance & Transfer Form --}}
        <div class="col-lg-5">
            {{-- Balance Card --}}
            <div class="glass-card p-4 mb-4 text-center position-relative overflow-hidden">
                <div class="position-absolute top-0 start-0 w-100 h-100 bg-gradient-primary opacity-10"></div>
                <h6 class="text-muted text-uppercase small mb-2">ჩემი ბალანსი</h6>
                <h1 class="display-4 fw-bold text-success mb-0">@money($balance)</h1>
                <p class="text-muted small mt-2"><i class="bi bi-wallet2 me-1"></i> ხელმისაწვდომი თანხა</p>
            </div>

            {{-- Transfer Form --}}
            <div class="glass-card p-4">
                <h5 class="text-white mb-4">
                    <i class="bi bi-arrow-right-circle text-primary me-2"></i>თანხის განაწილება
                </h5>
                
                @if($carsWithDebt->count() > 0)
                    <form method="POST" action="{{ route('wallet.transfer') }}">
                        @csrf
                        
                        <div class="mb-3">
                            <label class="text-muted small mb-1">აირჩიე მანქანა</label>
                            <select name="car_id" class="form-select" required>
                                <option value="">-- სია --</option>
                                @foreach($carsWithDebt as $car)
                                    <option value="{{ $car->id }}">
                                        {{ $car->make_model }} (Debt: @moneyShort($car->remaining_debt))
                                    </option>
                                @endforeach
                            </select>
                            @error('car_id')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="mb-4">
                            <label class="text-muted small mb-1">თანხა ($)</label>
                            <input type="number" 
                                   step="0.01" 
                                   name="amount" 
                                   class="form-control @error('amount') is-invalid @enderror" 
                                   placeholder="0.00" 
                                   max="{{ $balance }}" 
                                   required>
                            <div class="form-text text-white opacity-50 small">
                                მაქსიმუმ: @money($balance)
                            </div>
                            @error('amount')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <button type="submit" class="btn btn-success w-100 py-2">
                            <i class="bi bi-check-lg me-2"></i>გადატანა
                        </button>
                    </form>
                @else
                    <div class="text-center py-4 text-muted">
                        <i class="bi bi-check-circle-fill fs-1 text-success mb-2 d-block"></i>
                        ყველა მანქანის დავალიანება დაფარულია!
                    </div>
                @endif
            </div>
        </div>

        {{-- Transaction History --}}
        <div class="col-lg-7">
            <div class="glass-card p-4 h-100">
                <h5 class="text-white mb-4">
                    <i class="bi bi-clock-history text-warning me-2"></i>ბოლო ტრანზაქციები
                </h5>
                
                @if($history->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-borderless align-middle text-white mb-0">
                            <thead class="border-bottom border-secondary border-opacity-25 text-muted small text-uppercase">
                                <tr>
                                    <th>დანიშნულება</th>
                                    <th>თარიღი</th>
                                    <th class="text-end">თანხა</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($history as $transaction)
                                    @php
                                        $isIncome = $transaction->isIncome();
                                        $color = $isIncome ? 'text-success' : 'text-danger';
                                        $sign = $isIncome ? '+' : '-';
                                    @endphp
                                    <tr style="border-bottom: 1px solid rgba(255,255,255,0.05);">
                                        <td class="py-3">
                                            @if($transaction->car)
                                                <div class="fw-bold">{{ $transaction->car->make_model }}</div>
                                                <small class="text-muted" style="font-size: 0.75rem;">
                                                    VIN: {{ $transaction->car->vin }}
                                                </small>
                                            @else
                                                <span class="text-warning">ბალანსის შევსება</span>
                                            @endif
                                            
                                            @if($transaction->comment)
                                                <div class="text-white opacity-50 small fst-italic mt-1">
                                                    {{ $transaction->comment }}
                                                </div>
                                            @endif
                                        </td>
                                        <td class="text-muted small">
                                            {{ $transaction->payment_date->format('d M, H:i') }}
                                        </td>
                                        <td class="text-end fw-bold {{ $color }}">
                                            {{ $sign }}@money($transaction->amount)
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-5 text-muted">
                        ისტორია ცარიელია
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
