<?php

namespace App\Http\Controllers;

use App\Enums\CarStatus;
use App\Http\Requests\Car\StoreCarRequest;
use App\Http\Requests\Car\UpdateCarRequest;
use App\Models\Car;
use App\Models\CarFile;
use App\Models\User;
use App\Services\CarFileService;
use App\Services\SmsService;
use App\Services\ClientService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class CarController extends Controller
{
    public function __construct(
        private CarFileService $fileService,
        private SmsService $smsService,
        private ClientService $clientService
    ) {}

    /**
     * Display dashboard with car listing
     */
    public function index(Request $request): View
    {
        $user = Auth::user();
        
        // Build query with role-based filtering
        $query = Car::with('owner')
            ->forUser($user)
            ->search($request->get('search'))
            ->orderBy('id', 'desc');

        // Status filter
        if ($request->filled('filter_status')) {
            $status = CarStatus::tryFrom($request->get('filter_status'));
            if ($status) {
                $query->withStatus($status);
            }
        }

        $cars = $query->paginate(20);

        // Statistics (for admin and dealer)
        $stats = $this->getStatistics($user);

        return view('cars.index', compact('cars', 'stats'));
    }

    /**
     * Show car creation form (Admin only)
     */
    public function create(): View
    {
        $this->authorize('create', Car::class);
        
        $dealers = User::dealers()->get();
        $statuses = CarStatus::toArray();
        
        return view('cars.create', compact('dealers', 'statuses'));
    }

    /**
     * Store a new car
     */
    public function store(StoreCarRequest $request): RedirectResponse
    {
        $this->authorize('create', Car::class);
        
        $validated = $request->validated();
        $user = Auth::user();

        DB::beginTransaction();
        
        try {
            // Auto-create client if needed
            $clientUserId = null;
            if ($validated['user_id'] == $user->id && !empty($validated['client_id_number'])) {
                $clientUserId = $this->clientService->findOrCreateClient(
                    $validated['client_id_number'],
                    $validated['client_name'] ?? '',
                    $validated['client_phone'] ?? '',
                    $validated['lot_number'] ?? null
                );
            }

            // Create the car
            $car = Car::create([
                'user_id' => $validated['user_id'],
                'client_user_id' => $clientUserId,
                'vin' => $validated['vin'],
                'make_model' => $validated['make_model'],
                'year' => $validated['year'] ?? null,
                'lot_number' => $validated['lot_number'] ?? null,
                'auction_name' => $validated['auction_name'] ?? null,
                'status' => $validated['status'] ?? CarStatus::PURCHASED,
                'vehicle_cost' => $validated['vehicle_cost'] ?? 0,
                'shipping_cost' => $validated['shipping_cost'] ?? 0,
                'client_name' => $validated['client_name'] ?? null,
                'client_phone' => $validated['client_phone'] ?? null,
                'client_id_number' => $validated['client_id_number'] ?? null,
            ]);

            // Send SMS notification
            $this->smsService->sendStatusNotification($car, $validated['status']);

            DB::commit();

            return redirect()
                ->route('cars.edit', $car)
                ->with('success', 'მანქანა წარმატებით დაემატა!');

        } catch (\Exception $e) {
            DB::rollBack();
            
            return back()
                ->withInput()
                ->with('error', 'შეცდომა: ' . $e->getMessage());
        }
    }

    /**
     * Display car details
     */
    public function show(Car $car): View
    {
        $this->authorize('view', $car);
        
        $car->load(['owner', 'files', 'transactions']);

        // Group files by category
        $gallery = [
            'auction' => $car->getFilesByCategory('auction'),
            'port' => $car->getFilesByCategory('port'),
            'terminal' => $car->getFilesByCategory('terminal'),
        ];

        return view('cars.show', compact('car', 'gallery'));
    }

    /**
     * Show car edit form
     */
    public function edit(Car $car): View
    {
        $this->authorize('update', $car);
        
        $car->load(['owner', 'files']);
        
        $dealers = Auth::user()->isAdmin() ? User::dealers()->get() : collect();
        $statuses = CarStatus::toArray();
        
        $gallery = [
            'auction' => $car->getFilesByCategory('auction'),
            'port' => $car->getFilesByCategory('port'),
            'terminal' => $car->getFilesByCategory('terminal'),
        ];

        return view('cars.edit', compact('car', 'dealers', 'statuses', 'gallery'));
    }

    /**
     * Update car
     */
    public function update(UpdateCarRequest $request, Car $car): RedirectResponse
    {
        $this->authorize('update', $car);
        
        $validated = $request->validated();
        $user = Auth::user();
        $oldStatus = $car->status;

        DB::beginTransaction();
        
        try {
            // Handle client creation/update (admin only)
            if ($user->isAdmin()) {
                $clientUserId = $car->client_user_id;
                
                if ($validated['user_id'] == $user->id && !empty($validated['client_id_number'])) {
                    $clientUserId = $this->clientService->findOrCreateClient(
                        $validated['client_id_number'],
                        $validated['client_name'] ?? '',
                        $validated['client_phone'] ?? '',
                        $validated['lot_number'] ?? null
                    );
                }
                $validated['client_user_id'] = $clientUserId;
            }

            // Update car
            $car->update($validated);

            // Send SMS if status changed
            $newStatus = CarStatus::from($validated['status']);
            if ($oldStatus !== $newStatus) {
                $this->smsService->sendStatusNotification($car, $newStatus);
            }

            DB::commit();

            return redirect()
                ->route('dashboard')
                ->with('success', 'მანქანა წარმატებით განახლდა!');

        } catch (\Exception $e) {
            DB::rollBack();
            
            return back()
                ->withInput()
                ->with('error', 'შეცდომა: ' . $e->getMessage());
        }
    }

    /**
     * Delete car (Admin only)
     */
    public function destroy(Car $car): RedirectResponse
    {
        $this->authorize('delete', $car);

        // Delete all associated files
        foreach ($car->files as $file) {
            $file->deleteWithFile();
        }

        $car->delete();

        return redirect()
            ->route('dashboard')
            ->with('success', 'მანქანა წარმატებით წაიშალა!');
    }

    /**
     * Upload files to car
     */
    public function uploadFiles(Request $request, Car $car): RedirectResponse
    {
        $this->authorize('update', $car);

        $request->validate([
            'gallery' => 'required|array',
            'gallery.*' => 'file|max:20480|mimes:jpg,jpeg,png,gif,webp,mp4,mov,pdf',
            'upload_category' => 'required|in:auction,port,terminal',
        ]);

        $category = $request->input('upload_category');
        $files = $request->file('gallery');

        foreach ($files as $file) {
            $this->fileService->uploadFile($car, $file, $category);
        }

        return back()->with('success', 'ფაილები წარმატებით აიტვირთა!');
    }

    /**
     * Delete a file
     */
    public function deleteFile(Car $car, CarFile $file): RedirectResponse
    {
        $this->authorize('update', $car);

        if ($file->car_id !== $car->id) {
            abort(403);
        }

        // If this was the main photo, clear it
        if ($car->main_photo === $file->file_path) {
            $car->update(['main_photo' => null]);
        }

        $file->deleteWithFile();

        return back()->with('success', 'ფაილი წაიშალა!');
    }

    /**
     * Set main photo
     */
    public function setMainPhoto(Car $car, CarFile $file): RedirectResponse
    {
        $this->authorize('update', $car);

        if ($file->car_id !== $car->id || !$file->is_image) {
            abort(403);
        }

        $car->setMainPhoto($file);

        return back()->with('success', 'მთავარი ფოტო შეიცვალა!');
    }

    /**
     * Get statistics for dashboard
     */
    private function getStatistics(User $user): array
    {
        if ($user->isClient()) {
            return ['total' => 0, 'on_way' => 0, 'debt' => 0];
        }

        $baseQuery = Car::query();
        
        if ($user->isDealer()) {
            $baseQuery->where('user_id', $user->id);
        }

        return [
            'total' => (clone $baseQuery)->count(),
            'on_way' => (clone $baseQuery)->onWay()->count(),
            'debt' => (clone $baseQuery)
                ->selectRaw('SUM(vehicle_cost + shipping_cost + additional_cost - paid_amount) as debt')
                ->value('debt') ?? 0,
        ];
    }
}
