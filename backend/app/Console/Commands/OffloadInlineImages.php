<?php

namespace App\Console\Commands;

use App\Models\Product;
use App\Models\RawMaterial;
use App\Models\Texture;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Converts rows still holding a base64 data URI into files on the public disk.
 *
 * This is deliberately a command rather than a migration: it needs a writable
 * public disk and a `storage:link` in place, and rows it hasn't touched keep
 * rendering perfectly well from the database. Run it once those are ready.
 */
class OffloadInlineImages extends Command
{
    protected $signature = 'images:offload {--dry-run : List what would move without writing anything}';

    protected $description = 'Move base64 images out of the database and onto the public disk.';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $moved = 0;
        $skipped = 0;

        foreach ([Product::class, RawMaterial::class, Texture::class] as $class) {
            /** @var Model $sample */
            $sample = new $class;
            $column = $sample->imageColumn();
            $directory = $sample->imageDirectory();

            $rows = $class::withTrashed()
                ->where($column, 'like', 'data:%')
                ->get();

            $this->info(sprintf('%s: %d inline image(s)', class_basename($class), $rows->count()));

            foreach ($rows as $row) {
                $path = $this->write($row->{$column}, $directory, $dryRun);

                if ($path === null) {
                    $this->warn("  skipped #{$row->getKey()} ({$row->name}) — could not read the inline data");
                    $skipped++;
                    continue;
                }

                if (! $dryRun) {
                    $row->forceFill([$column => $path])->saveQuietly();
                }

                $this->line("  #{$row->getKey()} {$row->name} → {$path}");
                $moved++;
            }
        }

        $this->newLine();
        $this->info($dryRun
            ? "Dry run: {$moved} image(s) would move, {$skipped} skipped."
            : "Moved {$moved} image(s), skipped {$skipped}.");

        return self::SUCCESS;
    }

    /** Decode one data URI onto the public disk, returning its path. */
    private function write(string $dataUri, string $directory, bool $dryRun): ?string
    {
        if (! preg_match('/^data:([^;]+);base64,(.*)$/s', $dataUri, $matches)) {
            return null;
        }

        $binary = base64_decode($matches[2], true);

        if ($binary === false) {
            return null;
        }

        $extension = match (strtolower($matches[1])) {
            'image/png' => 'png',
            'image/gif' => 'gif',
            'image/webp' => 'webp',
            'image/svg+xml' => 'svg',
            default => 'jpg',
        };

        $path = $directory . '/' . Str::random(40) . '.' . $extension;

        if (! $dryRun) {
            Storage::disk('public')->put($path, $binary);
        }

        return $path;
    }
}
