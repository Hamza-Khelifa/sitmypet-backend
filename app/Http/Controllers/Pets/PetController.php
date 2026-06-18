<?php

declare(strict_types=1);

namespace App\Http\Controllers\Pets;

use App\Domains\Pets\Actions\RegisterPetAction;
use App\Domains\Pets\DTOs\RegisterPetDTO;
use App\Domains\Pets\Enums\SpeciesType;
use App\Domains\Pets\Repositories\Contracts\PetRepositoryInterface;
use App\Http\Controllers\Controller;
use App\Http\Requests\Pets\RegisterPetRequest;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;

class PetController extends Controller
{
    public function __construct(
        private readonly PetRepositoryInterface $petRepository,
        private readonly RegisterPetAction $registerPetAction
    ) {}

    public function index(): JsonResponse
    {
        $pets = $this->petRepository->findByOwnerId(request()->user()->id);
        return response()->json(['data' => $pets]);
    }

    public function store(RegisterPetRequest $request): JsonResponse
    {
        $dto = new RegisterPetDTO(
            ownerId: $request->user()->id,
            name: $request->validated('name'),
            species: SpeciesType::from($request->validated('species')),
            breed: $request->validated('breed'),
            birthDate: $request->validated('birth_date') ? Carbon::parse($request->validated('birth_date')) : null,
            weight: (float) $request->validated('weight'),
            behaviorNotes: $request->validated('behavior_notes'),
            emergencyContactName: $request->validated('emergency_contact_name'),
            emergencyContactPhone: $request->validated('emergency_contact_phone')
        );

        $pet = $this->registerPetAction->execute($dto);

        return response()->json([
            'message' => 'Pet registered successfully.',
            'data' => $pet
        ], 201);
    }

    public function show(string $id): JsonResponse
    {
        $pet = $this->petRepository->findById($id);

        if (!$pet) {
            return response()->json(['message' => 'Pet not found.'], 404);
        }

        return response()->json(['data' => $pet]);
    }

    public function logVaccination(\Illuminate\Http\Request $request, string $petId): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'date_administered' => 'required|date',
            'next_due_date' => 'nullable|date',
        ]);

        \Illuminate\Support\Facades\DB::table('vaccinations')->insert([
            'id' => (string) \Illuminate\Support\Str::uuid(),
            'pet_id' => $petId,
            'name' => $validated['name'],
            'date_administered' => $validated['date_administered'],
            'next_due_date' => $validated['next_due_date'] ?? null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json([
            'message' => 'Vaccination logged successfully.',
            'data' => [
                'name' => $validated['name']
            ]
        ], 201);
    }
}
