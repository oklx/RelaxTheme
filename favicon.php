<?php
/**
 * Favicon 代理 + 本地文件缓存
 *
 * 用法：favicon.php?d=example.com
 *       favicon.php?d=example.com&debug=1  (调试模式)
 * 缓存目录：assets/favicons/
 * 缓存有效期：180 天
 */

$domain = isset($_GET['d']) ? trim($_GET['d']) : '';
$debug = !empty($_GET['debug']);

// 校验 domain 合法性
if ($domain === '' || !preg_match('/^([a-zA-Z0-9]([a-zA-Z0-9\-]{0,61}[a-zA-Z0-9])?\.)+[a-zA-Z]{2,}$/', $domain)) {
    http_response_code(400);
    exit('Invalid domain');
}

$cacheDir = __DIR__ . '/assets/favicons';
$cacheFile = $cacheDir . '/' . md5($domain);
$cacheTTL = 180 * 24 * 3600; // 180 天

// 确保缓存目录存在
if (!is_dir($cacheDir)) {
    @mkdir($cacheDir, 0755, true);
}

// 命中缓存且未过期 → 直接输出
// 缓存文件可能带 .ico / .png / .jpg 等后缀
$cached = findCacheFile($cacheDir, md5($domain));
if ($cached && (time() - filemtime($cached)) < $cacheTTL) {
    $mime = detectMimeFromFile($cached);
    header('Content-Type: ' . $mime);
    header('Cache-Control: public, max-age=15552000');
    readfile($cached);
    exit;
}

// 如果有过期缓存文件，先删除
if ($cached) {
    @unlink($cached);
}

// 未命中缓存 → 按顺序尝试多个源
// 优先使用返回 PNG 格式的 API，直接抓 favicon.ico 放最后
$sources = array(
    array('url' => 'https://api.iowen.cn/favicon/' . $domain . '.png', 'name' => 'iowen'),
    array('url' => 'https://favicon.im/' . $domain . '?larger=true', 'name' => 'favicon.im'),
    array('url' => 'https://' . $domain . '/favicon.ico', 'name' => 'direct'),
);

$imageData = false;
$imageExt = 'png';
$debugLog = array();

foreach ($sources as $src) {
    $data = fetchUrl($src['url']);

    if ($debug) {
        $debugLog[] = $src['name'] . ' (' . $src['url'] . '): ' .
            ($data === false ? 'FETCH FAILED' : strlen($data) . ' bytes, valid=' . (isValidImage($data) ? 'yes' : 'no'));
    }

    if ($data !== false && strlen($data) > 100 && isValidImage($data)) {
        $imageData = $data;
        $imageExt = detectExtFromData($data);
        break;
    }
}

// 调试模式 → 输出日志
if ($debug) {
    header('Content-Type: text/plain; charset=utf-8');
    echo "Domain: {$domain}\n";
    echo "Cache dir: {$cacheDir}\n";
    echo "Cache dir writable: " . (is_writable($cacheDir) ? 'yes' : 'no') . "\n";
    echo "cURL available: " . (function_exists('curl_init') ? 'yes' : 'no') . "\n";
    echo "allow_url_fopen: " . (ini_get('allow_url_fopen') ? 'yes' : 'no') . "\n";
    echo "\nSources:\n";
    foreach ($debugLog as $line) {
        echo "  - {$line}\n";
    }
    echo "\nResult: " . ($imageData !== false ? 'OK (' . strlen($imageData) . ' bytes, ext=' . $imageExt . ')' : 'ALL FAILED');
    exit;
}

if ($imageData === false) {
    http_response_code(404);
    exit;
}

// 写入缓存（带正确后缀）
$savePath = $cacheFile . '.' . $imageExt;
@file_put_contents($savePath, $imageData);

// 输出图片
$mime = detectMimeFromExt($imageExt);
header('Content-Type: ' . $mime);
header('Cache-Control: public, max-age=15552000');
echo $imageData;
exit;


// ========== 辅助函数 ==========

/**
 * 查找缓存文件（支持多种后缀）
 */
function findCacheFile($dir, $hash) {
    $exts = array('png', 'ico', 'jpg', 'gif', 'webp', 'svg');
    foreach ($exts as $ext) {
        $path = $dir . '/' . $hash . '.' . $ext;
        if (file_exists($path)) return $path;
    }
    return false;
}

