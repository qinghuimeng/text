<?php 
require_once __DIR__ . '/lib/YZTokenClient.php';

$token = 'fill access_token';//请填入商家授权后获取的access_token
$client = new YZTokenClient($token);

$method = 'kdt.item.add';//要调用的api名称
$methodVersion = '1.0.0';//要调用的api版本号

$params = [
    'title' => 'api测试商品',
    'desc' => '这里是描述',
    'price' => '0.01',
    'post_fee' => '0',
    'quantity' => '88',
];

$files = [
	[
		'url' => __DIR__ . '/test1.png',
		'field' => 'images[]',
	],
	[
		'url' => __DIR__ . '/test2.png',
		'field' => 'images[]',
	],
];

echo '<pre>';
var_dump( 
	$client->post($method, $methodVersion, $params, $files)
);
echo '</pre>';
