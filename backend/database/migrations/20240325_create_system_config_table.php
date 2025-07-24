<?php

use think\migration\Migrator;
use think\migration\db\Column;

class CreateSystemConfigTable extends Migrator
{
    public function change()
    {
        $table = $this->table('system_config', ['engine' => 'InnoDB', 'collation' => 'utf8mb4_unicode_ci']);
        $table
            ->addColumn('type', 'string', ['limit' => 30, 'null' => false, 'comment' => '配置类型：sms, payment 等'])
            ->addColumn('name', 'string', ['limit' => 50, 'null' => false, 'comment' => '配置名称'])
            ->addColumn('driver', 'string', ['limit' => 20, 'null' => false, 'comment' => '服务商：aliyun, tencent, huawei, alipay, wechat 等'])
            ->addColumn('config', 'json', ['null' => false, 'comment' => '配置信息（JSON格式）'])
            ->addColumn('status', 'boolean', ['signed' => false, 'null' => false, 'default' => 1, 'comment' => '状态：0禁用，1启用'])
            ->addColumn('is_default', 'boolean', ['signed' => false, 'null' => false, 'default' => 0, 'comment' => '是否默认配置'])
            ->addColumn('remark', 'string', ['limit' => 255, 'null' => true, 'comment' => '备注说明'])
            ->addColumn('create_time', 'integer', ['signed' => false, 'null' => false, 'default' => 0, 'comment' => '创建时间'])
            ->addColumn('update_time', 'integer', ['signed' => false, 'null' => false, 'default' => 0, 'comment' => '更新时间'])
            ->addIndex(['type', 'name'], ['unique' => true])
            ->addIndex(['type', 'driver'])
            ->addIndex(['status'])
            ->create();

        // 插入默认短信配置
        $this->execute("INSERT INTO system_config (type, name, driver, config, status, is_default, create_time, update_time) VALUES 
            ('sms', '默认阿里云短信', 'aliyun', '{\"access_key_id\":\"\",\"access_key_secret\":\"\",\"sign_name\":\"\",\"template_code\":\"\"}', 1, 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
            ('sms', '默认腾讯云短信', 'tencent', '{\"secret_id\":\"\",\"secret_key\":\"\",\"sdk_app_id\":\"\",\"sign_name\":\"\",\"template_id\":\"\"}', 1, 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
            ('sms', '默认华为云短信', 'huawei', '{\"app_key\":\"\",\"app_secret\":\"\",\"sender\":\"\",\"template_id\":\"\"}', 1, 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
        ");

        // 插入默认支付配置
        $this->execute("INSERT INTO system_config (type, name, driver, config, status, is_default, create_time, update_time) VALUES 
            ('payment', '默认支付宝', 'alipay', '{\"app_id\":\"\",\"private_key\":\"\",\"public_key\":\"\",\"notify_url\":\"\",\"return_url\":\"\"}', 1, 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
            ('payment', '默认微信支付', 'wechat', '{\"app_id\":\"\",\"mch_id\":\"\",\"key\":\"\",\"cert_path\":\"\",\"key_path\":\"\",\"notify_url\":\"\"}', 1, 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
        ");
    }
} 