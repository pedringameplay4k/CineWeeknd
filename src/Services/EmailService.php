<?php
/**
 * CineWeeknd — EmailService via Resend API
 *
 * ── SETUP (2 minutos) ───────────────────────────────────────
 * 1. Crie conta grátis em: https://resend.com
 * 2. Gere uma API Key no painel: API Keys → Create API Key
 * 3. Cole a chave em RESEND_API_KEY abaixo
 * ────────────────────────────────────────────────────────────
 */

// ═══════════════════════════════════════
// ► CONFIGURAÇÃO — edite aqui ◄
// ═══════════════════════════════════════
define('RESEND_API_KEY', 're_jTzs6zgh_FcXMhp4TSF8PAMSCi9NbZNYi');   // ← cole sua API Key
define('MAIL_FROM',      'onboarding@resend.dev'); // domínio padrão Resend (funciona sem configurar domínio próprio)
define('MAIL_FROM_NAME', 'CineWeeknd');
define('MAIL_ENABLED',   true);                // false = simula sem enviar
// ═══════════════════════════════════════

class EmailService {

    public static function sendOrderConfirmation(
        string $toEmail,
        string $toName,
        array  $order,
        array  $items,
        array  $tokens = []
    ): bool {
        if (!MAIL_ENABLED) return true;

        $subject = '🎬 Seu ingresso CineWeeknd — Pedido #' . str_pad($order['id'], 6, '0', STR_PAD_LEFT);
        $html    = self::buildEmailHTML($toName, $order, $items, $tokens);

        // Chama a API REST do Resend (sem precisar de Composer/extensões)
        $payload = json_encode([
            'from'    => MAIL_FROM_NAME . ' <' . MAIL_FROM . '>',
            'to'      => [$toEmail],
            'subject' => $subject,
            'html'    => $html,
        ]);

        $ch = curl_init('https://api.resend.com/emails');
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $payload,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER     => [
                'Authorization: Bearer ' . RESEND_API_KEY,
                'Content-Type: application/json',
            ],
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200) {
            $msg = 'Resend HTTP ' . $httpCode . ': ' . $response;
            error_log('CineWeeknd ' . $msg);
            throw new \RuntimeException($msg);
        }

