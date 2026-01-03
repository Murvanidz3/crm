<?php

namespace App\Http\Controllers;

use App\Services\TransactionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class WalletController extends Controller
{
    public function __construct(
        private TransactionService $transactionService
    ) {}

    /**
     * Display wallet page with balance and transfer form
     */
    public function index(): View
    {
        $user = Auth::user();

        // Get user's current balance
        $balance = $user->balance;

        // Get cars with debt
        $carsWithDebt = $this->transactionService->getCarsWithDebt($user);

        // Get transaction history
        $history = $this->transactionService->getUserTransactions($user, 10);

        return view('wallet.index', compact('balance', 'carsWithDebt', 'history'));
    }

    /**
     * Transfer money from balance to car payment
     * 
     * Uses atomic database operations to prevent race conditions
     */
    public function transfer(Request $request): RedirectResponse
    {
        $request->validate([
            'car_id' => 'required|exists:cars,id',
            'amount' => 'required|numeric|min:0.01|max:9999999.99',
        ], [
            'car_id.required' => 'მანქანა აუცილებელია',
            'car_id.exists' => 'მითითებული მანქანა არ არსებობს',
            'amount.required' => 'თანხა აუცილებელია',
            'amount.min' => 'თანხა უნდა იყოს 0-ზე მეტი',
        ]);

        $user = Auth::user();
        $carId = $request->input('car_id');
        $amount = (float) $request->input('amount');

        // Verify user owns the car
        $car = $user->cars()->findOrFail($carId);

        try {
            $this->transactionService->transferToCarPayment(
                $user,
                $car,
                $amount,
                'გადახდა ბალანსიდან'
            );

            return back()->with('success', 'გადახდა შესრულებულია წარმატებით! ✅');

        } catch (\Exception $e) {
            return back()->with('error', 'შეცდომა: ' . $e->getMessage());
        }
    }
}