/**
 * 使用 cURL 抓取 URL，超时 5 秒
 */
function fetchUrl($url) {
    if (function_exists('curl_init')) {
        $ch = curl_init();
        curl_setopt_array($ch, array(
            CURLOPT_URL            => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 5,
            CURLOPT_CONNECTTIMEOUT => 3,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS      => 3,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_USERAGENT      => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
            CURLOPT_HTTPHEADER     => array('Accept: image/*,*/*'),
        ));

        $data = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($data === false || $httpCode < 200 || $httpCode >= 400) {
            return false;
        }
        return $data;
    }

    // fallback: file_get_contents
    if (!ini_get('allow_url_fopen')) {
        return false;
    }

    $ctx = stream_context_create(array(
        'http' => array(
            'timeout' => 5,
            'follow_location' => true,
            'max_redirects' => 3,
            'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
            'header' => "Accept: image/*,*/*\r\n",
        ),
        'ssl' => array(
            'verify_peer' => false,
            'verify_peer_name' => false,
        ),
    ));
    $data = @file_get_contents($url, false, $ctx);
    return $data !== false ? $data : false;
}

/**
 * 校验是否为有效图片数据（排除 HTML 响应）
 */
function isValidImage($data) {
    if (strlen($data) < 4) {
        return false;
    }

    // 先排除 HTML/text 响应
    $trimmed = ltrim($data);
    $lower = strtolower(substr($trimmed, 0, 50));
    if (strpos($lower, '<!doctype') !== false ||
        strpos($lower, '<html') !== false ||
        strpos($lower, '<head') !== false ||
        strpos($lower, '<!-') !== false ||
        strpos($lower, '{"') === 0 ||
        strpos($lower, 'not found') !== false ||
        strpos($lower, 'error') === 0 ||
        strpos($lower, '403') === 0 ||
        strpos($lower, '404') === 0) {
        return false;
    }

    $header = substr($data, 0, 12);

    // PNG
    if (substr($header, 0, 4) === "\x89PNG") return true;
    // JPEG
    if (substr($header, 0, 3) === "\xFF\xD8\xFF") return true;
    // GIF
    if (substr($header, 0, 6) === "GIF87a" || substr($header, 0, 6) === "GIF89a") return true;
    // ICO
    if (substr($header, 0, 4) === "\x00\x00\x01\x00") return true;
    // CUR (cursor, similar to ICO)
    if (substr($header, 0, 4) === "\x00\x00\x02\x00") return true;
    // BMP
    if (substr($header, 0, 2) === "BM") return true;
    // WEBP
    if (substr($header, 0, 4) === "RIFF" && strlen($data) > 11 && substr($data, 8, 4) === "WEBP") return true;
    // SVG
    if (substr($trimmed, 0, 4) === '<svg') return true;

    return false;
}

/**
 * 从图片数据检测文件后缀
 */
function detectExtFromData($data) {
    $header = substr($data, 0, 12);

    if (substr($header, 0, 4) === "\x89PNG") return 'png';
    if (substr($header, 0, 3) === "\xFF\xD8\xFF") return 'jpg';
    if (substr($header, 0, 3) === "GIF") return 'gif';
    if (substr($header, 0, 4) === "\x00\x00\x01\x00") return 'ico';
    if (substr($header, 0, 4) === "\x00\x00\x02\x00") return 'ico';
    if (substr($header, 0, 2) === "BM") return 'png'; // treat as png
    if (substr($header, 0, 4) === "RIFF" && strlen($data) > 11 && substr($data, 8, 4) === "WEBP") return 'webp';
    if (strpos(ltrim($data), '<svg') === 0) return 'svg';

    return 'png';
}

/**
 * 从文件后缀推断 MIME
 */
function detectMimeFromExt($ext) {
    $map = array(
        'png'  => 'image/png',
        'jpg'  => 'image/jpeg',
        'gif'  => 'image/gif',
        'ico'  => 'image/x-icon',
        'webp' => 'image/webp',
        'svg'  => 'image/svg+xml',
    );
    return isset($map[$ext]) ? $map[$ext] : 'image/png';
}

/**
 * 从缓存文件路径推断 MIME
 */
function detectMimeFromFile($path) {
    $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
    return detectMimeFromExt($ext);
}
