<?php

namespace App\Filesystem;

use App\Services\BunnyStorageService;
use League\Flysystem\Config;
use League\Flysystem\DirectoryAttributes;
use League\Flysystem\FileAttributes;
use League\Flysystem\FilesystemAdapter;
use League\Flysystem\UnableToCheckExistence;
use League\Flysystem\UnableToCopyFile;
use League\Flysystem\UnableToDeleteDirectory;
use League\Flysystem\UnableToDeleteFile;
use League\Flysystem\UnableToReadFile;
use League\Flysystem\UnableToRetrieveMetadata;
use League\Flysystem\UnableToSetVisibility;
use League\Flysystem\UnableToWriteFile;
use League\Flysystem\Visibility;

class BunnyFilesystemAdapter implements FilesystemAdapter
{
    public function __construct(
        protected BunnyStorageService $bunny,
    ) {}

    public function fileExists(string $path): bool
    {
        try {
            return $this->bunny->exists($path);
        } catch (\Throwable) {
            throw UnableToCheckExistence::forLocation($path, new \Exception('Unable to check Bunny file existence.'));
        }
    }

    public function directoryExists(string $path): bool
    {
        try {
            $this->bunny->list(trim($path, '/').'/');

            return true;
        } catch (\Throwable) {
            return false;
        }
    }

    public function write(string $path, string $contents, Config $config): void
    {
        $mime = $config->get('mimetype') ?? $this->bunny->mimeTypeForPath($path);

        if (! $this->bunny->upload($path, $contents, $mime)) {
            throw UnableToWriteFile::atLocation($path, 'Bunny upload failed.');
        }
    }

    public function writeStream(string $path, $contents, Config $config): void
    {
        $data = stream_get_contents($contents);

        if ($data === false) {
            throw UnableToWriteFile::atLocation($path, 'Unable to read stream for Bunny upload.');
        }

        $this->write($path, $data, $config);
    }

    public function read(string $path): string
    {
        try {
            return $this->bunny->download($path);
        } catch (\Throwable $exception) {
            throw UnableToReadFile::fromLocation($path, $exception->getMessage(), $exception);
        }
    }

    public function readStream(string $path)
    {
        $stream = fopen('php://temp', 'r+');
        fwrite($stream, $this->read($path));
        rewind($stream);

        return $stream;
    }

    public function delete(string $path): void
    {
        if (! $this->bunny->delete($path)) {
            throw UnableToDeleteFile::atLocation($path);
        }
    }

    public function deleteDirectory(string $path): void
    {
        throw UnableToDeleteDirectory::atLocation($path, 'Directory delete is not supported on Bunny disk.');
    }

    public function createDirectory(string $path, Config $config): void
    {
        // Bunny creates folders implicitly on upload.
    }

    public function setVisibility(string $path, string $visibility): void
    {
        throw UnableToSetVisibility::atLocation($path, 'Visibility is not supported on Bunny disk.');
    }

    public function visibility(string $path): FileAttributes
    {
        return new FileAttributes($path, null, Visibility::PUBLIC);
    }

    public function mimeType(string $path): FileAttributes
    {
        return new FileAttributes($path, null, null, null, $this->bunny->mimeTypeForPath($path));
    }

    public function lastModified(string $path): FileAttributes
    {
        return new FileAttributes($path, null, null, time());
    }

    public function fileSize(string $path): FileAttributes
    {
        try {
            return new FileAttributes($path, strlen($this->read($path)));
        } catch (\Throwable $exception) {
            throw UnableToRetrieveMetadata::fileSize($path, $exception->getMessage(), $exception);
        }
    }

    public function listContents(string $path, bool $deep): iterable
    {
        $prefix = trim($path, '/');
        $items = $this->bunny->list($prefix === '' ? '' : $prefix.'/');

        foreach ($items as $item) {
            $name = $item['ObjectName'] ?? null;

            if ($name === null) {
                continue;
            }

            $itemPath = trim(($prefix === '' ? '' : $prefix.'/').$name, '/');

            if ($item['IsDirectory'] ?? false) {
                yield new DirectoryAttributes($itemPath);

                if ($deep) {
                    yield from $this->listContents($itemPath, true);
                }

                continue;
            }

            yield new FileAttributes(
                $itemPath,
                isset($item['Length']) ? (int) $item['Length'] : null,
                Visibility::PUBLIC,
                isset($item['LastChanged']) ? strtotime((string) $item['LastChanged']) : null,
                $this->bunny->mimeTypeForPath($itemPath),
            );
        }
    }

    public function move(string $source, string $destination, Config $config): void
    {
        $this->copy($source, $destination, $config);
        $this->delete($source);
    }

    public function copy(string $source, string $destination, Config $config): void
    {
        try {
            $this->write($destination, $this->read($source), $config);
        } catch (\Throwable $exception) {
            throw UnableToCopyFile::fromLocationTo($source, $destination, $exception);
        }
    }

    /**
     * Public CDN URL for Filament previews and Storage::url().
     */
    public function getUrl(string $path): string
    {
        return $this->bunny->publicUrl($path);
    }
}
