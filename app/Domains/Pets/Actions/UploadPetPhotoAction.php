<?php

declare(strict_types=1);

namespace App\Domains\Pets\Actions;

use App\Domains\Pets\Entities\PetPhoto;
use App\Domains\Pets\Repositories\Contracts\PetRepositoryInterface;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;

final class UploadPetPhotoAction
{
    public function __construct(
        private readonly PetRepositoryInterface $petRepository
    ) {}

    public function execute(string $petId, UploadedFile $file, bool $isPrimary = false): PetPhoto
    {
        $pet = $this->petRepository->findById($petId);
        
        if (!$pet) {
            throw new InvalidArgumentException('Pet not found.');
        }

        // Ideally, we upload to S3 here. For scaffolding, using local 'public' disk.
        $path = $file->store('pet_photos', 'public');
        
        if (!$path) {
            throw new \RuntimeException('Failed to store photo.');
        }

        // If this is set to primary, we should unset other primary photos for this pet first.
        if ($isPrimary) {
            PetPhoto::where('pet_id', $petId)->update(['is_primary' => false]);
        }

        $photo = new PetPhoto([
            'pet_id' => $petId,
            'photo_url' => Storage::url($path),
            'is_primary' => $isPrimary,
        ]);

        $photo->save();

        return $photo;
    }
}
