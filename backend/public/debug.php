<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<pre>";
echo "=== 调试 is_cli 问题 ===\n";
echo "PHP_SAPI: " . PHP_SAPI . "\n";
echo "PHP版本: " . PHP_VERSION . "\n";

// 逐步加载
try {
    echo "\n1. 加载 autoload...\n";
    require __DIR__ . '/../vendor/autoload.php';
    echo "✓ autoload 加载成功\n";
    
    echo "\n2. 创建 App 实例...\n";
    $app = new \think\App();
    echo "✓ App 实例创建成功\n";
    
    echo "\n3. 初始化应用...\n";
    $app->initialize();
    echo "✓ 应用初始化成功\n";
    
    echo "\n4. 获取配置...\n";
    $config = $app->config->get('app');
    echo "✓ 配置获取成功\n";
    echo "app 配置项: " . print_r(array_keys($config), true) . "\n";
    
    echo "\n5. 检查 is_cli 配置...\n";
    if (isset($config['is_cli'])) {
        echo "✓ is_cli 配置存在: " . var_export($config['is_cli'], true) . "\n";
    } else {
        echo "✗ is_cli 配置不存在\n";
    }
    
} catch (\Throwable $e) {
    echo "\n✗ 错误: " . $e->getMessage() . "\n";
    echo "文件: " . $e->getFile() . "\n";
    echo "行号: " . $e->getLine() . "\n";
    echo "\n堆栈:\n" . $e->getTraceAsString() . "\n";
}

echo "</pre>"; 