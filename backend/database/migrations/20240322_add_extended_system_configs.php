<?php

use think\migration\Migrator;
use think\migration\db\Column;

class AddExtendedSystemConfigs extends Migrator
{
    public function change()
    {
        // 添加邮箱配置
        $this->execute("INSERT INTO system_config (type, name, driver, config, status, is_default, create_time, update_time) VALUES 
            ('email', '默认SMTP邮箱', 'smtp', '{\"host\":\"smtp.163.com\",\"port\":465,\"username\":\"\",\"password\":\"\",\"encryption\":\"ssl\",\"from_address\":\"\",\"from_name\":\"\"}', 1, 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
            ('email', '阿里云邮件推送', 'aliyun', '{\"access_key_id\":\"\",\"access_key_secret\":\"\",\"account_name\":\"\",\"from_alias\":\"\"}', 1, 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
            ('email', '腾讯云邮件推送', 'tencent', '{\"secret_id\":\"\",\"secret_key\":\"\",\"from_email_address\":\"\",\"from_name\":\"\"}', 1, 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
        ");

        // 添加对象存储配置
        $this->execute("INSERT INTO system_config (type, name, driver, config, status, is_default, create_time, update_time) VALUES 
            ('storage', '阿里云OSS', 'aliyun', '{\"access_key_id\":\"\",\"access_key_secret\":\"\",\"endpoint\":\"\",\"bucket\":\"\",\"domain\":\"\"}', 1, 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
            ('storage', '腾讯云COS', 'tencent', '{\"secret_id\":\"\",\"secret_key\":\"\",\"region\":\"\",\"bucket\":\"\",\"domain\":\"\"}', 1, 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
            ('storage', '华为云OBS', 'huawei', '{\"access_key_id\":\"\",\"secret_access_key\":\"\",\"endpoint\":\"\",\"bucket\":\"\",\"domain\":\"\"}', 1, 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
            ('storage', '七牛云存储', 'qiniu', '{\"access_key\":\"\",\"secret_key\":\"\",\"bucket\":\"\",\"domain\":\"\"}', 1, 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
            ('storage', '本地存储', 'local', '{\"root_path\":\"uploads\",\"url_prefix\":\"/uploads\"}', 1, 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
        ");

        // 添加SSO单点登录配置
        $this->execute("INSERT INTO system_config (type, name, driver, config, status, is_default, create_time, update_time) VALUES 
            ('sso', 'CAS单点登录', 'cas', '{\"server_url\":\"\",\"service_url\":\"\",\"version\":\"2.0\"}', 1, 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
            ('sso', 'OAuth2.0', 'oauth2', '{\"client_id\":\"\",\"client_secret\":\"\",\"authorize_url\":\"\",\"token_url\":\"\",\"userinfo_url\":\"\"}', 1, 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
            ('sso', 'SAML2.0', 'saml2', '{\"idp_entity_id\":\"\",\"idp_sso_url\":\"\",\"idp_cert\":\"\",\"sp_entity_id\":\"\",\"sp_acs_url\":\"\"}', 1, 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
            ('sso', 'LDAP认证', 'ldap', '{\"host\":\"\",\"port\":389,\"base_dn\":\"\",\"bind_dn\":\"\",\"bind_password\":\"\",\"user_filter\":\"\"}', 1, 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
        ");

        // 添加Redis缓存配置
        $this->execute("INSERT INTO system_config (type, name, driver, config, status, is_default, create_time, update_time) VALUES 
            ('redis', '本地Redis', 'local', '{\"host\":\"127.0.0.1\",\"port\":6379,\"password\":\"\",\"database\":0,\"timeout\":0}', 1, 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
            ('redis', '阿里云Redis', 'aliyun', '{\"host\":\"\",\"port\":6379,\"password\":\"\",\"database\":0,\"timeout\":0}', 1, 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
            ('redis', '腾讯云Redis', 'tencent', '{\"host\":\"\",\"port\":6379,\"password\":\"\",\"database\":0,\"timeout\":0}', 1, 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
            ('redis', 'Redis集群', 'cluster', '{\"nodes\":[\"127.0.0.1:6379\"],\"password\":\"\",\"timeout\":0}', 1, 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
        ");

        // 添加队列配置
        $this->execute("INSERT INTO system_config (type, name, driver, config, status, is_default, create_time, update_time) VALUES 
            ('queue', 'Redis队列', 'redis', '{\"host\":\"127.0.0.1\",\"port\":6379,\"password\":\"\",\"database\":1,\"queue_name\":\"default\"}', 1, 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
            ('queue', '数据库队列', 'database', '{\"table\":\"jobs\",\"connection\":\"mysql\"}', 1, 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
            ('queue', '阿里云MNS', 'aliyun_mns', '{\"access_key_id\":\"\",\"access_key_secret\":\"\",\"endpoint\":\"\",\"queue_name\":\"\"}', 1, 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
            ('queue', '腾讯云CMQ', 'tencent_cmq', '{\"secret_id\":\"\",\"secret_key\":\"\",\"region\":\"\",\"queue_name\":\"\"}', 1, 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
        ");

        // 添加日志配置
        $this->execute("INSERT INTO system_config (type, name, driver, config, status, is_default, create_time, update_time) VALUES 
            ('log', '本地文件日志', 'file', '{\"path\":\"runtime/log\",\"level\":\"info\",\"max_files\":30}', 1, 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
            ('log', '阿里云SLS', 'aliyun_sls', '{\"access_key_id\":\"\",\"access_key_secret\":\"\",\"endpoint\":\"\",\"project\":\"\",\"logstore\":\"\"}', 1, 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
            ('log', '腾讯云CLS', 'tencent_cls', '{\"secret_id\":\"\",\"secret_key\":\"\",\"region\":\"\",\"topic_id\":\"\"}', 1, 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
            ('log', 'Elasticsearch', 'elasticsearch', '{\"hosts\":[\"localhost:9200\"],\"index\":\"logs\",\"type\":\"log\"}', 1, 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
        ");

        // 添加监控配置
        $this->execute("INSERT INTO system_config (type, name, driver, config, status, is_default, create_time, update_time) VALUES 
            ('monitor', 'Prometheus监控', 'prometheus', '{\"pushgateway_url\":\"\",\"job_name\":\"edumatrix\"}', 1, 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
            ('monitor', '阿里云ARMS', 'aliyun_arms', '{\"access_key_id\":\"\",\"access_key_secret\":\"\",\"pid\":\"\"}', 1, 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
            ('monitor', '腾讯云APM', 'tencent_apm', '{\"secret_id\":\"\",\"secret_key\":\"\",\"instance_id\":\"\"}', 1, 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
            ('monitor', '自定义监控', 'custom', '{\"api_url\":\"\",\"api_key\":\"\",\"metrics\":[\"cpu\",\"memory\",\"disk\"]}', 1, 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
        ");

        // 添加安全配置
        $this->execute("INSERT INTO system_config (type, name, driver, config, status, is_default, create_time, update_time) VALUES 
            ('security', 'JWT配置', 'jwt', '{\"secret\":\"\",\"expire_time\":7200,\"refresh_expire_time\":604800}', 1, 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
            ('security', 'CORS配置', 'cors', '{\"allowed_origins\":[\"*\"],\"allowed_methods\":[\"GET\",\"POST\",\"PUT\",\"DELETE\"],\"allowed_headers\":[\"*\"],\"expose_headers\":[\"*\"],\"max_age\":86400}', 1, 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
            ('security', '限流配置', 'rate_limit', '{\"max_requests\":1000,\"window_time\":3600,\"storage\":\"redis\"}', 1, 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
            ('security', 'WAF配置', 'waf', '{\"enabled\":true,\"rules\":[\"sql_injection\",\"xss\",\"csrf\"],\"whitelist\":[\"127.0.0.1\"]}', 1, 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
        ");

        // 添加第三方服务配置
        $this->execute("INSERT INTO system_config (type, name, driver, config, status, is_default, create_time, update_time) VALUES 
            ('third_party', '微信开放平台', 'wechat_open', '{\"app_id\":\"\",\"app_secret\":\"\",\"token\":\"\",\"encoding_aes_key\":\"\"}', 1, 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
            ('third_party', 'QQ互联', 'qq_connect', '{\"app_id\":\"\",\"app_key\":\"\",\"redirect_uri\":\"\"}', 1, 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
            ('third_party', '微博开放平台', 'weibo', '{\"app_key\":\"\",\"app_secret\":\"\",\"redirect_uri\":\"\"}', 1, 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
            ('third_party', 'GitHub OAuth', 'github', '{\"client_id\":\"\",\"client_secret\":\"\",\"redirect_uri\":\"\"}', 1, 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
            ('third_party', '高德地图', 'amap', '{\"key\":\"\",\"secret\":\"\"}', 1, 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
            ('third_party', '腾讯地图', 'tencent_map', '{\"key\":\"\",\"secret\":\"\"}', 1, 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
            ('third_party', '百度AI', 'baidu_ai', '{\"app_id\":\"\",\"api_key\":\"\",\"secret_key\":\"\"}', 1, 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
            ('third_party', '阿里云AI', 'aliyun_ai', '{\"access_key_id\":\"\",\"access_key_secret\":\"\",\"endpoint\":\"\"}', 1, 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
        ");
    }
} 