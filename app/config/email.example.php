<?php
/**
 * MODELO de configuração de e-mail (SMTP).
 *
 * Copie este arquivo para "email.php" (na mesma pasta) e preencha com as
 * suas credenciais. O email.php real NÃO é versionado (está no .gitignore).
 *
 * Dica (Gmail): ative a verificação em duas etapas e gere uma
 * "Senha de app" em https://myaccount.google.com/apppasswords —
 * use essa senha de 16 caracteres no campo 'senha'.
 */
return [
    // Mude para true depois de preencher as credenciais abaixo.
    'ativo'           => false,

    'host'            => 'smtp.gmail.com',
    'porta'           => 587,
    'seguranca'       => 'tls',            // 'tls' (587) ou 'ssl' (465)
    'usuario'         => 'seu-email@gmail.com',
    'senha'           => 'sua-senha-de-app',

    'remetente_email' => 'seu-email@gmail.com',
    'remetente_nome'  => 'Sprint Max',
];
