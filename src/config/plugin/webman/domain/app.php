<?php
return [
    'enable' => true,
    // 多应用绑定关系
    'bind' => [
        'abc.com' => '', // 不属于任何应用
        'a.abc.com'  => 'admin', // 绑定到admin应用
        'blog.abc.com'  => 'blog', // 绑定到blog应用
    ],
    // 绑定关系，域名，应用的验证逻辑，返回true时认为符合绑定关系，反之不符合返回404
    'check' => function ($bind, $domain, $app) {
        return isset($bind[$domain]) && $bind[$domain] === $app;
    }
];