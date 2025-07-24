<?php

namespace filesystem;

use League\Flysystem\AdapterInterface;
use League\Flysystem\Config;
use Qcloud\Cos\Client;

class CosAdapter implements AdapterInterface
{
    /**
     * COS 客户端
     * @var Client
     */
    private $client;
    private $bucket;
    private $region;
    private $url;

    public function __construct(array $config)
    {
        $this->bucket = $config['bucket'];
        $this->region = $config['region'];
        $this->url = $config['url'] ?? '';
        $scheme = $config['scheme'] ?? 'https';
        
        try {
            $this->client = new Client([
                'region'      => $this->region,
                'scheme'      => $scheme,
                'credentials' => [
                    'secretId'  => $config['secretId'],
                    'secretKey' => $config['secretKey'],
                ],
                'http'        => [
                    'verify' => $config['verify'] ?? false,
                ],
            ]);
        } catch (\Exception $e) {
            throw new \Exception('COS客户端初始化失败: ' . $e->getMessage());
        }
    }

    public function write($path, $contents, Config $config = null)
    {
        try {
            $this->client->putObject([
                'Bucket' => $this->bucket,
                'Key'    => $path,
                'Body'   => $contents,
            ]);
            return true;
        } catch (\Exception $e) {
            \think\facade\Log::error('COS putObject 失败: '.$e->getMessage());
            return false;
        }
    }

    public function writeStream($path, $resource, Config $config = null)
    {
        try {
            $contents = stream_get_contents($resource);
            return $this->write($path, $contents, $config);
        } catch (\Exception $e) {
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
            // COS不支持直接重命名，需要复制后删除
            $contents = $this->read($path);
            if ($contents === false) {
                return false;
            }
            
            if (!$this->write($newpath, $contents['contents'])) {
                return false;
            }
            
            return $this->delete($path);
        } catch (\Exception $e) {
            return false;
        }
    }

    public function copy($path, $newpath)
    {
        try {
            $contents = $this->read($path);
            if ($contents === false) {
                return false;
            }
            
            return $this->write($newpath, $contents['contents']);
        } catch (\Exception $e) {
            return false;
        }
    }

    public function delete($path)
    {
        try {
            $this->client->deleteObject([
                'Bucket' => $this->bucket,
                'Key'    => $path,
            ]);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function deleteDir($dirname)
    {
        try {
            $contents = $this->listContents($dirname, true);
            foreach ($contents as $item) {
                if ($item['type'] === 'file') {
                    $this->delete($item['path']);
                }
            }
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function createDir($dirname, Config $config = null)
    {
        try {
            // COS中目录是通过对象名以/结尾来表示的
            $this->write(rtrim($dirname, '/') . '/', '');
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function setVisibility($path, $visibility)
    {
        // COS不支持动态设置可见性
        return true;
    }

    public function has($path)
    {
        try {
            $this->client->headObject([
                'Bucket' => $this->bucket,
                'Key'    => $path,
            ]);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function read($path)
    {
        try {
            $resp = $this->client->getObject([
                'Bucket' => $this->bucket,
                'Key'    => $path,
            ]);
            return ['contents' => (string) $resp['Body']];
        } catch (\Exception $e) {
            return false;
        }
    }

    public function readStream($path)
    {
        try {
            $contents = $this->read($path);
            if ($contents === false) {
                return false;
            }
            
            $stream = fopen('php://temp', 'r+');
            fwrite($stream, $contents['contents']);
            rewind($stream);
            return ['stream' => $stream];
        } catch (\Exception $e) {
            return false;
        }
    }

    public function listContents($directory = '', $recursive = false)
    {
        try {
            $result = $this->client->listObjects([
                'Bucket'    => $this->bucket,
                'Prefix'    => $directory,
                'Delimiter' => $recursive ? '' : '/',
            ]);

            $contents = [];

            foreach (($result['Contents'] ?? []) as $object) {
                $contents[] = [
                    'type'      => 'file',
                    'path'      => $object['Key'],
                    'size'      => $object['Size'],
                    'timestamp' => strtotime($object['LastModified']),
                ];
            }

            foreach (($result['CommonPrefixes'] ?? []) as $prefix) {
                $contents[] = [
                    'type' => 'dir',
                    'path' => rtrim($prefix['Prefix'], '/'),
                ];
            }

            return $contents;
        } catch (\Exception $e) {
            return [];
        }
    }

    public function getMetadata($path)
    {
        try {
            $resp = $this->client->headObject([
                'Bucket' => $this->bucket,
                'Key'    => $path,
            ]);

            return [
                'type'       => 'file',
                'path'       => $path,
                'size'       => $resp['ContentLength'] ?? 0,
                'timestamp'  => isset($resp['LastModified']) ? strtotime($resp['LastModified']) : time(),
                'visibility' => 'public',
            ];
        } catch (\Exception $e) {
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
            $resp = $this->client->headObject([
                'Bucket' => $this->bucket,
                'Key'    => $path,
            ]);
            // qcloud SDK 返回的 ContentType 字段包含 MIME 类型
            $mimeType = $resp['ContentType'] ?? 'application/octet-stream';
            return ['mimetype' => $mimeType];
        } catch (\Exception $e) {
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
        // COS默认所有文件都是公开的
        return ['visibility' => 'public'];
    }

    public function getUrl($path)
    {
        if ($this->url) {
            return rtrim($this->url, '/') . '/' . ltrim($path, '/');
        }
        return "https://{$this->bucket}.cos.{$this->region}.myqcloud.com/{$path}";
    }
} 