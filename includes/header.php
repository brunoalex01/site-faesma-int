<?php
/**
 * FAESMA - Header Template
 * Common header for all pages
 */

// Start output buffering if not already started
if (!ob_get_level())
    ob_start();

// Get site settings
$site_settings = getAllSettings();
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>
        <?php echo isset($page_title) ? $page_title . ' - ' . SITE_NAME : SITE_NAME; ?>
    </title>
    <meta name="description"
        content="<?php echo $meta_description ?? 'FAESMA - Faculdade Alcance de Ensino Superior do Maranhão. Graduação e Pós-graduação EAD.'; ?>">
    <meta name="keywords"
        content="<?php echo $meta_keywords ?? 'faculdade, faesma, ensino superior, maranhão, graduação, pós-graduação, ead'; ?>">
    <meta name="author" content="FAESMA">
    <meta property="og:title" content="<?php echo isset($page_title) ? $page_title : SITE_NAME; ?>">
    <meta property="og:description"
        content="<?php echo $meta_description ?? 'Faculdade Alcance de Ensino Superior do Maranhão'; ?>">
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?php echo getCurrentUrl(); ?>">
    <link rel="icon" type="favicon.ico" href="<?php echo ASSETS_URL; ?>assets/img/favicon.ico">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?php echo ASSETS_URL; ?>/css/style.css">
    <?php if (isset($additional_css)): ?>
        <?php foreach ($additional_css as $css): ?>
            <link rel="stylesheet" href="<?php echo $css; ?>">
        <?php endforeach; ?>
    <?php endif; ?>
</head>

<body class="<?php echo $body_class ?? ''; ?>">

    <!-- Header -->
    <header class="site-header">
        <!-- Main Navigation -->
        <nav class="main-nav">
            <div class="container">
                <div class="nav-content">
                    <!-- Logo -->
                    <div class="logo">
                        <a href="<?php echo BASE_URL; ?>/">
                            <img src="assets/img/BANNER FAESMA FINAL PROD.png" alt="logo faesma" width=200px height=auto>
                        </a>
                    </div>

                    <!-- Mobile Menu Toggle -->
                    <button class="mobile-menu-toggle" aria-label="Menu" aria-expanded="false">
                        <span></span>
                        <span></span>
                        <span></span>
                    </button>

                    <!-- Navigation Menu -->
                    <ul class="nav-menu">
                        <li><a href="<?php echo BASE_URL; ?>/"
                                class="<?php echo (basename($_SERVER['PHP_SELF']) == 'index.php') ? 'active' : ''; ?>">Início</a>
                        </li>

                        <li class="has-submenu">
                            <a href="<?php echo BASE_URL; ?>/cursos.php"
                                class="<?php echo (basename($_SERVER['PHP_SELF']) == 'cursos.php' || basename($_SERVER['PHP_SELF']) == 'curso-detalhes.php') ? 'active' : ''; ?>">
                                Cursos <i class="fas fa-chevron-down"></i>
                            </a>
                            <ul class="submenu">
                                <?php
                                $categories = getCourseCategories();
                                foreach ($categories as $category):
                                    ?>
                                    <li>
                                        <a
                                            href="<?php echo BASE_URL; ?>/cursos.php?categoria=<?php echo $category['nome']; ?>">
                                            <?php echo $category['nome']; ?>
                                        </a>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </li>

                        <li><a href="<?php echo BASE_URL; ?>/sobre.php"
                                class="<?php echo (basename($_SERVER['PHP_SELF']) == 'sobre.php') ? 'active' : ''; ?>">Sobre</a>
                        </li>
                        <li><a href="<?php echo BASE_URL; ?>/vestibular.php"
                                class="<?php echo (basename($_SERVER['PHP_SELF']) == 'vestibular.php') ? 'active' : ''; ?>">Processo Seletivo</a>
                        </li>
                        <li><a href="<?php echo BASE_URL; ?>/contato.php"
                                class="<?php echo (basename($_SERVER['PHP_SELF']) == 'contato.php') ? 'active' : ''; ?>">Contato</a>
                        </li>
                    </ul>

                    <!-- CTA Button -->
                    <div class="nav-cta">
                        <a href="https://app.faesma.com.br/" class="btn btn-primary">
                            Área do Aluno
                        </a>
                    </div>
                </div>
            </div>
        </nav>
    </header>

    <!-- Main Content -->
    <main class="site-content">