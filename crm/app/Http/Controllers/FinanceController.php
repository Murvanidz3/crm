<?php

namespace App\Http\Controllers;

use App\Enums\TransactionPurpose;
use App\Models\Car;
use App\Models\Transaction;
use App\Models\User;
use App\Services\TransactionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class FinanceController extends Controller
{
    public function __construct(
        private TransactionService $transactionService
    ) {}

    /**
     * Display financial overview
     */
    public function index(): View
    {
        $user = Auth::user();
        
        // Build query based on role
        $query = Car::with(['owner'])
            ->select('cars.*');

        if ($user->isDealer()) {
            $query->where('user_id', $user->id);
        } elseif (!$user->isAdmin()) {
            // Clients shouldn't access this page
            abort(403);
        }

        $cars = $query->orderBy('user_id', 'asc')
            ->orderBy('id', 'desc')
            ->get();

        // Group by owner
        $financeData = $cars->groupBy('user_id')->map(function ($ownerCars) {
            $owner = $ownerCars->first()->owner;
            
            $stats = [
                'cost' => $ownerCars->sum('total_cost'),
                'paid' => $ownerCars->sum('paid_amount'),
                'debt' => $ownerCars->sum('debt'),
            ];

            return [
                'owner' => $owner,
                'cars' => $ownerCars,
                'stats' => $stats,
            ];
        });

        // Global stats (admin only)
        $globalStats = [
            'total_cost' => $cars->sum('total_cost'),
            'total_paid' => $cars->sum('paid_amount'),
            'total_debt' => $cars->sum('debt'),
        ];

        return view('finance.index', compact('financeData', 'globalStats'));
    }

    /**
     * Display transactions list
     */
    public function transactions(Request $request): View
    {
        $user = Auth::user();

        $query = Transaction::with(['car', 'user'])
            ->forUser($user)
            ->orderBy('payment_date', 'desc')
            ->orderBy('id', 'desc');

        $transactions = $query->paginate(25);

        // Get lists for admin add transaction form
        $carsList = [];
        $usersList = [];

        if ($user->isAdmin()) {
            $carsList = Car::orderBy('id', 'desc')->get(['id', 'make_model', 'vin']);
            $usersList = User::dealers()->get(['id', 'full_name', 'username']);
        }

        return view('finance.transactions', compact('transactions', 'carsList', 'usersList'));
    }

    /**
     * Store new transaction (Admin only)
     */
    public function storeTransaction(Request $request): RedirectResponse
    {
        $this->authorize('create', Transaction::class);

        $request->validate([
            'transaction_type' => 'required|in:balance,vehicle,shipping',
            'amount' => 'required|numeric|min:0.01',
            'payment_date' => 'required|date',
            'comment' => 'nullable|string|max:500',
            'target_user_id' => 'required_if:transaction_type,balance|exists:users,id',
            'target_car_id' => 'required_if:transaction_type,vehicle,shipping|exists:cars,id',
        ]);

        $type = $request->input('transaction_type');
        $amount = (float) $request->input('amount');
        $comment = $request->input('comment');

        try {
            if ($type === 'balance') {
                $targetUser = User::findOrFail($request->input('target_user_id'));
                $this->transactionService->addBalanceTopup($targetUser, $amount, $comment);
                $message = 'ბალანსი შევსებულია';
            } else {
                $car = Car::findOrFail($request->input('target_car_id'));
                $purpose = $type === 'vehicle' 
                    ? TransactionPurpose::VEHICLE_PAYMENT 
                    : TransactionPurpose::SHIPPING;
                    
                $this->transactionService->addCarPayment($car, $amount, $purpose, $comment);
                $message = $type === 'vehicle' ? 'მანქანის საფასური დაემატა' : 'ტრანსპორტირების თანხა დაემატა';
            }

            return redirect()
                ->route('finance.transactions')
                ->with('success', $message);

        } catch (\Exception $e) {
            return back()->with('error', 'შეცდომა: ' . $e->getMessage());
        }
    }

    /**
     * Update transaction (Admin only)
     */
    public function updateTransaction(Request $request, Transaction $transaction): RedirectResponse
    {
        $this->authorize('update', $transaction);

        $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'payment_date' => 'required|date',
            'purpose' => 'required|string',
            'comment' => 'nullable|string|max:500',
        ]);

        try {
            $this->transactionService->updateTransaction(
                $transaction,
                (float) $request->input('amount'),
                $request->input('purpose'),
                new \DateTime($request->input('payment_date')),
                $request->input('comment')
            );

            return redirect()
                ->route('finance.transactions')
                ->with('success', 'რედაქტირება შესრულდა');

        } catch (\Exception $e) {
            return back()->with('error', 'შეცდომა: ' . $e->getMessage());
        }
    }

    /**
     * Delete transaction (Admin only)
     */
    public function destroyTransaction(Transaction $transaction): RedirectResponse
    {
        $this->authorize('delete', $transaction);

        try {
            $this->transactionService->deleteTransaction($transaction);

            return redirect()
                ->route('finance.transactions')
                ->with('success', 'წაშლილია (თანხა დაკორექტირდა)');

        } catch (\Exception $e) {
            return back()->with('error', 'შეცდომა: ' . $e->getMessage());
        }
    }
}
