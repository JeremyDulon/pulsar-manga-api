<?php


namespace App\Service;

use App\Entity\File;
use Gaufrette\Adapter\AwsS3;
use Gaufrette\Adapter\Local;
use Gaufrette\Adapter\MetadataSupporter;
use Gaufrette\Filesystem;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class UploaderService
{
    /** @var Filesystem $filesystem  */
    private Filesystem $filesystem;

    /** @var LoggerInterface $logger */
    private LoggerInterface $logger;

    /** @var ParameterBagInterface $parameterBag */
    private ParameterBagInterface $parameterBag;

    public function __construct(
        Filesystem $filesystem,
        ParameterBagInterface $parameterBag,
        LoggerInterface $logger
    ) {
        $this->logger = $logger;
        $this->filesystem = $filesystem;
        $this->parameterBag = $parameterBag;
    }

    /**
     * @param string $fileData
     * @param string $path
     * @param string $extension
     * @return string
     */
    public function upload(File &$file, string $fileData, string $path, string $extension): string
    {
        $tmpFile = tmpfile();
        $tmpFilePath = stream_get_meta_data($tmpFile)['uri'];
        fwrite($tmpFile, $fileData);
        $mime = mime_content_type($tmpFilePath);
        fclose($tmpFile);

        $name = md5( time() . mt_rand());

        $filePath = "$path/$name.$extension";
        $file->setPath($filePath);

        return  $this->uploadToAdapter(
            $fileData,
            $filePath,
            $mime
        );
    }

    /**
     * @param string $fileData
     * @param string $filePath
     * @param string $mime
     * @return string
     */
    public function uploadToAdapter(string $fileData, string $filePath, string $mime): string
    {
        $adapter = $this->filesystem->getAdapter();
        if ($adapter instanceof MetadataSupporter) {
            $adapter->setMetadata($filePath, array('contentType' => $mime));
        }
        $bytesWritten = $adapter->write($filePath, $fileData);
        if (empty($bytesWritten)) {
            $this->logger->error('Image not uploaded: ' . $filePath);
        }

        $url = '';
        if ($adapter instanceof AwsS3) {
            $url = $this->parameterBag->get('bucket_endpoint') . $filePath;
        }

        if ($adapter instanceof Local) {
            $url = $this->parameterBag->get('host_url') . 'uploads/' . $filePath;
        }

        return $url;
    }
}
