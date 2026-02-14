<?php

namespace App\Services;

use App\Models\Attachment;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Servizio centralizzato per salvare allegati sotto media/attachments/{context}/...
 * Path univoco e adatto a backup/export.
 */
class AttachmentService
{
    private const MEDIA_ROOT = 'media/attachments';

    private const DISK = 'local';

    /**
     * Salva un file e crea il record Attachment collegato a un modello.
     * Path: media/attachments/{context}/{year}/{month}/{uuid}_{slug}.{ext}
     */
    public function store(UploadedFile $file, Model $attachable, ?string $tag = null): Attachment
    {
        $context = $this->contextForModel($attachable);
        $year = now()->format('Y');
        $month = now()->format('m');
        $uuid = Str::uuid()->toString();
        $slug = Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) ?: 'file';
        $ext = $file->getClientOriginalExtension() ?: $file->guessExtension() ?: 'bin';
        $filename = $uuid . '_' . $slug . '.' . $ext;
        $dir = self::MEDIA_ROOT . '/' . $context . '/' . $year . '/' . $month;
        if (! Storage::disk(self::DISK)->exists($dir)) {
            Storage::disk(self::DISK)->makeDirectory($dir);
        }
        $path = $file->storeAs($dir, $filename, self::DISK);
        if ($path === false) {
            throw new \RuntimeException('Impossibile salvare il file su disco. Verifica permessi di storage.');
        }

        return Attachment::create([
            'attachable_type' => $attachable->getMorphClass(),
            'attachable_id' => $attachable->getKey(),
            'tag' => $tag,
            'file_path' => $path,
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
            'disk' => self::DISK,
        ]);
    }

    /**
     * Salva un allegato per "setting" (attachable_type=setting, attachable_id null).
     * Path: media/attachments/settings/{year}/{month}/...
     */
    public function storeForSetting(UploadedFile $file, string $tag): Attachment
    {
        $context = 'settings';
        $year = now()->format('Y');
        $month = now()->format('m');
        $uuid = Str::uuid()->toString();
        $slug = Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) ?: 'file';
        $ext = $file->getClientOriginalExtension() ?: $file->guessExtension() ?: 'png';
        $filename = $uuid . '_' . $slug . '.' . $ext;
        $dir = self::MEDIA_ROOT . '/' . $context . '/' . $year . '/' . $month;
        if (! Storage::disk(self::DISK)->exists($dir)) {
            Storage::disk(self::DISK)->makeDirectory($dir);
        }
        $path = $file->storeAs($dir, $filename, self::DISK);
        if ($path === false) {
            throw new \RuntimeException('Impossibile salvare il file su disco. Verifica permessi di storage.');
        }

        return Attachment::create([
            'attachable_type' => 'setting',
            'attachable_id' => null,
            'tag' => $tag,
            'file_path' => $path,
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
            'disk' => self::DISK,
        ]);
    }

    private function contextForModel(Model $model): string
    {
        return Str::snake(Str::plural(class_basename($model)));
    }
}
