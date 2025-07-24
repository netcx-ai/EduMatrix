<?php
namespace app\service\ai;

use GuzzleHttp\Client;

class DeepSeekProvider implements AiProviderInterface
{
    protected string $apiKey;
    protected string $baseUri = 'https://api.deepseek.com/';

    public function __construct()
    {
        // 从 env 读取 AI 组内的密钥
        $this->apiKey = env('AI.DEEPSEEK_API_KEY');
        if (!$this->apiKey) {
            throw new \RuntimeException('DeepSeek API Key 未配置 (.env 中 [AI] DEEPSEEK_API_KEY)');
        }
    }

    public function generate(string $toolCode, array $params): string
    {
        // 简化：systemPrompt & userPrompt 拼接
        $systemPrompt = $params['system_prompt'] ?? '你是教育内容生成助手，请输出 JSON 或 Markdown 格式的教学内容。';
        $topic        = $params['topic'] ?? ($params['message'] ?? '未知主题');
        $userPrompt   = $params['user_prompt'] ?? "请针对主题《{$topic}》生成教学内容。";

        $client = new Client([
            'base_uri' => $this->baseUri,
            'timeout'  => 90,
            'verify'   => false, // 如果需要跳过 SSL
        ]);

        $response = $client->post('chat/completions', [
            'headers' => [
                'Authorization' => 'Bearer '.$this->apiKey,
                'Content-Type'  => 'application/json',
            ],
            'json' => [
                'model' => 'deepseek-chat',
                'stream'=> false,
                'messages' => [
                    ['role'=>'system', 'content'=>$systemPrompt],
                    ['role'=>'user',   'content'=>$userPrompt],
                ],
            ],
        ]);

        $data = json_decode($response->getBody()->getContents(), true);
        return $data['choices'][0]['message']['content'] ?? '';
    }
} 