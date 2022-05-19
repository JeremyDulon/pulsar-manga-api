<?php


namespace App\Service;

use Gaufrette\Adapter\AwsS3;
use Gaufrette\Adapter\Local;
use Gaufrette\Adapter\MetadataSupporter;
use Gaufrette\Filesystem;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class UploaderService
{
    /** @var Filesystem $filesystem  */
    private $filesystem;

    /** @var LoggerInterface $logger */
    private $logger;

    /** @var ParameterBagInterface $parameterBag */
    private $parameterBag;

    private const KEY = 'art/';

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
    public function upload(string $fileData, string $path, string $extension): string
    {
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

        $url = $filePath;
        if ($adapter instanceof AwsS3) {
            $url = $this->parameterBag->get('aws_s3_url') . $url;
        }

        if ($adapter instanceof Local) {
            $url = $this->parameterBag->get('host_url') . 'uploads/' . $url;
        }

        return $url;
    }
}
