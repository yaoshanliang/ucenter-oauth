<?php
require_once('wp-load.php');

//基础config
define('CLIENT_ID', 'wordpress');//应用ID，需要申请
define('CLIENT_SECRET', 'wordpress_secret');//应用密钥，需要申请
define('REDIRECT_URI', 'http://wordpress.iat.net.cn/wp-ucenter-login.php');//回调地址
define('UCENTER_HOME', 'http://ucenter.szjlxh.com');//用户中心地址

define('UCENTER_OAUTH', UCENTER_HOME . '/oauth');
define('UCENTER_API', UCENTER_HOME . '/api');

define('SUCCESS', 0);

if(isset($_GET['code'])){
    ucenter_oauth();
}

function ucenter_oauth() {
    //根据授权码获取access_token
    $url = UCENTER_API . '/oauth/accessToken';
    $data = array('client_id' => CLIENT_ID,
        'client_secret' => CLIENT_SECRET,
        'grant_type' => 'authorization_code',
        'redirect_uri' => REDIRECT_URI,
        'code' => $_GET['code']);
    $response = wp_remote_post($url, array('method' => 'POST', 'body' => $data));
    $data = json_decode($response['body'], true);
    if(SUCCESS !== $data['code']) {
        wp_die('授权失败');
    }
    $access_token = $data['data']['access_token'];

    //根据access_token获取用户信息
    $url = UCENTER_API . '/user/?access_token=' . $access_token;
    $data = wp_remote_get($url);
    $data = json_decode($data['body'], true);
    if(SUCCESS !== $data['code']) {
        wp_die('获取用户信息失败');
    }
    $username = $data['data']['username'];
    $user_id = $data['data']['user_id'];

    //根据返回的用户信息登录，用户还未存在时则插入
    $current_user = get_user_by('login', $username);
    if(is_wp_error($current_user) || !$current_user) {
        $random_password = wp_generate_password($length = 12, $include_standard_special_chars = false);
        $user_id = wp_insert_user(array(
            'user_login' => $username,
            'display_name' => $username,
            'nick_name' => $username,
            'user_pass' => $random_password
        ));
        wp_set_auth_cookie($user_id);
    } else {
        wp_set_auth_cookie($current_user->ID);
    }
    header('Location: ' . home_url() . '/wp-admin');
    exit;
}

//统一跳转
function wb_ouath_redirect(){
    echo '<script>
        if(window.opener) {
            window.opener.location.reload();
            window.close();
        } else {
            window.location.href = "' . home_url() . '/wp-admin";
        }
    </script>';
}

//生成授权url
function ucenter_oauth_url() {
    return UCENTER_OAUTH . '/authorize?client_id=' . CLIENT_ID . '&response_type=code&redirect_uri=' . urlencode(REDIRECT_URI);
}

header("Location: " . ucenter_oauth_url());
exit;
// echo '点此进入授权登录<a href=' . ucenter_oauth_url() . '>' . ucenter_oauth_url() . '</a>';
