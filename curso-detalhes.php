<?php
/**
 * FAESMA - Course Details Page
 * Display detailed information about a specific course
 */

define('FAESMA_ACCESS', true);

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/Database.php';
require_once __DIR__ . '/includes/functions.php';

// Get course identifier from URL (pode ser slug ou nome)
$course_identifier = isset($_GET['curso']) ? sanitize($_GET['curso']) : '';

if (empty($course_identifier)) {
    header('Location: ' . BASE_URL . '/cursos.php');
    exit;
}

// Get course details - tenta primeiro por slug, depois por nome
$course = getCourse($course_identifier, 'slug');

if (!$course) {
    // Se não encontrou por slug, tenta por nome
    $course = getCourse($course_identifier, 'nome');
}

if (!$course) {
    header('Location: ' . BASE_URL . '/cursos.php');
    exit;
}

// Get curriculum
$curriculum = getCourseCurriculum($course['id']);

// Get related courses (same category)
$related_courses = getCourses(['category_id' => $course['category_id'], 'status' => 'ativo'], 3);

// Page meta information
$page_title = $course['nome'];
$meta_description = $course['descricao_curta'] ?? generateMetaDescription($course['descricao_completa']);

include __DIR__ . '/includes/header.php';
?>

<!-- Page Header -->
<section
    style="background: linear-gradient(135deg, var(--color-primary), var(--color-primary-light)); color: white; padding: 3rem 0;">
    <div class="container">
        <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 1rem; flex-wrap: wrap;">
            <a href="<?php echo BASE_URL; ?>/cursos.php" style="color: rgba(255,255,255,0.8); font-size: 0.9rem;">
                <i class="fas fa-arrow-left"></i> Voltar para Cursos
            </a>
            <span style="color: rgba(255,255,255,0.5);">|</span>
            <span style="color: rgba(255,255,255,0.8); font-size: 0.9rem;">
                <?php echo htmlspecialchars($course['categoria_nome']); ?>
            </span>
        </div>
        <h1 style="color: white; margin-bottom: 1rem;">
            <?php echo htmlspecialchars($course['nome']); ?>
        </h1>
        <p style="font-size: 1.2rem; color: rgba(255,255,255,0.9); margin-bottom: 1.5rem;">
            <?php echo htmlspecialchars($course['descricao_curta']); ?>
        </p>

        <div style="display: flex; gap: 2rem; flex-wrap: wrap; font-size: 0.95rem;">
            <div style="display: flex; align-items: center; gap: 0.5rem;">
                <i class="fas fa-clock" style="color: var(--color-accent);"></i>
                <span>Duração: <strong>
                        <?php echo htmlspecialchars($course['duracao_texto']); ?>
                    </strong></span>
            </div>
            <div style="display: flex; align-items: center; gap: 0.5rem;">
                <i class="fas fa-laptop" style="color: var(--color-accent);"></i>
                <span>Modalidade: <strong>
                        <?php echo htmlspecialchars($course['modalidade_nome']); ?>
                    </strong></span>
            </div>
            <?php if ($course['carga_horaria']): ?>
                <div style="display: flex; align-items: center; gap: 0.5rem;">
                    <i class="fas fa-book" style="color: var(--color-accent);"></i>
                    <span>Carga Horária: <strong>
                            <?php echo number_format($course['carga_horaria']); ?>h
                        </strong></span>
                </div>
            <?php endif; ?>
            <?php if ($course['valor_mensalidade']): ?>
                <div style="display: flex; align-items: center; gap: 0.5rem;">
                    <i class="fas fa-money-bill" style="color: var(--color-accent);"></i>
                    <span>A partir de: <strong>
                            <?php echo formatCurrency($course['valor_mensalidade']); ?>/mês
                        </strong></span>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- Course Content -->
