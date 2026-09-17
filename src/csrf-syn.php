<?php
// SESSION FOUND! token = wGJtk6fy3odfgueh1MLB0kI2zIL52VgmoH44lryA
// Key = sistem-informasi-sekretariat-cache-tEENa5ozaWyXjpBwyQEGZtL4oppggbJj6RlrIa9F
// Session id stored in cookie decrypts to: hash|tEENa5o... 
// => Laravel 13 session cookie value = {csrf_hash}|{session_id}?? No...
// => The cookie encryption produces an array [hash, sid]? The decrypted 'value' part
//    tEENa5o... IS the session id. The part1 df2427... is the HMAC-like hash for the cookie.
// So the session IS found via part2 as sid. 

// Now the 419: form token sent was BJMr8yGO... but session token = wGJtk6fy...
// => the POST's session cookie differs from the GET's! The sid AFTER login (S1)
//    != sid used at create GET. Every response ROTATES session id (new cookie).
//    With curl -b -c it should follow... BUT we did 2x GET /anggota/create in
//    token-stability test: both gave the same token BUT sid changed each time!
//    That means: each request creates a NEW session (session not persisted by client?).

// CRITICAL INSIGHT: session cookie value changes on EVERY request. The jar updates.
// So POST sends latest sid + its token. Mismatch => because the POST's sid is
// yet ANOTHER new session (rotated again on the last GET) whose _token differs.

// Wait no - token-stability test showed T1 == T2 across two GETs with -c updating jar.
// If each GET rotated sid, the second GET would have a NEW session with NEW random token.
// T1 == T2 disproves that. So tokens stable per session.

// Then why does POST 419? The POST goes to $BASE/anggota. With cookies from jar.
// Compare sid sent on POST vs the sid that GET gave token for. Add debug to response.

// Let's check nginx access log for POST - maybe it goes through cloudflared? No.
// Check: does POST even reach Laravel? Add temporary logging middleware? Simpler:
// test POST /login with correct token (that WORKED earlier - 302). So POST csrf works
// for /login but not /anggota?! Difference: route middleware auth on POST anggota store?
// CSRF middleware runs BEFORE auth. Auth shouldn't matter.

// Hmm - actually wait: POST /anggota has middleware auth. If session's auth is OK,
// CSRF check passes first. Unless... VerifyCsrfToken runs in web group AFTER
// session started. Both fine.

// Test: POST to /login (guest-only) with the SAME jar after logging in
require '/var/www/html/vendor/autoload.php';
define('LARAVEL_START', microtime(true));
$app = require_once '/var/www/html/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Check EncryptCookies: maybe SESSION cookie gets encrypted with app key that changed?
// APP_KEY was generated BEFORE containers rebuilt? sidval decrypt WORKED (we got parts),
// so encryption key consistent.

// The remaining suspect: Laravel 13 CSRF token rotation on EVERY request via
// "ValidateCsrfToken" comparing HEADER X-XSRF-TOKEN vs cookie. Curl sends _token form
// field which is compared to session _token. Both stable. UNLESS nginx strips something?

// Direct test: POST with session cookie as RAW header + form token
echo "check sessions table: postgres sessions table unused since redis driver\n";
$redis = $app->make('redis')->connection();
$val = (string) $redis->get('sistem-informasi-sekretariat-cache-tEENa5ozaWyXjpBwyQEGZtL4oppggbJj6RlrIa9F');
preg_match('/_token\\\\";s:\d+:"([^"]+)"/', $val, $m);
echo 'token-extracted: ' . ($m[1] ?? 'none') . "\n";
echo 'form-token-was: BJMr8yGOLX0My35c7HUjMilt242WvkgCID4i54DA (from stability test - different session though)';