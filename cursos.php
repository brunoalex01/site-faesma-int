<?php
/**
 * FAESMA - Courses Listing Page
 * Display all courses with filtering options
 */

define('FAESMA_ACCESS', true);

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/Database.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/db.php';

// Page meta information
$page_title = 'Cursos';
$meta_description = 'Conheça todos os cursos de Graduação e Pós-graduação da FAESMA.';

// Get filters from query string
$filters = [];
if (isset($_GET['categoria']) && !empty($_GET['categoria'])) {
    $category = getCourseCategoriesFromView();
    foreach ($category as $cat) {
        if ($cat['nome'] === $_GET['categoria']) {
            $filters['category_id'] = $cat['id'];
            break;
        }
    }
}

if (isset($_GET['modalidade']) && !empty($_GET['modalidade'])) {
    $modalities = getCourseModalitiesFromView();
    foreach ($modalities as $mod) {
        if ($mod['nome'] === $_GET['modalidade']) {
            $filters['modality_id'] = $mod['id'];
            break;
        }
    }
}

if (isset($_GET['busca']) && !empty($_GET['busca'])) {
    $filters['search'] = sanitize($_GET['busca']);
}

// Pagination
$page = isset($_GET['pagina']) ? max(1, (int)$_GET['pagina']) : 1;
$per_page = ITEMS_PER_PAGE;
$offset = ($page - 1) * $per_page;

// Get courses from integrated view
$courses = getCoursesFromView($filters, $per_page, $offset);
$total_courses = getCourseCountFromView($filters);
$total_pages = ceil($total_courses / $per_page);

// Get filter options from integrated view
$categories = getCourseCategoriesFromView();
$modalities = getCourseModalitiesFromView();

include __DIR__ . '/includes/header.php';
?>

<!-- Page Header -->
<section style="background: linear-gradient(135deg, var(--color-primary), var(--color-primary-light)); color: white; padding: 4rem 0; text-align: center;">
    <div class="container">
        <h1 style="color: white; margin-bottom: 1rem;">Nossos Cursos</h1>
        <p style="font-size: 1.2rem; color: rgba(255,255,255,0.9);">
            Encontre o curso ideal para sua carreira
        </p>
    </div>
</section>

