<?php
require_once __DIR__ . '/lib/YZOauthClient.php';

$clientId = "fill client_id";//请填入开发者后台的client_id
$clientSecret = "fill client_secret";//请填入开发者后台的client_secret
$redirectUrl = "fill redirect_uri";//请填入开发者后台所填写的回调地址，本示例中回调地址应指向本文件

$token = new YZOauthClient( $clientId , $clientSecret );
$keys = array();
$type = 'code';//如要刷新access_token，这里的值为refresh_token
$keys['code'] = $_GET['code'];//如要刷新access_token，这里为$keys['refresh_token']
$keys['redirect_uri'] = $redirectUrl;

echo '<pre>';
var_dump(
    $token->getToken( $type , $keys )
);
echo '</pre>';