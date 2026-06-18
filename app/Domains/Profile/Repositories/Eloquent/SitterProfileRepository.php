<?php

declare(strict_types=1);

namespace App\Domains\Profile\Repositories\Eloquent;

use App\Domains\Profile\Entities\SitterProfile;
use App\Domains\Profile\Repositories\Contracts\SitterProfileRepositoryInterface;
use App\Domains\Profile\ValueObjects\LocationPoint;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

final class SitterProfileRepository implements SitterProfileRepositoryInterface
{
    public function findById(string $id): ?SitterProfile
    {
        return SitterProfile::find($id);
    }
    
    public function findByUserId(string $userId): ?SitterProfile
    {
        return SitterProfile::where('user_id', $userId)->first();
    }
    
    public function save(SitterProfile $profile): SitterProfile
    {
        $profile->save();
        return $profile;
    }
    
    public function updateLocation(string $id, LocationPoint $point): bool
    {
        // Use PostGIS ST_GeographyFromText to save the exact point geometry
        return (bool) DB::update(
            'UPDATE sitter_profiles SET location = ST_GeographyFromText(?) WHERE id = ?',
            [$point->toWkt(), $id]
        );
    }
    
    public function findNearby(LocationPoint $center, int $radiusInMeters): Collection
    {
        // Use PostGIS ST_DWithin to perform an optimized spatial radius search
        return SitterProfile::where('is_verified', true)
            ->whereRaw(
                'ST_DWithin(location, ST_GeographyFromText(?), ?)',
                [$center->toWkt(), $radiusInMeters]
            )
            ->get();
    }
}
