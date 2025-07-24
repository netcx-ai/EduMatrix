<?php
namespace app\service\ai;

interface AiProviderInterface
{
    /**
     * 生成 AI 内容
     * @param string $toolCode   工具代码
     * @param array  $params     动态参数
     * @return string            返回生成的文本/Markdown/JSON
     */
    public function generate(string $toolCode, array $params): string;
} 