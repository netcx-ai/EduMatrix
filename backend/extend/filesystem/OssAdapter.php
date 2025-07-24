<?php

namespace filesystem;

use League\Flysystem\AdapterInterface;
use League\Flysystem\Config;
use League\Flysystem\Util;
use OSS\OssClient;
use OSS\Core\OssException;

class OssAdapter implements AdapterInterface
{
    private $client;
    private $bucket;
    private $endpoint;
    private $url;

    public function __construct(array $config)
    {
        $this->bucket = $config['bucket'];
        $this->endpoint = $config['endpoint'];
        $this->url = $config['url'] ?? '';
        
        try {
            $this->client = new OssClient(
                $config['accessKeyId'],
                $config['accessKeySecret'],
                $this->endpoint
            );
        } catch (OssException $e) {
            throw new \Exception('OSS客户端初始化失败: ' . $e->getMessage());
        }
    }

    public function write($path, $contents, Config $config = null)
    {
        try {
            $this->client->putObject($this->bucket, $path, $contents);
            return true;
        } catch (OssException $e) {
            return false;
        }
    }

    public function writeStream($path, $resource, Config $config = null)
    {
        try {
            $this->client->uploadStream($this->bucket, $path, $resource);
            return true;
        } catch (OssException $e) {
            return false;
        }
    }

    public function update($path, $contents, Config $config = null)
    {
        return $this->write($path, $contents, $config);
    }

    public function updateStream($path, $resource, Config $config = null)
    {
        return $this->writeStream($path, $resource, $config);
    }

    public function rename($path, $newpath)
    {
        try {
            $this->client->copyObject($this->bucket, $path, $this->bucket, $newpath);
            $this->client->deleteObject($this->bucket, $path);
            return true;
        } catch (OssException $e) {
            return false;
        }
    }

    public function copy($path, $newpath)
    {
        try {
            $this->client->copyObject($this->bucket, $path, $this->bucket, $newpath);
            return true;
        } catch (OssException $e) {
            return false;
        }
    }

    public function delete($path)
    {
        try {
            $this->client->deleteObject($this->bucket, $path);
            return true;
        } catch (OssException $e) {
            return false;
        }
    }

    public function deleteDir($dirname)
    {
        try {
            $objects = $this->client->listObjects($this->bucket, [
                'prefix' => $dirname,
            ]);

            $keys = [];
            foreach ($objects->getObjectList() as $object) {
                $keys[] = $object->getKey();
            }

            if (!empty($keys)) {
                $this->client->deleteObjects($this->bucket, $keys);
            }
            return true;
        } catch (OssException $e) {
            return false;
        }
    }

    public function createDir($dirname, Config $config = null)
    {
        try {
            $this->client->putObject($this->bucket, rtrim($dirname, '/') . '/', '');
            return true;
        } catch (OssException $e) {
            return false;
        }
    }

    public function setVisibility($path, $visibility)
    {
        // OSS不支持动态设置可见性
        return true;
    }

    public function has($path)
    {
        try {
            return $this->client->doesObjectExist($this->bucket, $path);
        } catch (OssException $e) {
            return false;
        }
    }

    public function read($path)
    {
        try {
            $contents = $this->client->getObject($this->bucket, $path);
            return ['contents' => $contents];
        } catch (OssException $e) {
            return false;
        }
    }

    public function readStream($path)
    {
        try {
            $contents = $this->client->getObject($this->bucket, $path);
            $stream = fopen('php://temp', 'r+');
            fwrite($stream, $contents);
            rewind($stream);
            return ['stream' => $stream];
        } catch (OssException $e) {
            return false;
        }
    }

    public function listContents($directory = '', $recursive = false)
    {
        try {
            $objects = $this->client->listObjects($this->bucket, [
                'prefix' => $directory,
                'delimiter' => $recursive ? '' : '/',
            ]);

            $contents = [];
            
            foreach ($objects->getObjectList() as $object) {
                $contents[] = [
                    'type' => 'file',
                    'path' => $object->getKey(),
                    'size' => $object->getSize(),
                    'timestamp' => $object->getLastModified(),
                ];
            }

            foreach ($objects->getCommonPrefixes() as $prefix) {
                $contents[] = [
                    'type' => 'dir',
                    'path' => rtrim($prefix, '/'),
                ];
            }

            return $contents;
        } catch (OssException $e) {
            return [];
        }
    }

    public function getMetadata($path)
    {
        try {
            $meta = $this->client->getObjectMeta($this->bucket, $path);
            return [
                'type' => 'file',
                'path' => $path,
                'size' => $meta['content-length'] ?? 0,
                'timestamp' => strtotime($meta['last-modified'] ?? ''),
                'visibility' => 'public',
            ];
        } catch (OssException $e) {
            return false;
        }
    }

    public function getSize($path)
    {
        $meta = $this->getMetadata($path);
        return $meta ? ['size' => $meta['size']] : false;
    }

    public function getMimetype($path)
    {
        try {
            $meta = $this->client->getObjectMeta($this->bucket, $path);
            return ['mimetype' => $meta['content-type'] ?? 'application/octet-stream'];
        } catch (OssException $e) {
            return false;
        }
    }

    public function getTimestamp($path)
    {
        $meta = $this->getMetadata($path);
        return $meta ? ['timestamp' => $meta['timestamp']] : false;
    }

    public function getVisibility($path)
    {
        // OSS默认所有文件都是公开的
        return ['visibility' => 'public'];
    }

    public function getUrl($path)
    {
        if ($this->url) {
            return rtrim($this->url, '/') . '/' . ltrim($path, '/');
        }
        return "https://{$this->bucket}.{$this->endpoint}/{$path}";
    }
} 