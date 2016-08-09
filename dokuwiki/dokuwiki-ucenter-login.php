<?php

//基础config
define('CLIENT_ID', 'CLIENT_ID');//应用ID，需要申请
define('CLIENT_SECRET', 'CLIENT_SECRET');//应用密钥，需要申请
define('REDIRECT_URI', 'http://localhost/dokuwiki/doku.php?do=login');//回调地址
define('UCENTER_HOME', 'http://ucenter.szjlxh.com');//用户中心地址
define('CLIENT_HOME', 'http://localhost/dokuwiki/doku.php');

define('UCENTER_OAUTH', UCENTER_HOME . '/oauth');
define('UCENTER_API', UCENTER_HOME . '/api');

define('SUCCESS', 0);

global $ACT;
/* @var INPUT $input */
global $INPUT;
/* @var DokuWiki_Auth_Plugin $auth */
global $auth;
global $AUTH_ACL;


switch ($ACT) {
    case 'login' :
        // fetch access token
        if (isset($_GET['code'])) {
            ucenter_oauth();
        }

        // fetch auth code
        if (empty($_SESSION['access_token'])) {
            header("Location: " . ucenter_oauth_url());
            exit;
        } else { // fetch authenticated user data and log in local application
            $url = UCENTER_API . '/user/?access_token=' . $_SESSION['access_token'];
            $data = json_decode(ucenter_curl($url), true);
            if (SUCCESS !== $data['code']) {
                exit('授权失败');
            }

            localLogin($data['data']);
        }

        act_redirect('', '');
        break;
    case 'logout':
        unset($_SESSION['access_token']);
        break;
    case 'register' :
        header("Location: " . ucenter_oauth_url());
        exit;
        break;
}

if ($ACT === 'login') {

} else if ($ACT === 'logout') {
}

function ucenter_oauth()
{
    // 获取access_token
    $url = UCENTER_API . '/oauth/accessToken';
    $data = array('client_id' => CLIENT_ID,
        'client_secret' => CLIENT_SECRET,
        'grant_type' => 'authorization_code',
        'redirect_uri' => REDIRECT_URI,
        'code' => $_GET['code']);
    $data = json_decode(ucenter_curl($url, 'POST', $data), true);
    if (SUCCESS !== $data['code']) {
        exit('授权失败');
    }
    $access_token = $data['data']['access_token'];
    $_SESSION['access_token'] = $access_token;
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
function ucenter_oauth_url()
{
    return UCENTER_OAUTH . '/authorize?client_id=' . CLIENT_ID . '&response_type=code&redirect_uri=' . urlencode(REDIRECT_URI);
}

function localLogin($data)
{
    /* @var DokuWiki_Auth_Plugin $auth */
    global $auth;
    global $INPUT;
    global $AUTH_ACL;

    if ($auth->getUserData($data['user_id'], 'no-password') !== false ||
        $auth->createUser($data['user_id'], 'no-password', $data['username'], $data['email'])
    ) {
        auth_login_wrapper([
            'user' => $data['user_id'],
            'password' => 'no-password',
            'sticky' => false,
            'silent' => $INPUT->bool('http_credentials')
        ]);
        $AUTH_ACL = auth_loadACL();
        return true;
    }
    return false;
}