<section class="section">
    <div class="container">
        <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 3rem;">
            <!-- Main Content -->
            <div>
                <!-- Description -->
                <?php if ($course['descricao_completa']): ?>
                    <div
                        style="background: white; padding: 2rem; border-radius: var(--radius-lg); box-shadow: var(--shadow-md); margin-bottom: 2rem;">
                        <h2
                            style="color: var(--color-primary); margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.5rem;">
                            <i class="fas fa-info-circle" style="color: var(--color-secondary);"></i>
                            Sobre o Curso
                        </h2>
                        <div style="color: var(--color-gray-700); line-height: 1.8;">
                            <?php echo nl2br(htmlspecialchars($course['descricao_completa'])); ?>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Objectives -->
                <?php if ($course['objetivos']): ?>
                    <div
                        style="background: white; padding: 2rem; border-radius: var(--radius-lg); box-shadow: var(--shadow-md); margin-bottom: 2rem;">
                        <h2
                            style="color: var(--color-primary); margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.5rem;">
                            <i class="fas fa-bullseye" style="color: var(--color-secondary);"></i>
                            Objetivos
                        </h2>
                        <div style="color: var(--color-gray-700); line-height: 1.8;">
                            <?php echo nl2br(htmlspecialchars($course['objetivos'])); ?>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Graduate Profile -->
                <?php if ($course['perfil_egresso']): ?>
                    <div
                        style="background: white; padding: 2rem; border-radius: var(--radius-lg); box-shadow: var(--shadow-md); margin-bottom: 2rem;">
                        <h2
                            style="color: var(--color-primary); margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.5rem;">
                            <i class="fas fa-user-graduate" style="color: var(--color-secondary);"></i>
                            Perfil do Egresso
                        </h2>
                        <div style="color: var(--color-gray-700); line-height: 1.8;">
                            <?php echo nl2br(htmlspecialchars($course['perfil_egresso'])); ?>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Job Market -->
                <?php if ($course['mercado_trabalho']): ?>
                    <div
                        style="background: white; padding: 2rem; border-radius: var(--radius-lg); box-shadow: var(--shadow-md); margin-bottom: 2rem;">
                        <h2
                            style="color: var(--color-primary); margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.5rem;">
                            <i class="fas fa-briefcase" style="color: var(--color-secondary);"></i>
                            Mercado de Trabalho
                        </h2>
                        <div style="color: var(--color-gray-700); line-height: 1.8;">
                            <?php echo nl2br(htmlspecialchars($course['mercado_trabalho'])); ?>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Curriculum -->
                <?php if (!empty($curriculum)): ?>
                    <div
                        style="background: white; padding: 2rem; border-radius: var(--radius-lg); box-shadow: var(--shadow-md);">
                        <h2
                            style="color: var(--color-primary); margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.5rem;">
                            <i class="fas fa-list" style="color: var(--color-secondary);"></i>
                            Grade Curricular
                        </h2>

                        <?php foreach ($curriculum as $semester => $subjects): ?>
                            <div style="margin-bottom: 1.5rem;">
                                <h3
                                    style="color: var(--color-secondary); font-size: 1.1rem; margin-bottom: 1rem; padding-bottom: 0.5rem; border-bottom: 2px solid var(--color-gray-200);">
                                    <?php echo $semester; ?>º Semestre
                                </h3>
                                <ul style="list-style: none; padding: 0;">
                                    <?php foreach ($subjects as $subject): ?>
                                        <li
                                            style="padding: 0.75rem; border-left: 3px solid var(--color-accent); background: var(--color-gray-50); margin-bottom: 0.5rem; display: flex; justify-content: space-between; align-items: center;">
                                            <span style="color: var(--color-gray-800); font-weight: 500;">
                                                <?php echo htmlspecialchars($subject['disciplina']); ?>
                                            </span>
                                            <?php if ($subject['carga_horaria']): ?>
                                                <span style="color: var(--color-gray-600); font-size: 0.875rem;">
                                                    <?php echo $subject['carga_horaria']; ?>h
                                                </span>
                                            <?php endif; ?>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Sidebar -->
            <div>
                <!-- Enrollment CTA -->
                <div
                    style="background: linear-gradient(135deg, var(--color-secondary), var(--color-secondary-light)); color: white; padding: 2rem; border-radius: var(--radius-lg); box-shadow: var(--shadow-lg); margin-bottom: 2rem; text-align: center;">
                    <i class="fas fa-graduation-cap"
                        style="font-size: 3rem; margin-bottom: 1rem; color: var(--color-accent);"></i>
                    <h3 style="color: white; margin-bottom: 1rem;">Inscreva-se Agora!</h3>
                    <p style="color: rgba(255,255,255,0.9); margin-bottom: 1.5rem; font-size: 0.95rem;">
                        Dêo primeiro passo para transformar seu futuro
                    </p>
                    <?php 
                        // Usar link_oferta se disponível, caso contrário usar vestibular
                        $enrollment_link = !empty($course['link_oferta']) 
                            ? $course['link_oferta'] 
                            : BASE_URL . '/vestibular.php?curso=' . $course['slug'];
                        $target_attr = !empty($course['link_oferta']) ? ' target="_blank" rel="noopener noreferrer"' : '';
                    ?>
                    <a href="<?php echo htmlspecialchars($enrollment_link); ?>"<?php echo $target_attr; ?>
                        class="btn btn-primary btn-large" style="width: 100%;">
                        <i class="fas fa-pen"></i> Fazer Inscrição
                    </a>
                </div>

                <!-- Course Info -->
                <div
                    style="background: white; padding: 2rem; border-radius: var(--radius-lg); box-shadow: var(--shadow-md); margin-bottom: 2rem;">
                    <h3 style="color: var(--color-primary); margin-bottom: 1.5rem; font-size: 1.1rem;">Informações do
                        Curso</h3>
                    <ul style="list-style: none; padding: 0;">
                        <li
                            style="padding: 1rem 0; border-bottom: 1px solid var(--color-gray-200); display: flex; justify-content: space-between;">
                            <span style="color: var(--color-gray-600);">Categoria:</span>
                            <strong style="color: var(--color-primary);">
                                <?php echo htmlspecialchars($course['categoria_nome']); ?>
                            </strong>
                        </li>
                        <li
                            style="padding: 1rem 0; border-bottom: 1px solid var(--color-gray-200); display: flex; justify-content: space-between;">
                            <span style="color: var(--color-gray-600);">Modalidade:</span>
                            <strong style="color: var(--color-primary);">
                                <?php echo htmlspecialchars($course['modalidade_nome']); ?>
                            </strong>
                        </li>
                        <li
                            style="padding: 1rem 0; border-bottom: 1px solid var(--color-gray-200); display: flex; justify-content: space-between;">
                            <span style="color: var(--color-gray-600);">Duração:</span>
                            <strong style="color: var(--color-primary);">
                                <?php echo htmlspecialchars($course['duracao_texto']); ?>
                            </strong>
                        </li>
                        <?php if ($course['carga_horaria']): ?>
                            <li
                                style="padding: 1rem 0; border-bottom: 1px solid var(--color-gray-200); display: flex; justify-content: space-between;">
                                <span style="color: var(--color-gray-600);">Carga Horária:</span>
                                <strong style="color: var(--color-primary);">
                                    <?php echo number_format($course['carga_horaria']); ?>h
                                </strong>
                            </li>
                        <?php endif; ?>
                        <?php if ($course['coordenador']): ?>
                            <li
                                style="padding: 1rem 0; border-bottom: 1px solid var(--color-gray-200); display: flex; justify-content: space-between;">
                                <span style="color: var(--color-gray-600);">Coordenador:</span>
                                <strong style="color: var(--color-primary);">
                                    <?php echo htmlspecialchars($course['coordenador']); ?>
                                </strong>
                            </li>
                        <?php endif; ?>
                        <?php if ($course['vagas_disponiveis']): ?>
                            <li style="padding: 1rem 0; display: flex; justify-content: space-between;">
                                <span style="color: var(--color-gray-600);">Vagas:</span>
                                <strong style="color: var(--color-secondary);">
                                    <?php echo $course['vagas_disponiveis']; ?> vagas
                                </strong>
                            </li>
                        <?php endif; ?>
                    </ul>
                </div>

                <!-- Contact -->
                <div
                    style="background: var(--color-gray-100); padding: 1.5rem; border-radius: var(--radius-lg); text-align: center;">
                    <i class="fas fa-phone-alt"
                        style="font-size: 2rem; color: var(--color-secondary); margin-bottom: 1rem;"></i>
                    <h4 style="color: var(--color-primary); font-size: 1rem; margin-bottom: 0.5rem;">Dúvidas?</h4>
                    <p style="color: var(--color-gray-600); font-size: 0.9rem; margin-bottom: 1rem;">Entre em contato
                        conosco</p>
                    <a href="<?php echo BASE_URL; ?>/contato.php" class="btn btn-outline btn-small"
                        style="width: 100%;">
                        Fale Conosco
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Related Courses -->
<?php if (!empty($related_courses) && count($related_courses) > 1): ?>
    <section class="section section-light">
        <div class="container">
            <h2 style="color: var(--color-primary); margin-bottom: 2rem; text-align: center;">Cursos Relacionados</h2>
            <div class="grid grid-3">
                <?php foreach ($related_courses as $related): ?>
                    <?php if ($related['id'] !== $course['id']): ?>
                        <div class="card">
                            <div class="card-content">
                                <h3 class="card-title">
                                    <?php echo htmlspecialchars($related['nome']); ?>
                                </h3>
                                <p class="card-text">
                                    <?php echo truncateText(htmlspecialchars($related['descricao_curta']), 100); ?>
                                </p>
                                <a href="<?php echo BASE_URL; ?>/curso-detalhes.php?curso=<?php echo $related['slug']; ?>"
                                    class="btn btn-outline btn-small">
                                    Ver Detalhes
                                </a>
                            </div>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
<?php endif; ?>

<?php
include __DIR__ . '/includes/footer.php';
?>