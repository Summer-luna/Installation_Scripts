$proxy_URL = 'https://vtiger.testuni.net';

// Update $_SERVER for reverse proxy with public domain
if (trim($proxy_URL)) {
    $_SERVER['HTTP_PORT'] = parse_url(trim($proxy_URL), PHP_URL_PORT);
    $_SERVER['HTTP_HOST'] = parse_url(trim($proxy_URL), PHP_URL_HOST);
    if (preg_match('/^https/i', trim($proxy_URL))) {
        $_SERVER['HTTPS'] = 'on';
        $_SERVER['HTTP_HOST'] .= $_SERVER['HTTP_PORT'] && $_SERVER['HTTP_PORT'] != 443 ? ':'.$_SERVER['HTTP_PORT'] : '';
    } else {
        $_SERVER['HTTP_HOST'] .= $_SERVER['HTTP_PORT'] && $_SERVER['HTTP_PORT'] != 80 ? ':'.$_SERVER['HTTP_PORT'] : '';
    }
}

// Update $site_URL using VT_SITE_URL environment variable
$site_URL = 'http'.(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on' ? 's' : '').'://'.$_SERVER['HTTP_HOST'].'/';

// Store $site_URL on /tmp for system services
if ($_SERVER['HTTP_HOST']) {
    $site_URL_file = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'vtiger_site_URL';
    if (!file_exists($site_URL_file) || filemtime($site_URL_file) + 3600 < time()) {
        file_put_contents($site_URL_file, $site_URL);
        $port = parse_url('http://'.$_SERVER['HTTP_HOST'], PHP_URL_PORT);
        if ($_SERVER['HTTPS'] === 'on' && $port != 443) {
            file_put_contents(sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'https_localhost_proxy', 'tcp-listen:'.$port.',reuseaddr,fork tcp:localhost:443');
        } elseif ($port != 80) {
            file_put_contents(sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'http_localhost_proxy', 'tcp-listen:'.$port.',reuseaddr,fork tcp:localhost:80');
        }
    }
}