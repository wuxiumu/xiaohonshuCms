<?php
/**
 * 口令请用环境变量 CMS_ADMIN_PASS，勿提交明文。
 */
$pass = getenv('CMS_ADMIN_PASS') ?: '';
$user = getenv('CMS_ADMIN_USER') ?: 'admin';
return [
    'users' => [
        $user => $pass,
    ],
    'site_name' => getenv('CMS_SITE_NAME') ?: 'My 小红书',
    'post_dir' => __DIR__ . '/data/posts',
];
