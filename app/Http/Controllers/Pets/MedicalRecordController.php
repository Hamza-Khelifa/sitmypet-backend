<?php

declare(strict_types=1);

namespace App\Http\Controllers\Pets;

use App\Domains\Pets\Actions\UpdateMedicalHistoryAction;
use App\Domains\Pets\DTOs\AddMedicalRecordDTO;
use App\Domains\Pets\Repositories\Contracts\MedicalDataRepositoryInterface;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MedicalRecordController extends Controller
{
    public function __construct(
        private readonly MedicalDataRepositoryInterface $medicalDataRepository,
        private readonly UpdateMedicalHistoryAction $action
    ) {}

    public function index(string $petId): JsonResponse
    {
        $records = $this->medicalDataRepository->findMedicalRecordsByPetId($petId);
        return response()->json(['data' => $records]);
    }

    public function store(Request $request, string $petId): JsonResponse
    {
        $validated = $request->validate([
            'condition' => ['required', 'string', 'max:255'],
            'medication' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
        ]);

        $dto = new AddMedicalRecordDTO(
            petId: $petId,
            condition: $validated['condition'],
            medication: $validated['medication'] ?? null,
            notes: $validated['notes'] ?? null
        );

        $record = $this->action->execute($dto);

        return response()->json([
            'message' => 'Medical record added successfully.',
            'data' => $record
        ], 201);
    }
}
