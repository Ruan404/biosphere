<?php
namespace App\Film\Dto;

class FilmChunkUploadDto
{
    public function __construct(
        public readonly array $file,
        public readonly int $chunkNumber,
        public readonly int $totalChunks,
        public readonly string $filename,
        public readonly string $token,
        public readonly ?string $title = null,
        public readonly ?string $description = null,
        public readonly ?array $coverFile = null,
    ) {}
}
