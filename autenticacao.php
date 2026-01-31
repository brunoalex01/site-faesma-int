<?php
/**
 * FAESMA - Autenticação de Documentos
 * Página para autenticação de documentos via sistema externo
 */

define('FAESMA_ACCESS', true);

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/Database.php';
require_once __DIR__ . '/includes/functions.php';

$page_title = 'Autenticação de Documentos';
$meta_description = 'Autentique seus documentos da FAESMA - Faculdade Alcance de Ensino Superior do Maranhão.';

include __DIR__ . '/includes/header.php';
?>

<!-- Page Header -->
<section style="background: linear-gradient(135deg, var(--color-primary), var(--color-primary-light)); color: white; padding: 3rem 0; text-align: center;">
    <div class="container">
        <h1 style="font-size: 2.5rem; margin-bottom: 1rem; color: var(--color-white);">Autenticação de Documentos</h1>
        <p style="font-size: 1.1rem; opacity: 0.9;">Verifique a autenticidade dos seus documentos</p>
    </div>
</section>

<!-- Content Section -->
<section class="section" style="padding: 2rem 0;">
    <div class="container">
        <div class="autenticacao-wrapper" style="width: 100%; max-width: 1200px; margin: 0 auto;">
            <iframe 
                src="https://app.faesma.com.br/projetos/unimestre/impressoes/download.php" 
                style="width: 100%; height: 80vh; min-height: 600px; border: none; border-radius: var(--radius-lg); box-shadow: var(--shadow-lg);"
                title="Sistema de Autenticação de Documentos"
                loading="lazy">
            </iframe>
        </div>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