        return true;
    }

    // ──────────────────────────────────────────────────────
    // HTML do e-mail — ingresso estilizado inline
    // ──────────────────────────────────────────────────────
    public static function buildEmailHTML(string $name, array $order, array $items, array $tokens = []): string {
        $orderNum = str_pad($order['id'], 6, '0', STR_PAD_LEFT);
        $date     = date('d/m/Y', strtotime($order['created_at']));
        $time     = date('H:i',   strtotime($order['created_at']));
        $total    = 'R$ ' . number_format($order['total'],    2, ',', '.');
        $subtotal = 'R$ ' . number_format($order['subtotal'], 2, ',', '.');

        $itemRows = '';
        foreach ($items as $item) {
            // Compatível com itens do carrinho (type/price/qty/name)
            // E itens do banco (item_type/unit_price/quantity/item_name)
            $itype = $item['item_type'] ?? $item['type'] ?? 'movie';
            $iname = $item['item_name'] ?? $item['name'] ?? '';
            $iqty  = $item['quantity']  ?? $item['qty']  ?? 1;
            $iprice= $item['unit_price']?? $item['price'] ?? 0;
            $iseat = $item['seat'] ?? null;

            $icon  = $itype === 'movie' ? '🎥' : '🍿';
            $label = $itype === 'movie' ? 'INGRESSO' : 'COMBO';
            $total = 'R$ ' . number_format($iprice * $iqty, 2, ',', '.');
            $seatBadge = $iseat
                ? "<span style='background:#ff5f1f;color:#fff;font-weight:800;font-size:10px;padding:2px 7px;border-radius:5px;margin-left:6px'>🪑 {$iseat}</span>"
                : '';

            $itemRows .= "
            <tr>
              <td style='padding:12px 0;border-bottom:1px solid #2e2750'>
                <table width='100%' cellpadding='0' cellspacing='0'><tr>
                  <td width='32' style='font-size:1.2rem;vertical-align:middle'>{$icon}</td>
                  <td style='vertical-align:middle;padding-left:10px'>
                    <div style='font-weight:700;color:#f0eaff;font-size:14px'>" . htmlspecialchars($iname) . "</div>
                    <div style='font-size:11px;color:#8b7fb5;text-transform:uppercase;letter-spacing:1px;margin-top:3px'>
                      {$label} · {$iqty}x {$seatBadge}
                    </div>
                  </td>
                  <td align='right' style='font-weight:700;color:#ff5f1f;font-size:14px;vertical-align:middle'>{$total}</td>
                </tr></table>
              </td>
            </tr>";
        }

        $discountRow = '';
        if (($order['discount'] ?? 0) > 0) {
            $disc = 'R$ ' . number_format($order['discount'], 2, ',', '.');
            $discountRow = "<tr><td style='text-align:right;color:#10b981;font-size:13px;padding-bottom:6px'>Desconto: -{$disc}</td></tr>";
        }

        // Gera bloco de links de acesso
        $tokenLinksHtml = '';
        if (!empty($tokens)) {
            foreach ($tokens as $tok) {
                $watchUrl = (isset($_SERVER['HTTP_HOST']) ? 'http://' . $_SERVER['HTTP_HOST'] : 'http://localhost') . '/CineWeeknd_final/public/watch/' . $tok['token'];
                $screening = isset($tok['starts_at']) ? date('d/m/Y H:i', strtotime($tok['starts_at'])) : '';
                $tokenLinksHtml .= "
                <div style='margin-bottom:8px;padding:10px 12px;background:rgba(0,0,0,.2);border-radius:8px'>
                  <div style='font-size:12px;color:#f0eaff;font-weight:700;margin-bottom:4px'>" . htmlspecialchars($tok['movie_title'] ?? $tok['title'] ?? '') . "</div>
                  <div style='font-size:11px;color:#8b7fb5;margin-bottom:6px'>" . $screening . "</div>
                  <a href='{$watchUrl}' style='display:inline-block;background:linear-gradient(135deg,#7c3aed,#a855f7);color:#fff;text-decoration:none;font-size:12px;font-weight:700;padding:6px 14px;border-radius:8px'>
                    ▶ Assistir Agora
                  </a>
                  <div style='font-size:10px;color:#5e5580;margin-top:4px'>Link válido por 3h a partir do início da sessão</div>
                </div>";
            }
        }
        $tokenBlock = $tokenLinksHtml ? "
        <div style='background:rgba(168,85,247,.08);border:1px solid rgba(168,85,247,.2);border-radius:10px;padding:14px 16px;margin-bottom:16px'>
          <div style='font-size:11px;color:#8b7fb5;text-transform:uppercase;letter-spacing:1px;font-weight:700;margin-bottom:10px'>🎬 Seus Links de Acesso</div>
          {$tokenLinksHtml}
        </div>" : '';

        return <<<HTML
<!DOCTYPE html>
<html lang="pt-BR">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"></head>
<body style="margin:0;padding:0;background:#0d0a1a;font-family:'Segoe UI',Arial,sans-serif">
<table width="100%" cellpadding="0" cellspacing="0" style="background:#0d0a1a;padding:32px 16px">
<tr><td align="center">
<table width="100%" style="max-width:520px;background:#120f22;border-radius:20px;overflow:hidden;border:1px solid #2e2750" cellpadding="0" cellspacing="0">

  <!-- Header -->
  <tr><td style="background:linear-gradient(135deg,#1e1340,#2a1a4a);padding:28px 32px;text-align:center;border-bottom:1px solid #2e2750">
    <div style="font-size:28px;margin-bottom:8px">🎬</div>
    <div style="font-size:24px;font-weight:700;color:#f0eaff;letter-spacing:1px">Cine<span style="color:#ff5f1f">Weeknd</span></div>
    <div style="font-size:13px;color:#8b7fb5;margin-top:4px">Seu ingresso chegou!</div>
  </td></tr>

  <!-- Confirmação -->
  <tr><td style="padding:24px 32px 8px">
    <div style="background:linear-gradient(135deg,rgba(16,185,129,.12),rgba(5,150,105,.06));border:1px solid rgba(16,185,129,.25);border-radius:12px;padding:16px 20px;text-align:center">
      <div style="font-size:22px;margin-bottom:6px">✅</div>
      <div style="font-weight:700;color:#f0eaff;font-size:16px">Pagamento Confirmado!</div>
      <div style="color:#8b7fb5;font-size:13px;margin-top:4px">Olá, <strong style="color:#f0eaff">{$name}</strong>! Boa sessão 🍿</div>
    </div>
  </td></tr>

  <!-- Número do pedido -->
  <tr><td style="padding:16px 32px 0">
    <table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:16px">
      <tr>
        <td style="font-size:11px;color:#8b7fb5;text-transform:uppercase;letter-spacing:1px;font-weight:700">Pedido</td>
        <td align="right" style="font-family:'Courier New',monospace;color:#a855f7;font-size:14px;font-weight:700;letter-spacing:2px">#{$orderNum}</td>
      </tr>
    </table>
    <table width="100%" cellpadding="0" cellspacing="0">{$itemRows}</table>
  </td></tr>

  <!-- Linha picotada -->
  <tr><td>
    <table width="100%" cellpadding="0" cellspacing="0"><tr>
      <td width="16" style="background:#0d0a1a;border-radius:0 50% 50% 0"></td>
      <td style="border-top:2px dashed #2e2750;padding:12px 0"></td>
      <td width="16" style="background:#0d0a1a;border-radius:50% 0 0 50%"></td>
    </tr></table>
  </td></tr>

  <!-- Meta info -->
  <tr><td style="padding:0 32px 24px">
    <table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:16px">
      <tr>
        <td align="center" style="border-right:1px solid #2e2750;padding:8px 0">
          <div style="font-size:10px;color:#8b7fb5;text-transform:uppercase;letter-spacing:.8px;font-weight:700">Data</div>
          <div style="font-size:13px;color:#f0eaff;font-weight:700;margin-top:3px">{$date}</div>
        </td>
        <td align="center" style="border-right:1px solid #2e2750;padding:8px 0">
          <div style="font-size:10px;color:#8b7fb5;text-transform:uppercase;letter-spacing:.8px;font-weight:700">Horário</div>
          <div style="font-size:13px;color:#f0eaff;font-weight:700;margin-top:3px">{$time}</div>
        </td>
        <td align="center" style="border-right:1px solid #2e2750;padding:8px 0">
          <div style="font-size:10px;color:#8b7fb5;text-transform:uppercase;letter-spacing:.8px;font-weight:700">Pagamento</div>
          <div style="font-size:13px;color:#10b981;font-weight:700;margin-top:3px">✓ Confirmado</div>
        </td>
        <td align="center" style="padding:8px 0">
          <div style="font-size:10px;color:#8b7fb5;text-transform:uppercase;letter-spacing:.8px;font-weight:700">Status</div>
          <div style="font-size:13px;color:#10b981;font-weight:700;margin-top:3px">Ativo</div>
        </td>
      </tr>
    </table>

    <!-- Total -->
    {$discountRow}
    <table width="100%" cellpadding="0" cellspacing="0" style="background:rgba(168,85,247,.08);border:1px solid rgba(168,85,247,.2);border-radius:10px;margin-bottom:20px">
      <tr>
        <td style="padding:12px 16px;color:#8b7fb5;font-size:13px;font-weight:700">Total Pago</td>
        <td align="right" style="padding:12px 16px;color:#ff5f1f;font-size:20px;font-weight:700">{$total}</td>
      </tr>
    </table>

    <!-- Barcode simulado -->
    <div style="text-align:center;background:#0d0a1a;border-radius:8px;padding:12px;margin-bottom:8px">
      <div style="font-family:'Courier New',monospace;font-size:22px;color:rgba(240,234,255,.6);letter-spacing:2px;line-height:1">
        ||||| || ||| ||||| | |||| ||||| ||| || |||||
      </div>
      <div style="font-family:'Courier New',monospace;font-size:10px;color:rgba(255,255,255,.2);margin-top:6px;letter-spacing:2px">
        {$orderNum} · CINEWEEKND · {$date}
      </div>
    </div>
  </td></tr>

  <!-- Rodapé -->
  <tr><td style="background:#0d0a1a;padding:16px 32px;text-align:center;border-top:1px solid #2e2750">
    <div style="font-size:12px;color:#5e5580">Este é um e-mail automático — não responda.</div>
    <div style="font-size:12px;color:#5e5580;margin-top:4px">© 2026 CineWeeknd · Todos os direitos reservados</div>
  </td></tr>

</table>
</td></tr>
</table>
</body>
</html>
HTML;
    }
}
