<?php
/**
 * Cabeçalho <head> comum das páginas do painel/loja.
 * Espera (opcionais, definidos antes do include):
 *   $page_title — título da aba (sufixo de "Sprint Max — ...")
 *   $css_extra  — array de CSS adicionais em /assets/css/ (ex.: ['loja.css'])
 */
$page_title = $page_title ?? 'Sprint Max';
$css_extra  = $css_extra  ?? [];
?>
<head>
    <meta charset="UTF-8">
    <?php require __DIR__ . '/theme-init.php'; ?>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sprint Max — <?= htmlspecialchars($page_title) ?></title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <link rel="icon" href="/assets/img/favicon.png" type="image/x-icon">
    <link rel="stylesheet" href="/assets/css/dashboard.css">
    <link rel="stylesheet" href="/assets/css/theme.css">
    <?php foreach ($css_extra as $arquivoCss): ?>
    <link rel="stylesheet" href="/assets/css/<?= htmlspecialchars($arquivoCss) ?>">
    <?php endforeach; ?>
</head>
