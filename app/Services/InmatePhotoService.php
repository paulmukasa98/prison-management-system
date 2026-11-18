<?php

namespace App\Services;

use App\Models\Document;
use App\Models\DocumentLink;
use App\Models\Inmate;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Throwable;

class InmatePhotoService
{
    public const RULES = ['nullable', 'file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048', 'dimensions:max_width=3000,max_height=3000'];

    public function __construct(protected AuditService $auditService) {}

    /** @param callable(): Inmate $save */
    public function save(callable $save, ?UploadedFile $photo, User $actor): Inmate
    {
        $stored = null;
        try {
            return DB::transaction(function () use ($save, $photo, $actor, &$stored): Inmate {
                $inmate = $save();
                if ($photo !== null) {
                    $stored = $this->store($inmate, $photo, $actor);
                }

                return $inmate;
            });
        } catch (Throwable $exception) {
            if ($stored !== null) {
                Storage::disk('local')->delete($stored->storage_key);
            }
            throw $exception;
        }
    }

    private function store(Inmate $inmate, UploadedFile $photo, User $actor): Document
    {
        Validator::validate(['photo' => $photo], ['photo' => self::RULES]);
        $bytes = $this->normalize($photo);
        $key = 'inmate-photos/'.Str::uuid().'.jpg';
        try {
            if (! Storage::disk('local')->put($key, $bytes, ['visibility' => 'private'])) {
                throw ValidationException::withMessages(['photo' => 'The photograph could not be saved. Please try again.']);
            }
            $document = new Document([
                'document_ref' => 'DOC-'.Str::uuid(), 'mime_type' => 'image/jpeg',
                'size_bytes' => strlen($bytes), 'sha256' => hash('sha256', $bytes), 'state' => 'Available',
            ]);
            $document->disk = 'local';
            $document->storage_key = $key;
            $document->original_name = 'inmate-photo.jpg';
            $document->uploaded_by = $actor->id;
            $document->save();
            $link = new DocumentLink(['purpose' => DocumentLink::INMATE_PHOTO]);
            $link->document()->associate($document);
            $link->inmate()->associate($inmate);
            $link->save();
            $this->auditService->log(
                action: 'inmate_photo_uploaded', outcome: 'Success', resourceType: 'Inmate',
                resourceReference: $inmate->prisoner_number,
                changes: ['document_ref' => $document->document_ref, 'mime_type' => $document->mime_type, 'size_bytes' => $document->size_bytes],
                facilityId: $inmate->currentFacilityId(), actorId: $actor->id
            );

            return $document;
        } catch (Throwable $exception) {
            Storage::disk('local')->delete($key);
            throw $exception;
        }
    }

    private function normalize(UploadedFile $photo): string
    {
        $source = @imagecreatefromstring($photo->getContent());
        if ($source === false) {
            throw ValidationException::withMessages(['photo' => 'Choose a valid JPEG, PNG or WebP photograph.']);
        }
        $scale = min(1, 1600 / max(imagesx($source), imagesy($source)));
        $width = max(1, (int) round(imagesx($source) * $scale));
        $height = max(1, (int) round(imagesy($source) * $scale));
        $image = imagecreatetruecolor($width, $height);
        imagefill($image, 0, 0, imagecolorallocate($image, 255, 255, 255));
        imagecopyresampled($image, $source, 0, 0, 0, 0, $width, $height, imagesx($source), imagesy($source));
        ob_start();
        try {
            $written = imagejpeg($image, null, 85);
            $bytes = ob_get_contents();
        } finally {
            ob_end_clean();
        }
        if (! $written || $bytes === false) {
            throw ValidationException::withMessages(['photo' => 'The photograph could not be processed. Please choose another image.']);
        }

        return $bytes;
    }
}
