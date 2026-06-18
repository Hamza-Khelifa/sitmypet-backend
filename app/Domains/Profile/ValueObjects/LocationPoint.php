<?php

declare(strict_types=1);

namespace App\Domains\Profile\ValueObjects;

use InvalidArgumentException;

final class LocationPoint
{
    public function __construct(
        public readonly float $latitude,
        public readonly float $longitude
    ) {
        if ($this->latitude < -90 || $this->latitude > 90) {
            throw new InvalidArgumentException('Latitude must be between -90 and 90 degrees.');
        }

        if ($this->longitude < -180 || $this->longitude > 180) {
            throw new InvalidArgumentException('Longitude must be between -180 and 180 degrees.');
        }
    }

    /**
     * Convert to WKT (Well-Known Text) for PostGIS.
     */
    public function toWkt(): string
    {
        // PostGIS uses Longitude Latitude (X Y)
        return sprintf('POINT(%F %F)', $this->longitude, $this->latitude);
    }
}
