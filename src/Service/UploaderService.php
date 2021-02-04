<?php


namespace App\Service;

use Gaufrette\Filesystem;

class UploaderService
{
    /** @var Filesystem $filesystem  */
    private $filesystem;

    private const KEY = 'art/';

    public function __construct(Filesystem $filesystem) {
        $this->filesystem = $filesystem;
    }

    /**
     * @param string $fileData
     * @param string $path
     * @param string $extension
     * @return string
     */
    public function upload(string $fileData, string $path, string $extension) {
        $tmpFile = tmpfile();
        $tmpFilePath = stream_get_meta_data($tmpFile)['uri'];
        fwrite($tmpFile, $fileData);
        $mime = mime_content_type($tmpFilePath);
        fclose($tmpFile);

        $name = md5( time() . mt_rand());

        return  $this->uploadToAdapter(
            $fileData,
            $path . '/' . "$name.$extension",
            $mime
        );
    }

    /**
     * @param string $fileData
     * @param string $filePath
     * @param string $mime
     * @return string
     */
    public function uploadToAdapter(string $fileData, string $filePath, string $mime) {
        $adapter = $this->filesystem->getAdapter();
        $adapter->setMetadata($filePath, array('contentType' => $mime));
        $adapter->write(self::KEY . $filePath, $fileData);

        return $filePath;
    }
}
