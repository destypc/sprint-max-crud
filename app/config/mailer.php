<?php

require_once __DIR__ . '/../../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

/**
 * Lê a configuração de e-mail.
 * Prioridade: arquivo local app/config/email.php (dev) → variáveis de
 * ambiente (produção). Retorna null se nenhuma fonte estiver disponível.
 */
function configEmail(): ?array
{
    $caminho = __DIR__ . '/email.php';
    if (is_file($caminho)) {
        return require $caminho;
    }

    // Fallback para variáveis de ambiente (deploy).
    $usuario = getenv('MAIL_USERNAME');
    if ($usuario === false || $usuario === '') {
        return null;
    }

    return [
        'ativo'           => filter_var(getenv('MAIL_ATIVO') ?: 'true', FILTER_VALIDATE_BOOLEAN),
        'host'            => getenv('MAIL_HOST') ?: 'smtp.gmail.com',
        'porta'           => getenv('MAIL_PORT') ?: 587,
        'seguranca'       => getenv('MAIL_SEGURANCA') ?: 'tls',
        'usuario'         => $usuario,
        'senha'           => getenv('MAIL_PASSWORD') ?: '',
        'remetente_email' => getenv('MAIL_FROM') ?: $usuario,
        'remetente_nome'  => getenv('MAIL_FROM_NOME') ?: 'Sprint Max',
    ];
}

/**
 * Indica se o envio de e-mail está configurado e habilitado.
 */
function emailAtivo(): bool
{
    $cfg = configEmail();
    return !empty($cfg['ativo']);
}

/**
 * Envia um e-mail HTML via SMTP usando as credenciais de email.php.
 * Retorna true em caso de sucesso; registra o erro no log em caso de falha.
 */
function enviarEmail(string $para, string $paraNome, string $assunto, string $corpoHtml): bool
{
    $cfg = configEmail();
    if (empty($cfg['ativo'])) {
        return false;
    }

    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = $cfg['host'];
        $mail->SMTPAuth   = true;
        $mail->Username   = $cfg['usuario'];
        $mail->Password   = $cfg['senha'];
        $mail->SMTPSecure = ($cfg['seguranca'] ?? 'tls') === 'ssl'
            ? PHPMailer::ENCRYPTION_SMTPS
            : PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = (int) $cfg['porta'];
        $mail->Timeout    = 15;
        $mail->CharSet    = 'UTF-8';

        $mail->setFrom($cfg['remetente_email'], $cfg['remetente_nome'] ?? 'Sprint Max');
        $mail->addAddress($para, $paraNome);

        $mail->isHTML(true);
        $mail->Subject = $assunto;
        $mail->Body    = $corpoHtml;
        $mail->AltBody = trim(strip_tags(str_replace(['<br>', '<br/>', '<br />'], "\n", $corpoHtml)));

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log('enviarEmail falhou: ' . $mail->ErrorInfo);
        return false;
    }
}

/**
 * Monta o corpo HTML do e-mail de recuperação de senha.
 */
function corpoEmailRecuperacao(string $nome, string $link): string
{
    $nomeSeguro = htmlspecialchars($nome, ENT_QUOTES);
    $linkSeguro = htmlspecialchars($link, ENT_QUOTES);

    return '
    <div style="font-family:Arial,Helvetica,sans-serif;max-width:480px;margin:0 auto;background:#1a1a1d;border:1px solid #2a2a2e;border-radius:12px;overflow:hidden">
        <div style="background:linear-gradient(135deg,#f97316,#fb923c);padding:20px 24px">
            <h1 style="margin:0;color:#fff;font-size:20px;font-weight:700">Sprint Max</h1>
        </div>
        <div style="padding:24px;color:#e5e7eb">
            <p style="margin:0 0 12px">Olá, <strong>' . $nomeSeguro . '</strong>.</p>
            <p style="margin:0 0 16px;line-height:1.6">
                Recebemos uma solicitação para redefinir a senha da sua conta.
                Clique no botão abaixo para criar uma nova senha. O link expira em 1 hora.
            </p>
            <p style="text-align:center;margin:24px 0">
                <a href="' . $linkSeguro . '" style="display:inline-block;background:#f97316;color:#fff;text-decoration:none;font-weight:600;padding:12px 24px;border-radius:8px">
                    Redefinir minha senha
                </a>
            </p>
            <p style="margin:16px 0 0;font-size:12px;color:#9ca3af;line-height:1.6">
                Se você não solicitou isso, ignore este e-mail — sua senha permanecerá a mesma.
            </p>
        </div>
    </div>';
}