<!-- Filters Section -->
<section class="section section-light">
    <div class="container">
        <form id="course-filter-form" method="GET" style="background: white; padding: 2rem; border-radius: var(--radius-lg); box-shadow: var(--shadow-md);">
            <div class="grid grid-3">
                <div class="form-group">
                    <label for="category-filter" class="form-label">Categoria</label>
                    <select name="categoria" id="category-filter" class="form-control form-select">
                        <option value="">Todas as categorias</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo $cat['nome']; ?>" <?php echo (isset($_GET['categoria']) && $_GET['categoria'] === $cat['nome']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($cat['nome']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="modality-filter" class="form-label">Modalidade</label>
                    <select name="modalidade" id="modality-filter" class="form-control form-select">
                        <option value="">Todas as modalidades</option>
                        <?php foreach ($modalities as $mod): ?>
                            <option value="<?php echo $mod['nome']; ?>" <?php echo (isset($_GET['modalidade']) && $_GET['modalidade'] === $mod['nome']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($mod['nome']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="search-input" class="form-label">Buscar</label>
                    <input type="text" name="busca" id="search-input" class="form-control" 
                           placeholder="Digite o nome do curso..." 
                           value="<?php echo isset($_GET['busca']) ? htmlspecialchars($_GET['busca']) : ''; ?>">
                </div>
            </div>
            
            <div style="display: flex; gap: 1rem; margin-top: 1rem;">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search"></i> Buscar
                </button>
                <a href="<?php echo BASE_URL; ?>/cursos.php" class="btn btn-outline">
                    <i class="fas fa-redo"></i> Limpar Filtros
                </a>
            </div>
        </form>
    </div>
</section>

<!-- Courses Grid -->
<section class="section">
    <div class="container">
        <?php if (!empty($courses)): ?>
            <div style="margin-bottom: 2rem; color: var(--color-gray-600);">
                Exibindo <?php echo count($courses); ?> de <?php echo $total_courses; ?> curso<?php echo $total_courses != 1 ? 's' : ''; ?>
            </div>
            
            <div class="grid grid-3">
                <?php foreach ($courses as $course): ?>
                    <div class="card">
                        <div class="card-image">
                            <?php if ($course['imagem_destaque']): ?>
                                <img src="<?php echo UPLOADS_URL; ?>/courses/<?php echo $course['imagem_destaque']; ?>" 
                                     alt="<?php echo htmlspecialchars($course['nome']); ?>">
                            <?php endif; ?>
                            <div class="card-badge"><?php echo htmlspecialchars($course['categoria_nome']); ?></div>
                        </div>
                        <div class="card-content">
                            <h3 class="card-title"><?php echo htmlspecialchars($course['nome']); ?></h3>
                            <p class="card-text"><?php echo htmlspecialchars($course['descricao_curta']); ?></p>
                            
                            <div style="display: flex; flex-direction: column; gap: 0.5rem; margin: 1rem 0; font-size: 0.875rem; color: var(--color-gray-600);">
                                <div style="display: flex; align-items: center; gap: 0.5rem;">
                                    <i class="fas fa-clock" style="width: 20px; color: var(--color-secondary);"></i>
                                    <span><?php echo htmlspecialchars($course['duracao_texto']); ?></span>
                                </div>
                                <div style="display: flex; align-items: center; gap: 0.5rem;">
                                    <i class="fas fa-laptop" style="width: 20px; color: var(--color-secondary);"></i>
                                    <span><?php echo htmlspecialchars($course['modalidade_nome']); ?></span>
                                </div>
                                <?php if ($course['valor_mensalidade']): ?>
                                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                                        <i class="fas fa-money-bill" style="width: 20px; color: var(--color-secondary);"></i>
                                        <span><strong><?php echo formatCurrency($course['valor_mensalidade']); ?>/mês</strong></span>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <a href="<?php echo BASE_URL; ?>/curso-detalhes.php?curso=<?php echo $course['nome']; ?>" class="btn btn-primary" style="width: 100%;">
                                Saiba Mais
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
                <div class="pagination" style="display: flex; justify-content: center; gap: 0.5rem; margin-top: 3rem; flex-wrap: wrap;">
                    <?php if ($page > 1): ?>
                        <a href="?<?php echo http_build_query(array_merge($_GET, ['pagina' => $page - 1])); ?>" 
                           class="btn btn-outline btn-small">
                            <i class="fas fa-chevron-left"></i> Anterior
                        </a>
                    <?php endif; ?>
                    
                    <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                        <a href="?<?php echo http_build_query(array_merge($_GET, ['pagina' => $i])); ?>" 
                           class="btn <?php echo $i === $page ? 'btn-primary' : 'btn-outline'; ?> btn-small">
                            <?php echo $i; ?>
                        </a>
                    <?php endfor; ?>
                    
                    <?php if ($page < $total_pages): ?>
                        <a href="?<?php echo http_build_query(array_merge($_GET, ['pagina' => $page + 1])); ?>" 
                           class="btn btn-outline btn-small">
                            Próxima <i class="fas fa-chevron-right"></i>
                        </a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
            
        <?php else: ?>
            <div style="text-align: center; padding: 4rem 2rem;">
                <i class="fas fa-search" style="font-size: 4rem; color: var(--color-gray-400); margin-bottom: 1rem;"></i>
                <h3 style="color: var(--color-gray-600); margin-bottom: 1rem;">Nenhum curso encontrado</h3>
                <p style="color: var(--color-gray-500); margin-bottom: 2rem;">
                    Tente ajustar os filtros ou realizar uma nova busca.
                </p>
                <a href="<?php echo BASE_URL; ?>/cursos.php" class="btn btn-primary">
                    Ver Todos os Cursos
                </a>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- Call to Action -->
<section class="section" style="background: linear-gradient(135deg, var(--color-secondary), var(--color-secondary-light)); color: white;">
    <div class="container">
        <div style="max-width: 700px; margin: 0 auto; text-align: center;">
            <h2 style="color: white; margin-bottom: 1rem;">Encontrou o Curso Ideal?</h2>
            <p style="font-size: 1.1rem; margin-bottom: 2rem; color: rgba(255,255,255,0.95);">
                Não perca tempo! Inscreva-se agora no processo seletivo e dê o primeiro passo rumo ao seu futuro profissional.
            </p>
            <a href="<?php echo BASE_URL; ?>/vestibular.php" class="btn btn-primary btn-large">
                Inscreva-se no Vestibular
            </a>
        </div>
    </div>
</section>

<?php
include __DIR__ . '/includes/footer.php';
?>
