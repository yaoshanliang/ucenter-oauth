<?php

// 基础config
define('CLIENT_ID', '');// 应用ID，需要申请
define('CLIENT_SECRET', '');// 应用密钥，需要申请
define('REDIRECT_URI', '');// 回调地址
define('UCENTER_HOME', 'http://ucenter.szjlxh.com');// 用户中心地址

define('UCENTER_OAUTH', UCENTER_HOME . '/oauth');
define('UCENTER_API', UCENTER_HOME . '/api');

define('SUCCESS', 0);

session_start();

if (isset($_GET['code'])){
    ucenter_oauth();
}

if (empty($_SESSION['access_token'])) {
    header("Location: " . ucenter_oauth_url());
    exit;
}

// ucenter授权
function ucenter_oauth() {
    // 获取access_token
    $url = UCENTER_API . '/oauth/accessToken';
    $data = array('client_id' => CLIENT_ID,
        'client_secret' => CLIENT_SECRET,
        'grant_type' => 'authorization_code',
        'redirect_uri' => REDIRECT_URI,
        'code' => $_GET['code']);
    $data = json_decode(ucenter_curl($url, 'POST', $data), true);
    if(SUCCESS !== $data['code']) {
        exit('授权失败');
    }
    $access_token = $data['data']['access_token'];
    $_SESSION['access_token'] = $access_token;

    // 根据access_token获取用户信息
    $url = UCENTER_API . '/user/?access_token=' . $access_token;
    $data = json_decode(ucenter_curl($url), true);
    if(SUCCESS !== $data['code']) {
        exit('授权失败');
    }
    $username = $data['data']['username'];
    $user_id = $data['data']['user_id'];

    header('Location: ' . '/');
    exit;
}

// curl请求
function ucenter_curl($url, $method = 'GET', $data = array())
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    if ($method == 'POST') {
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    }
    $output = curl_exec($ch);
    curl_close($ch);

    return $output;
}

// 生成授权url
function ucenter_oauth_url() {
    return UCENTER_OAUTH . '/authorize?client_id=' . CLIENT_ID . '&response_type=code&redirect_uri=' . urlencode(REDIRECT_URI);
}

// echo '点此进入授权登录<a href=' . ucenter_oauth_url() . '>' . ucenter_oauth_url() . '</a>';
