<!DOCTYPE html>
<html lang="ka">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Login - OneCar CRM</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="{{ asset('css/glassmorphism.css') }}" rel="stylesheet">
</head>
<body class="d-flex align-items-center justify-content-center" style="min-height: 100vh;">

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-4">
            <div class="glass-card p-5">
                <div class="text-center mb-4">
                    <img src="{{ asset('images/logo.png') }}" alt="Logo" width="160" class="mb-4">
                    <h4 class="text-white fw-bold">ავტორიზაცია</h4>
                    <p class="text-muted small">შეიყვანეთ მონაცემები სისტემაში შესასვლელად</p>
                </div>

                @if($errors->any())
                    <div class="alert alert-danger bg-danger bg-opacity-25 border-0 text-white text-center small py-2 mb-4">
                        @foreach($errors->all() as $error)
                            {{ $error }}<br>
                        @endforeach
                    </div>
                @endif

                <form method="POST" action="{{ route('login') }}">
                    @csrf
                    
                    <div class="mb-3">
                        <label class="text-muted small mb-1">მომხმარებელი</label>
                        <input type="text" 
                               name="username" 
                               class="form-control @error('username') is-invalid @enderror" 
                               value="{{ old('username') }}"
                               required 
                               autofocus
                               autocomplete="username"
                               placeholder="User">
                    </div>
                    
                    <div class="mb-4">
                        <label class="text-muted small mb-1">პაროლი</label>
                        <input type="password" 
                               name="password" 
                               class="form-control @error('password') is-invalid @enderror" 
                               required 
                               autocomplete="current-password"
                               placeholder="••••••">
                    </div>
                    
                    <div class="mb-4">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="remember" id="remember">
                            <label class="form-check-label text-muted small" for="remember">
                                დამიმახსოვრე
                            </label>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-primary w-100 py-2">
                        <i class="bi bi-box-arrow-in-right me-2"></i>შესვლა
                    </button>
                </form>
            </div>
            
            <p class="text-center text-muted small mt-4">
                &copy; {{ date('Y') }} OneCar CRM. All rights reserved.
            </p>
        </div>
    </div>
</div>

</body>
</html>
