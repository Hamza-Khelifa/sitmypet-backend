<?php

declare(strict_types=1);

namespace App\Domains\Marketplace\ReadModels\Contracts;

use App\Domains\Marketplace\DTOs\SearchDemandsDTO;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface DemandFeedReadModelInterface
{
    /**
     * Executes optimized spatial queries to return paginated nearby open demands.
     */
    public function searchNearbyOpenDemands(SearchDemandsDTO $dto): LengthAwarePaginator;
}
