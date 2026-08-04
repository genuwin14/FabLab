<?php

namespace App\Models\Concerns;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

/**
 * Images live on the public disk and the row keeps the path.
 *
 * They used to be base64 data URIs in the column itself, which meant every
 * listing query dragged the image bytes along and every page inlined them.
 * Rows written that way still render — `imageUrl` hands a data URI straight
 * back — so existing data keeps working while new uploads become files.
 *
 * A model using this trait declares `$imageColumn` and `$imageDirectory`.
 */
trait HasStoredImage
{
    public function imageColumn(): string
    {
        return $this->imageColumn ?? 'image';
    }

    public function imageDirectory(): string
    {
        return $this->imageDirectory ?? 'uploads';
    }

    /** What a template should put in `src`. Null when there's no image. */
    public function getImageUrlAttribute(): ?string
    {
        return \App\Support\ImageUrl::for($this->{$this->imageColumn()});
    }

    /**
     * Store an upload on the public disk and return the path to save. Any
     * file the model was pointing at is removed.
     */
    public function storeImage(UploadedFile $file): string
    {
        $this->deleteStoredImage();

        return $file->store($this->imageDirectory(), 'public');
    }

    /** Remove the model's image file, if it has one on disk. */
    public function deleteStoredImage(): void
    {
        $value = $this->{$this->imageColumn()};

        if (filled($value) && ! $this->isInlineOrRemote($value)) {
            Storage::disk('public')->delete($value);
        }
    }

    private function isInlineOrRemote(string $value): bool
    {
        return str_starts_with($value, 'data:')
            || str_starts_with($value, 'http://')
            || str_starts_with($value, 'https://');
    }
}
