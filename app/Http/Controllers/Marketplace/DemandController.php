<?php

declare(strict_types=1);

namespace App\Http\Controllers\Marketplace;

use App\Domains\Marketplace\Actions\CreateDemandAction;
use App\Domains\Marketplace\DTOs\CreateDemandDTO;
use App\Domains\Marketplace\DTOs\SearchDemandsDTO;
use App\Domains\Marketplace\ReadModels\Contracts\DemandFeedReadModelInterface;
use App\Domains\Profile\ValueObjects\LocationPoint;
use App\Http\Controllers\Controller;
use App\Http\Requests\Marketplace\CreateDemandRequest;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DemandController extends Controller
{
    public function __construct(
        private readonly CreateDemandAction $createDemandAction,
        private readonly DemandFeedReadModelInterface $feedReadModel
    ) {}

    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'lat' => ['required', 'numeric'],
            'lng' => ['required', 'numeric'],
            'radius' => ['nullable', 'integer', 'min:1000', 'max:100000'],
        ]);

        $dto = new SearchDemandsDTO(
            center: new LocationPoint((float) $validated['lat'], (float) $validated['lng']),
            radiusInMeters: (int) ($validated['radius'] ?? 10000), // Default 10km
            limit: (int) $request->query('limit', 50),
            offset: (int) $request->query('offset', 0)
        );

        $results = $this->feedReadModel->searchNearbyOpenDemands($dto);

        return response()->json($results);
    }

    public function store(CreateDemandRequest $request): JsonResponse
    {
        $dto = new CreateDemandDTO(
            ownerId: $request->user()->id,
            petIds: $request->validated('pet_ids'),
            startDate: Carbon::parse($request->validated('start_date')),
            endDate: Carbon::parse($request->validated('end_date')),
            location: new LocationPoint(
                latitude: (float) $request->validated('latitude'),
                longitude: (float) $request->validated('longitude')
            ),
            requirements: $request->validated('requirements')
        );

        $demand = $this->createDemandAction->execute($dto);

        return response()->json([
            'message' => 'Demand created successfully.',
            'data' => $demand
        ], 201);
    }
}
