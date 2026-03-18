<?php
/**
 * CineWeeknd — Teste de E-mail
 * Acesse: http://localhost/CineWeeknd_final/public/teste_email.php
 * DELETE este arquivo depois de testar!
 */

$apiKey   = 're_GSP7YEkn_GEkGWfBuB59Qg2oConunTW2R';
$toEmail  = 'pedroscamargo23@gmail.com';

echo "<style>body{font-family:monospace;background:#0d0a1a;color:#f0eaff;padding:30px;font-size:14px}
.ok{color:#10b981}.err{color:#ef4444}.warn{color:#ffe94d}
pre{background:#1a1530;padding:16px;border-radius:8px;overflow:auto}
h2{color:#a855f7}</style>";

echo "<h2>🔍 CineWeeknd — Diagnóstico de E-mail</h2>";

// 1. cURL disponível?
echo "<b>1. cURL instalado:</b> ";
if (function_exists('curl_init')) {
    echo "<span class='ok'>✅ SIM</span><br>";
} else {
    echo "<span class='err'>❌ NÃO — Ative no php.ini: descomente ;extension=curl</span><br>";
    exit;
}

// 2. OpenSSL disponível?
echo "<b>2. OpenSSL (HTTPS):</b> ";
if (extension_loaded('openssl')) {
    echo "<span class='ok'>✅ SIM</span><br>";
} else {
    echo "<span class='warn'>⚠️ Não encontrado — pode causar problemas SSL</span><br>";
}

// 3. Testa conexão com Resend
echo "<b>3. Enviando e-mail para {$toEmail}...</b><br><br>";

$payload = json_encode([
    'from'    => 'CineWeeknd <onboarding@resend.dev>',
    'to'      => [$toEmail],
    'subject' => '🎬 Teste CineWeeknd — E-mail funcionando!',
    'html'    => '
    <div style="background:#0d0a1a;padding:32px;font-family:Arial,sans-serif">
      <div style="max-width:480px;margin:0 auto;background:#120f22;border-radius:16px;overflow:hidden;border:1px solid #2e2750">
        <div style="background:linear-gradient(135deg,#1e1340,#2a1a4a);padding:24px;text-align:center">
          <div style="font-size:32px">🎬</div>
          <div style="font-size:22px;font-weight:700;color:#f0eaff">Cine<span style="color:#ff5f1f">Weeknd</span></div>
        </div>
        <div style="padding:24px;text-align:center">
          <div style="font-size:40px;margin-bottom:12px">✅</div>
          <h2 style="color:#10b981;margin:0 0 8px">E-mail funcionando!</h2>
          <p style="color:#8b7fb5;margin:0">A integração com Resend está ativa.<br>Seus comprovantes vão chegar certinho.</p>
        </div>
        <div style="background:#0d0a1a;padding:14px;text-align:center;border-top:1px solid #2e2750">
          <span style="font-size:12px;color:#5e5580">© 2026 CineWeeknd</span>
        </div>
      </div>
    </div>',
]);

$ch = curl_init('https://api.resend.com/emails');
curl_setopt_array($ch, [
    CURLOPT_POST           => true,
    CURLOPT_POSTFIELDS     => $payload,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT        => 15,
    CURLOPT_HTTPHEADER     => [
        'Authorization: Bearer ' . $apiKey,
        'Content-Type: application/json',
    ],
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlErr  = curl_error($ch);
curl_close($ch);

if ($curlErr) {
    echo "<span class='err'>❌ Erro cURL: {$curlErr}</span><br><br>";
    echo "<b>Solução provável:</b> No <code>php.ini</code> do XAMPP, procure e corrija:<br>";
    echo "<pre>curl.cainfo = \"C:/xampp/apache/bin/curl-ca-bundle.crt\"</pre>";
} elseif ($httpCode === 200) {
    $data = json_decode($response, true);
    echo "<span class='ok'>✅ E-mail enviado com sucesso! ID: " . ($data['id'] ?? '—') . "</span><br>";
    echo "<br>📬 Verifique a caixa de entrada de <b>{$toEmail}</b> (e o spam!)";
} elseif ($httpCode === 401) {
    echo "<span class='err'>❌ API Key inválida (401)</span><br>";
    echo "Verifique a chave em <code>src/Services/EmailService.php</code>";
} elseif ($httpCode === 422) {
    echo "<span class='warn'>⚠️ Erro de validação (422)</span><br>";
    echo "<pre>" . htmlspecialchars($response) . "</pre>";
} else {
    echo "<span class='err'>❌ HTTP {$httpCode}</span><br>";
    echo "<pre>" . htmlspecialchars($response) . "</pre>";
}

echo "<br><br><hr style='border-color:#2e2750'><small style='color:#5e5580'>⚠️ Delete este arquivo após testar: <code>public/teste_email.php</code></small>";
