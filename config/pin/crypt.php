<?php

$pem = static fn (string $name, string $default) => ($value = env($name))
    ? str_replace('\n', PHP_EOL, $value)
    : $default;

return [

    /**
     * AES 对称加密密钥
     *
     * 要求长度为 16 字节
     */
    'key' => env('CRYPT_KEY', '8DbMV5CPrMFLFCP5'),

    /**
     * AES 初始化向量（IV）
     *
     * 要求长度为 16 字节
     */
    'iv' => env('CRYPT_IV', 'RVn4PTEgJdsOOkcY'),

    /**
     * RSA 私钥
     */
    'private_key' => $pem('CRYPT_PRIVATE_KEY', <<<'PEM'
-----BEGIN RSA PRIVATE KEY-----
MIICXQIBAAKBgQDbh2W4CQBvJVJK3MYl7jm6INgKPp1yNwQGpItSjIpaWhVRj48M
2qopT5IQ+GDVfx/B+psNC75c6uuinVPyZU6vvNAzWFR/ftRML2/hnj3YNtro9vPd
nYZQImBIIBDOCL6eT7QY8ajHswWAdCxfdhUAVxi3cQ56FWayYtC7XC5tJQIDAQAB
AoGAE4MQTnA2YqqditvenMZ0yQ9cRGTWV61+JR0A6SBmt6skE/C/lTPmRe+1mt6I
tNEswHAG0f0GOXoD4Zs8N+I7QQdQrkrDt5uulduH7dPtG+MLXgkrmlYQgwDQ8KP6
gCIV6ak3g/JTlgjRRT7EHVRcPcf9BAH9ZYp4xwWTC65X8G0CQQDwt1wzsYbKf2/t
hlne7nB0cZQtmNgWqnCwbFZVXzS+/OIDhxdpsHy9viBCTO+TZa5v2rcRAEyLhm+r
PbALcq63AkEA6Xenkj5mpWFn67HU7V74pp/0pcXtD+WgXVfzgmRrmZCzPQy9l/zY
+1/O8YQyHfI/TZAvcZ30PEr9ZRSBWAunAwJAKqHe44zieYS+dwvfaNtD8WuYOccj
JkiDcsuNMsuM1PKGuOc5H0/Rl+1PW06y86EDlu3elFVAOUnTBzoCrtRd2wJBAMMM
FBM5zseb+RYQG7O0BEgwmlNkaAk/7hoNwILPIpXJLfVzD3JK63wXLuzXZIdgO0Kx
kvF45PL6M3ifCPBly8ECQQDVrwNmsxKqqpwdxHgZMpmTwMibLDfKlxoufifVRd8+
SI5DBB6PX6xKFiEQDFigZUo289bJdtrpjVflfB570UuL
-----END RSA PRIVATE KEY-----
PEM),

    /**
     * RSA 公钥
     */
    'public_key' => $pem('CRYPT_PUBLIC_KEY', <<<'PEM'
-----BEGIN PUBLIC KEY-----
MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQDbh2W4CQBvJVJK3MYl7jm6INgK
Pp1yNwQGpItSjIpaWhVRj48M2qopT5IQ+GDVfx/B+psNC75c6uuinVPyZU6vvNAz
WFR/ftRML2/hnj3YNtro9vPdnYZQImBIIBDOCL6eT7QY8ajHswWAdCxfdhUAVxi3
cQ56FWayYtC7XC5tJQIDAQAB
-----END PUBLIC KEY-----
PEM),
];
