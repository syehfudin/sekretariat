<?php
require '/var/www/html/vendor/autoload.php';
define('LARAVEL_START', microtime(true));
$app = require_once '/var/www/html/bootstrap/app.php';

$kernelHttp = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernelHttp->bootstrap();

$enc = $app->make('encrypter');
$cookieJar = '';

function sessCookie($response) {
    foreach ($response->headers->getCookies() as $c) {
        if (str_contains($c->getName(), 'session')) {
            return $c->getName() . '=' . $c->getValue();
        }
    }
    return null;
}

// STEP 1: GET /login
$req = Illuminate\Http\Request::create('/login', 'GET');
if ($cookieJar) {
    $req->headers->set('Cookie', $cookieJar);
}
$resp = $kernelHttp->handle($req);
if ($c = sessCookie($resp)) {
    $cookieJar = $cookieJar ? $cookieJar . '; ' . $c : $c;
}
$html1 = $resp->getContent();
preg_match('/name="_token" value="([^"]*)"/', $html1 ?? $resp->getContent(), $m);
$token = $m[1] ?? '';
echo 'step1 token: ' . substr($token, 0, 10) . "\n";

// STEP 2: POST /login
$req2 = Illuminate\Http\Request::create('/login', 'POST', [
    'username' => 'SekGWB',
    'password' => '12345',
    '_token' => $token,
]);
$req2->headers->set('Cookie', $cookieJar);
$resp2 = $kernelHttp->handle($req2);
echo 'step2 status: ' . $resp2->getStatusCode() . "\n";
if ($c = sessCookie($resp2)) {
    // merge - replace session cookie
    $cookieJar = preg_replace('/sistem-informasi-sekretariat-session=[^;]*/', $c, $cookieJar);
    if (! str_contains($cookieJar, 'sistem-informasi-sekretariat-session=')) {
        $cookieJar .= '; ' . $c;
    }
}

// STEP 3: GET /anggota/create
$req3 = Illuminate\Http\Request::create('/anggota/create', 'GET');
$req3->headers->set('Cookie', $cookieJar);
$resp3 = $kernelHttp->handle($req3);
echo 'step3 status: ' . $resp3->getStatusCode() . "\n";
if ($c = sessCookie($resp3)) {
    $cookieJar = preg_replace('/sistem-informasi-sekretariat-session=[^;]*/', $c, $cookieJar);
}
preg_match('/name="_token" value="([^"]*)"/', $resp3->getContent(), $m3);
$token3 = $m3[1] ?? '';
echo 'step3 token: ' . substr($token3 = $token3 ?? ($m3[1] ?? ''), 0, 10) . "\n";

// STEP 4: POST /anggota
$req4 = Illuminate\Http\Request::create('/anggota', 'POST', [
    'nama_lengkap' => 'Dewi Kernel Test',
    'pekerjaan' => 'Perawat',
    '_token' => $token3,
]);
$req4->headers->set('Cookie', $cookieJar);
$resp4 = $kernelHttp->handle($req4);
echo 'step4 status: ' . $resp4->getStatusCode() . "\n";
$resp4->sendHeaders();