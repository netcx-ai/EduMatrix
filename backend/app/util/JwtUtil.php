<?php
declare (strict_types = 1);

namespace app\util;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use think\facade\Config;

class JwtUtil
{
    private static $key = 'edumatrix_secret_key';
    private static $expire = 7200; // token过期时间，单位秒

    /**
     * 生成token
     * @param array $data 需要保存的数据
     * @return string
     */
    public static function createToken(array $data): string
    {
        $time = time();
        $token = [
            'iat'  => $time,         // 签发时间
            'exp'  => $time + self::$expire, // 过期时间
            'data' => $data,         // 保存的数据
        ];
        return JWT::encode($token, self::$key, 'HS256');
    }

    /**
     * 验证token
     * @param string $token
     * @return array|false
     */
    public static function verifyToken(string $token)
    {
        try {
            $decoded = JWT::decode($token, new Key(self::$key, 'HS256'));
            return (array)$decoded->data;
        } catch (\Exception $e) {
            return false;
        }
    }
} 