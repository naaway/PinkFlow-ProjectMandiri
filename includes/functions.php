<?php
if(session_status()!==PHP_SESSION_ACTIVE) session_start();
function e($v):string{return htmlspecialchars((string)$v,ENT_QUOTES,'UTF-8');}
function rp($v):string{ $amount=(float)$v; if($amount>0 && $amount<1000){$amount*=1000;} return 'Rp '.number_format($amount,0,',','.');}
function product_image_file($image, $productName=''): string {
    $directory = dirname(__DIR__) . '/assets/images/';
    $candidate = basename((string) $image);
    if ($candidate !== '' && is_file($directory . $candidate)) { return $candidate; }
    $name = strtolower(preg_replace('/[^a-z0-9]/i', '', (string) $productName));
    $files = glob($directory . '*.{jpg,jpeg,png,webp,JPG,JPEG,PNG,WEBP}', GLOB_BRACE) ?: [];
    $bestFile = ''; $bestScore = 0;
    foreach ($files as $file) {
        $base = pathinfo($file, PATHINFO_FILENAME);
        if (stripos($base, 'logo') !== false) { continue; }
        $normalized = strtolower(preg_replace('/[^a-z0-9]/i', '', $base));
        similar_text($name, $normalized, $score);
        if ($normalized === $name) { return basename($file); }
        if ($score > $bestScore) { $bestScore = $score; $bestFile = basename($file); }
    }
    return $bestScore >= 55 ? $bestFile : '';
}
function product_image_url($image, $productName='', $prefix='../'): string {
    $file = product_image_file($image, $productName);
    return $file !== '' ? $prefix . 'assets/images/' . rawurlencode($file) : $prefix . 'assets/images/logo-pinkflow.png';
}
function cart_count():int{$n=0;foreach($_SESSION['cart']??[] as $q)$n+=(int)$q;return $n;}
function csrf():string{if(empty($_SESSION['csrf']))$_SESSION['csrf']=bin2hex(random_bytes(24));return $_SESSION['csrf'];}
function check_csrf():void{if($_SERVER['REQUEST_METHOD']==='POST'&&!hash_equals($_SESSION['csrf']??'',$_POST['csrf']??'')){http_response_code(419);exit('Form kedaluwarsa. Muat ulang halaman.');}}
function wa(string $m):string{return 'https://wa.me/6282187496556?text='.rawurlencode($m);}
