<?php


namespace App\Service;


use App\Entity\File;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class ImageService
{
    protected $parameterBag;

    protected $uploaderService;

    public function __construct(ParameterBagInterface $parameterBag, UploaderService $uploaderService)
    {
        $this->parameterBag = $parameterBag;
        $this->uploaderService = $uploaderService;
    }

    public function unparse_url($parsed_url): string
    {
        $scheme   = isset($parsed_url['scheme']) ? $parsed_url['scheme'] . '://' : '';
        $host     = isset($parsed_url['host']) ? $parsed_url['host'] : '';
        $port     = isset($parsed_url['port']) ? ':' . $parsed_url['port'] : '';
        $user     = isset($parsed_url['user']) ? $parsed_url['user'] : '';
        $pass     = isset($parsed_url['pass']) ? ':' . $parsed_url['pass']  : '';
        $pass     = ($user || $pass) ? "$pass@" : '';
        $path     = isset($parsed_url['path']) ? $parsed_url['path'] : '';
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

        $headers = array_merge($headers, [
            'User-Agent: Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/84.0.4147.89 Safari/537.36'
        ]);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $result = curl_exec($ch);
        if (curl_errno($ch)) {
            echo 'Error:' . curl_error($ch);
        }
        curl_close($ch);

        return $result;
    }

    /**
     * @param string $imageUrl
     * @param array $headers
     * @return File
     */
    public function getMangaImage(string $imageUrl, array $headers = []) {
        $result = $this->getImage($imageUrl, $headers);

        return $this->getFile($result, $imageUrl, 'mangas');
    }

    /**
     * @param string $imageUrl
     * @param array $headers
     * @return File
     */
    public function getChapterImage(string $imageUrl, array $headers = []) {
        $result = $this->getImage($imageUrl, $headers);

        return $this->getFile($result, $imageUrl, 'chapter_pages');
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

    public function uploadChapterImage(string $imageUrl, array $headers = []) {
        return $this->uploadImage('chapters', $imageUrl, $headers);
    }

    public function uploadMangaImage(string $imageUrl, array $headers = []) {
        return $this->uploadImage('mangas', $imageUrl, $headers);
    }

    /**
     * @param string $directory
     * @param string $imageUrl
     * @param array $headers
     * @return string
     */
    public function uploadImage(string $directory, string $imageUrl, array $headers = []) {
        if ($this->parameterBag->get('amazon_store_files') === true) {
            $parsed = parse_url($imageUrl);
            unset($parsed['query'], $parsed['fragment']);
            $imageUrl = $this->unparse_url($parsed);
            $result = $this->getImage($imageUrl, $headers);
            $extension = pathinfo($imageUrl, PATHINFO_EXTENSION);

            $url = $this->parameterBag->get('aws_s3_url') . $this->uploaderService->upload($result, $directory, $extension);
        } else {
            $url = $imageUrl;
        }

        $file = new File();
        $file->setExternalUrl($url);

        return $file;
    }
}
