<nav class="navbar navbar-expand-lg sticky-top mb-4 mb-md-5">
    <div class="container position-relative">
        <div class="d-flex align-items-center">
            <a class="navbar-brand p-0 me-4" href="{{ route('dashboard') }}">
                <img src="{{ asset('images/logo.png') }}" alt="Logo" width="130" height="50">
            </a>
            
            @notclient
            <div class="d-none d-md-block">
                <small class="text-muted d-block text-uppercase" style="font-size: 0.65rem; letter-spacing: 1px;">ჩემი ბალანსი</small>
                <div class="d-flex align-items-center gap-2">
                    <a href="{{ route('wallet.index') }}" class="text-decoration-none">
                        <span class="fw-bold text-success fs-5" id="balanceAmount" data-amount="{{ auth()->user()->formatted_balance }}">
                            <i class="bi bi-wallet2 me-2"></i>{{ auth()->user()->formatted_balance }}
                        </span>
                    </a>
                    <i class="bi bi-eye text-muted cursor-pointer balance-toggle" id="balanceToggleIcon" onclick="toggleBalance()" style="cursor: pointer; font-size: 1.1rem; transition: color 0.3s;"></i>
                </div>
            </div>
            @endnotclient
        </div>

        <div class="position-absolute start-50 translate-middle-x d-none d-md-block text-center">
            <div class="text-white fw-bold fs-5" style="letter-spacing: 0.5px;">
                {{ auth()->user()->display_name }}
            </div>
        </div>

        <div class="d-flex align-items-center gap-2 ms-auto">
            @admin
                <a href="{{ route('cars.create') }}" class="btn-nav btn-nav-add" title="მანქანის დამატება">
                    <i class="bi bi-plus-lg fs-4"></i>
                </a>
            @endadmin

            <a href="{{ route('dashboard') }}" class="btn-nav btn-nav-light" title="მთავარი">
                <i class="bi bi-grid-fill fs-5"></i>
            </a>
            
            @notclient
                <a href="{{ route('finance.index') }}" class="btn-nav btn-nav-success" title="ფინანსები">
                    <i class="bi bi-currency-dollar fs-5"></i>
                </a>
            @endnotclient
            
            @admin
                <a href="{{ route('users.index') }}" class="btn-nav btn-nav-light" title="დილერები / კლიენტები">
                    <i class="bi bi-people-fill fs-5"></i>
                </a>
            @endadmin

            <div class="vr bg-secondary opacity-25 mx-2" style="height: 25px;"></div>
            
            <a href="{{ route('password.change') }}" class="btn-nav btn-nav-light" title="პაროლი">
                <i class="bi bi-key-fill fs-5"></i>
            </a>

            @notclient
            @php
                $unreadCount = auth()->user()->getUnreadNotificationsCount();
            @endphp
            <a href="#" class="btn-nav btn-nav-light position-relative" title="შეტყობინებები">
                <i class="bi bi-bell-fill fs-5"></i>
                @if($unreadCount > 0)
                    <span class="notification-badge">{{ $unreadCount }}</span>
                @endif
            </a>
            @endnotclient

            <form method="POST" action="{{ route('logout') }}" class="d-inline">
                @csrf
                <button type="submit" class="btn-nav btn-nav-danger" title="გასვლა">
                    <i class="bi bi-box-arrow-right fs-5"></i>
                </button>
            </form>
        </div>
    </div>
</nav>

<script>
    function toggleBalance() {
        const amountEl = document.getElementById('balanceAmount');
        const iconEl = document.getElementById('balanceToggleIcon');
        const realAmount = amountEl.getAttribute('data-amount');
        const isHidden = localStorage.getItem('hideBalance') === 'true';

        if (isHidden) {
            amountEl.innerHTML = `<i class="bi bi-wallet2 me-2"></i>${realAmount}`;
            iconEl.classList.remove('bi-eye-slash'); 
            iconEl.classList.add('bi-eye');
            localStorage.setItem('hideBalance', 'false');
        } else {
            amountEl.innerHTML = `<i class="bi bi-wallet2 me-2"></i>*****`;
            iconEl.classList.remove('bi-eye'); 
            iconEl.classList.add('bi-eye-slash');
            localStorage.setItem('hideBalance', 'true');
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        const isHidden = localStorage.getItem('hideBalance') === 'true';
        if (isHidden) {
            const amountEl = document.getElementById('balanceAmount');
            if(amountEl) {
                const iconEl = document.getElementById('balanceToggleIcon');
                amountEl.innerHTML = `<i class="bi bi-wallet2 me-2"></i>*****`;
                iconEl.classList.remove('bi-eye'); 
                iconEl.classList.add('bi-eye-slash');
            }
        }
    });
</script>
