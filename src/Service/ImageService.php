<?php


namespace App\Service;


use App\Entity\File;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class ImageService
{
    /** @var ParameterBagInterface $parameterBag */
    private $parameterBag;

    /** @var UploaderService $uploaderService */
    private $uploaderService;

    /** @var LoggerInterface $logger */
    private $logger;

    public function __construct(
        ParameterBagInterface $parameterBag,
        UploaderService $uploaderService,
        LoggerInterface $logger
    )
    {
        $this->logger = $logger;
        $this->parameterBag = $parameterBag;
        $this->uploaderService = $uploaderService;
    }

    public function unparse_url($parsed_url): string
    {
        $scheme   = isset($parsed_url['scheme']) ? $parsed_url['scheme'] . '://' : '';
        $host     = $parsed_url['host'] ?? '';
        $port     = isset($parsed_url['port']) ? ':' . $parsed_url['port'] : '';
        $user     = $parsed_url['user'] ?? '';
        $pass     = isset($parsed_url['pass']) ? ':' . $parsed_url['pass']  : '';
        $pass     = ($user || $pass) ? "$pass@" : '';
        $path     = $parsed_url['path'] ?? '';
        $query    = isset($parsed_url['query']) ? '?' . $parsed_url['query'] : '';
        $fragment = isset($parsed_url['fragment']) ? '#' . $parsed_url['fragment'] : '';
        return "$scheme$user$pass$host$port$path$query$fragment";
    }


    /**
     * @param string $imageUrl
     * @param array $headers
     * @return bool|string
     */
    public function getImage(string $imageUrl, array $headers = []) {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $imageUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLINFO_HEADER_OUT, 1);

        $headers = array_merge($headers, [
            'User-Agent: ' . $this->parameterBag->get('user_agent'),
//            'Upgrade-Insecure-Requests: 1', // specs Fanfox ? visiblement pas utile
//            'Connection: keep-alive', // specs Fanfox ? visiblement pas utile
        ]);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $result = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if ($httpCode !== 200) {
            $this->logger->error($httpCode);
            return null;
        }
        curl_close($ch);

        return $result;
    }

    /**
     * @param $result
     * @param string $imageUrl
     * @param string $directory
     * @return File
     */
    public function getFile($result, string $imageUrl, string $directory) {
        // put temp file
        $filename = md5(time() . mt_rand());
        $extension = pathinfo($imageUrl, PATHINFO_EXTENSION);
        $fileExt = "$filename.$extension";
        $filePath = $this->parameterBag->get($directory . '_path') . "/$fileExt";
        $file = fopen($filePath, 'w+');
        fwrite($file, $result);
        fclose($file);

        $entityFile = new File();
        $entityFile->setName($fileExt)
            ->setPath($this->parameterBag->get($directory . '_directory'));

        return $entityFile;
    }

    public function getFileUrl(File $file) {
        return $this->parameterBag->get('host_url') . 'uploads/' . $file->getPath() . '/' . $file->getName();
    }

    public function uploadChapterImage(string $imageUrl, array $headers = []): ?File
    {
        return $this->uploadImage('chapters', $imageUrl, $headers);
    }

    public function uploadMangaImage(string $imageUrl, array $headers = []): ?File
    {
        return $this->uploadImage('mangas', $imageUrl, $headers);
    }

    /**
     * @param string $directory
     * @param string $imageUrl
     * @param array $headers
     * @param array $options
     * @return File
     */
    public function uploadImage(string $directory, string $imageUrl, array $headers = [], array $options = []): ?File
    {
        if ($this->parameterBag->get('save_on_filesystem') === true) {
            $result = $this->getImage($imageUrl, $headers);

            if (empty($result)) {
                return null;
            }

            $parsed = parse_url($imageUrl);
            unset($parsed['query'], $parsed['fragment']);
            $imageUrl = $this->unparse_url($parsed);
            $extension = pathinfo($imageUrl, PATHINFO_EXTENSION);

            $url = $this->uploaderService->upload($result, $directory, $extension);
        } else {
            $url = $imageUrl;
        }

        $file = new File();
        $file->setExternalUrl($url);

        return $file;
    }
}
