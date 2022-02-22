<?php
/**
 * This file is part of webman.
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the MIT-LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @author    walkor<walkor@workerman.net>
 * @copyright walkor<walkor@workerman.net>
 * @link      http://www.workerman.net/
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 */

use Webman\Route;

// 域名和应用绑定关系
$bind = config('plugin.webman.domain.app.bind', []);
// 是否开启简写url
$short = config('plugin.webman.domain.app.short_url', []);

$dir_iterator = new \RecursiveDirectoryIterator(app_path());
$iterator = new \RecursiveIteratorIterator($dir_iterator);
foreach ($iterator as $file) {
    // 忽略目录和非php文件
    if (is_dir($file) || $file->getExtension() != 'php') {
        continue;
    }

    $file_path = str_replace('\\', '/',$file->getPathname());
    // 文件路径里不带controller的文件忽略
    if (strpos($file_path, 'controller') === false) {
        continue;
    }

    // 根据文件路径计算uri
    $uri_path = strtolower(str_replace('controller/', '',substr(substr($file_path, strlen(app_path())), 0, -4)));
    // 根据文件路径是被类名
    $class_name = str_replace('/', '\\',substr(substr($file_path, strlen(base_path())), 0, -4));

    if (!class_exists($class_name)) {
        echo "Class $class_name not found, skip route for it\n";
        continue;
    }

    // 通过反射找到这个类的所有共有方法作为action
    $class = new ReflectionClass($class_name);
    $methods = $class->getMethods(ReflectionMethod::IS_PUBLIC);

    $route = function ($uri, $cb) use ($bind, $short) {
        // 简写url
        if ($short) {
            $app = strstr(ltrim($uri, '/'), '/', true);
            $app = $app ?: '';
            if ($app) {
                if (array_search($app, $bind)) {
                    $uri = strstr(ltrim($uri, '/'), '/');
                }
            }
        }
        Route::any($uri, $cb);
        Route::any($uri.'/', $cb);
    };

    // 设置路由
    foreach ($methods as $item) {
        $action = $item->name;
        if (in_array($action, ['__construct', '__destruct'])) {
            continue;
        }
        // action为index时uri里末尾/index可以省略
        if ($action === 'index') {
            // controller也为index时可以uri里可以省略/index/index
            if (substr($uri_path, -6) === '/index') {
                $route(substr($uri_path, 0, -6), [$class_name, $action]);
            }
            $route($uri_path, [$class_name, $action]);
        }
        $route($uri_path.'/'.$action, [$class_name, $action]);
    }

}