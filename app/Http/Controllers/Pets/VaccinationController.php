<?php

declare(strict_types=1);

namespace App\Http\Controllers\Pets;

use App\Domains\Pets\Actions\LogVaccinationAction;
use App\Domains\Pets\DTOs\LogVaccinationDTO;
use App\Domains\Pets\Repositories\Contracts\MedicalDataRepositoryInterface;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VaccinationController extends Controller
{
    public function __construct(
        private readonly MedicalDataRepositoryInterface $medicalDataRepository,
        private readonly LogVaccinationAction $action
    ) {}

    public function index(string $petId): JsonResponse
    {
        $vaccinations = $this->medicalDataRepository->findVaccinationsByPetId($petId);
        return response()->json(['data' => $vaccinations]);
    }

    public function store(Request $request, string $petId): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'date_administered' => ['required', 'date', 'before_or_equal:today'],
            'next_due_date' => ['nullable', 'date', 'after:date_administered'],
            'document_url' => ['nullable', 'url'],
        ]);

        $dto = new LogVaccinationDTO(
            petId: $petId,
            name: $validated['name'],
            dateAdministered: Carbon::parse($validated['date_administered']),
            nextDueDate: isset($validated['next_due_date']) ? Carbon::parse($validated['next_due_date']) : null,
            documentUrl: $validated['document_url'] ?? null
        );

        $vaccination = $this->action->execute($dto);

        return response()->json([
            'message' => 'Vaccination logged successfully.',
            'data' => $vaccination
        ], 201);
    }
}
