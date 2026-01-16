<?php
/**
 * FAESMA - Homepage*/

// Define access constant
define('FAESMA_ACCESS', true);

// Include configuration and dependencies
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/Database.php';
require_once __DIR__ . '/includes/functions.php';

// Page meta information
$page_title = 'Início';
$meta_description = 'FAESMA - Faculdade Alcance de Ensino Superior do Maranhão. Cursos de graduação, pós-graduação e tecnólogo com qualidade e infraestrutura moderna.';
$meta_keywords = 'FAESMA, faculdade maranhão, ensino superior, graduação, pós-graduação, São Luís';

// Get featured courses
$featured_courses = getCourses(['destaque' => true, 'status' => 'ativo'], 6);

// Include header
include __DIR__ . '/includes/header.php';
?>

<!-- Hero Section -->
<section class="hero"
    style="background: linear-gradient(135deg, var(--color-primary) 0%, var(--color-primary-light) 100%); color: white; padding: 6rem 0;">
    <div class="container">
        <div style="max-width: 800px; margin: 0 auto; text-align: center;">
            <h1
                style="color: white; font-size: clamp(2.5rem, 6vw, 4rem); margin-bottom: 1.5rem; text-shadow: 2px 2px 4px rgba(0,0,0,0.2);">
                Transforme seu Futuro com a FAESMA
            </h1>
            <p
                style="font-size: clamp(1.1rem, 2vw, 1.3rem); margin-bottom: 2.5rem; color: rgba(255,255,255,0.95); line-height: 1.8;">
                Formação de excelência, infraestrutura moderna e corpo docente qualificado. Realize seus sonhos com a
                melhor faculdade do Maranhão.
            </p>
            <div style="display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap;">
                <a href="<?php echo BASE_URL; ?>/cursos.php" class="btn btn-secondary btn-large">
                    Conheça Nossos Cursos
                </a>
                <a href="<?php echo BASE_URL; ?>/vestibular.php" class="btn btn-outline btn-large"
                    style="color: white; border-color: white;">
                    Inscreva-se
                </a>
            </div>
        </div>
    </div>
</section>

<!-- Features Section -->
<section class="section">
    <div class="container">
        <div class="grid grid-3">
            <div class="feature-card" style="text-align: center; padding: 2rem;">
                <div
                    style="width: 80px; height: 80px; margin: 0 auto 1.5rem; background: linear-gradient(135deg, var(--color-secondary), var(--color-secondary-light)); border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                    <i class="fas fa-graduation-cap" style="font-size: 2.5rem; color: white;"></i>
                </div>
                <h3 style="color: var(--color-primary); margin-bottom: 1rem;">Ensino de Qualidade</h3>
                <p style="color: var(--color-gray-600);">Corpo docente qualificado com mestres e doutores comprometidos
                    com sua formação.</p>
            </div>

            <div class="feature-card" style="text-align: center; padding: 2rem;">
                <div
                    style="width: 80px; height: 80px; margin: 0 auto 1.5rem; background: linear-gradient(135deg, var(--color-primary), var(--color-primary-light)); border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                    <i class="fas fa-building" style="font-size: 2.5rem; color: white;"></i>
                </div>
                <h3 style="color: var(--color-primary); margin-bottom: 1rem;">Infraestrutura Moderna</h3>
                <p style="color: var(--color-gray-600);">Laboratórios equipados, biblioteca completa e salas de aula
                    confortáveis.</p>
            </div>

            <div class="feature-card" style="text-align: center; padding: 2rem;">
                <div
                    style="width: 80px; height: 80px; margin: 0 auto 1.5rem; background: linear-gradient(135deg, var(--color-accent-dark), var(--color-accent)); border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                    <i class="fas fa-certificate" style="font-size: 2.5rem; color: white;"></i>
                </div>
                <h3 style="color: var(--color-primary); margin-bottom: 1rem;">Reconhecimento MEC</h3>
                <p style="color: var(--color-gray-600);">Cursos autorizados e reconhecidos pelo Ministério da Educação.
                </p>
            </div>
        </div>
    </div>
</section>

<!-- Featured Courses Section -->
<?php if (!empty($featured_courses)): ?>
    <section class="section section-light">
        <div class="container">
            <div style="text-align: center; margin-bottom: 3rem;">
                <h2 style="color: var(--color-primary);">Cursos em Destaque</h2>
                <p style="color: var(--color-gray-600); font-size: 1.1rem;">Conheça nossos principais cursos de graduação e
                    pós-graduação</p>
            </div>

            <div class="grid grid-3">
                <?php foreach ($featured_courses as $course): ?>
                    <div class="card">
                        <div class="card-image">
                            <?php if ($course['imagem_destaque']): ?>
                                <img src="<?php echo UPLOADS_URL; ?>/courses/<?php echo $course['imagem_destaque']; ?>"
                                    alt="<?php echo htmlspecialchars($course['nome']); ?>">
                            <?php endif; ?>
                            <div class="card-badge">
                                <?php echo htmlspecialchars($course['categoria_nome']); ?>
                            </div>
                        </div>
                        <div class="card-content">
                            <h3 class="card-title">
                                <?php echo htmlspecialchars($course['nome']); ?>
                            </h3>
                            <p class="card-text">
                                <?php echo htmlspecialchars($course['descricao_curta']); ?>
                            </p>
                            <div
                                style="display: flex; gap: 1rem; margin: 1rem 0; font-size: 0.875rem; color: var(--color-gray-600);">
                                <span><i class="fas fa-clock"></i>
                                    <?php echo htmlspecialchars($course['duracao_texto']); ?>
                                </span>
                                <span><i class="fas fa-laptop"></i>
                                    <?php echo htmlspecialchars($course['modalidade_nome']); ?>
                                </span>
                            </div>
                            <a href="<?php echo BASE_URL; ?>/curso-detalhes.php?curso=<?php echo $course['slug']; ?>"
                                class="btn btn-primary" style="width: 100%;">
                                Saiba Mais
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div style="text-align: center; margin-top: 2.5rem;">
                <a href="<?php echo BASE_URL; ?>/cursos.php" class="btn btn-outline">
                    Ver Todos os Cursos
                </a>
            </div>
        </div>
    </section>
<?php endif; ?>

<!-- Call to Action Section -->
<section class="section"
    style="background: linear-gradient(135deg, var(--color-secondary) 0%, var(--color-secondary-light) 100%); color: white;">
    <div class="container">
        <div style="max-width: 800px; margin: 0 auto; text-align: center;">
            <h2 style="color: white; margin-bottom: 1.5rem;">Pronto para Começar sua Jornada?</h2>
            <p style="font-size: 1.2rem; margin-bottom: 2rem; color: rgba(255,255,255,0.95);">
                Venha fazer parte da FAESMA e construir um futuro de sucesso. Inscreva-se agora!
            </p>
            <a href="<?php echo BASE_URL; ?>/vestibular.php" class="btn btn-primary btn-large">
                Inscreva-se
            </a>
        </div>
    </div>
</section>

<?php
// Include footer
include __DIR__ . '/includes/footer.php';
?>