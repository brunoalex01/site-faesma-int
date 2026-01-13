<?php
/**
 * FAESMA - Vestibular Page
 */

define('FAESMA_ACCESS', true);

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/Database.php';
require_once __DIR__ . '/includes/functions.php';

$page_title = 'Vestibular e Inscrições';
$meta_description = 'Processo seletivo da FAESMA. Inscreva-se e realize seu sonho de cursar ensino superior.';

// Get pre-selected course if any
$selected_course = null;
if (isset($_GET['curso'])) {
    $selected_course = getCourse(sanitize($_GET['curso']), 'slug');
}

include __DIR__ . '/includes/header.php';
?>

<!-- Page Header -->
<section
    style="background: linear-gradient(135deg, var(--color-secondary), var(--color-secondary-light)); color: white; padding: 5rem 0; text-align: center;">
    <div class="container">
        <h1 style="color: white; margin-bottom: 1rem; font-size: clamp(2rem, 5vw, 3.5rem);">Processo Seletivo 2026</h1>
        <p style="font-size: 1.3rem; color: rgba(255,255,255,0.95); margin-bottom: 2rem;">
            Realize seu sonho de cursar ensino superior na FAESMA
        </p>
        <a href="#inscricao" class="btn btn-primary btn-large">
            <i class="fas fa-pen"></i> Inscreva-se Agora
        </a>
    </div>
</section>

<!-- How it Works -->
<section class="section">
    <div class="container">
        <div style="text-align: center; margin-bottom: 3rem;">
            <h2 style="color: var(--color-primary);">Como Funciona</h2>
            <h4 style="color: var(--color-secondary); font-size: 1.1rem;">Processo de inscrição nos cursos de Graduação e Pós-Graduação</h4>
        </div>

        <div class="grid grid-4">
            <div style="text-align: center;">
                <div
                    style="width: 80px; height: 80px; margin: 0 auto 1rem; background: linear-gradient(135deg, var(--color-primary), var(--color-primary-light)); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 2rem; font-weight: 700; color: white;">
                    1
                </div>
                <h4 style="color: var(--color-primary); margin-bottom: 0.5rem;">Inscrição Online</h4>
                <p style="color: var(--color-secondary); font-size: 0.9rem;">Preencha o formulário de inscrição com seus
                    dados</p>
            </div>

            <div style="text-align: center;">
                <div
                    style="width: 80px; height: 80px; margin: 0 auto 1rem; background: linear-gradient(135deg, var(--color-primary), var(--color-primary-light)); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 2rem; font-weight: 700; color: white;">
                    2
                </div>
                <h4 style="color: var(--color-primary); margin-bottom: 0.5rem;">Matrícula</h4>
                <p style="color: var(--color-secondary); font-size: 0.9rem;">Efetue sua matrícula e comece a estudar</p>
            </div>
        </div>
    </div>
</section>

<!-- Enrollment Form -->
<section id="inscricao" class="section">
    <div class="container">
        <div style="max-width: 700px; margin: 0 auto;">
            <div style="text-align: center; margin-bottom: 2.5rem;">
                <h2 style="color: var(--color-primary);">Faça sua Pré-Inscrição</h2>
                <p style="color: var(--color-secondary); font-size: 1.1rem;">Preencha o formulário abaixo para iniciar
                    seu processo</p>
            </div>

            <form method="POST" action="processar-inscricao.php" class="validate-form"
                style="background: white; padding: 2.5rem; border-radius: var(--radius-lg); box-shadow: var(--shadow-xl);">
                <h3 style="color: var(--color-primary); margin-bottom: 1.5rem; font-size: 1.2rem;">Dados Pessoais</h3>

                <div class="form-group">
                    <label for="nome" class="form-label">Nome Completo *</label>
                    <input type="text" id="nome" name="nome" class="form-control" required>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label for="cpf" class="form-label">CPF *</label>
                        <input type="text" id="cpf" name="cpf" class="form-control" data-mask="cpf" required>
                    </div>

                    <div class="form-group">
                        <label for="data_nascimento" class="form-label">Data de Nascimento *</label>
                        <input type="date" id="data_nascimento" name="data_nascimento" class="form-control" required>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label for="email" class="form-label">E-mail *</label>
                        <input type="email" id="email" name="email" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label for="celular" class="form-label">Celular *</label>
                        <input type="tel" id="celular" name="celular" class="form-control" required>
                    </div>
                </div>

                <h3 style="color: var(--color-primary); margin: 2rem 0 1.5rem; font-size: 1.2rem;">Curso de Interesse
                </h3>

                <div class="form-group">
                    <label for="curso" class="form-label">Curso *</label>
                    <select id="curso" name="curso_id" class="form-control form-select" required>
                        <option value="">Selecione um curso</option>
                        <?php
                        $all_courses = getCourses(['status' => 'ativo']);
                        $last_category = '';
                        foreach ($all_courses as $course):
                            if ($last_category !== $course['categoria_nome']):
                                if ($last_category !== '')
                                    echo '</optgroup>';
                                echo '<optgroup label="' . htmlspecialchars($course['categoria_nome']) . '">';
                                $last_category = $course['categoria_nome'];
                            endif;
                            ?>
                            <option value="<?php echo $course['id']; ?>" <?php echo ($selected_course && $selected_course['id'] === $course['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($course['nome']); ?>
                            </option>
                            <?php
                        endforeach;
                        if ($last_category !== '')
                            echo '</optgroup>';
                        ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="modalidade" class="form-label">Forma de Ingresso *</label>
                    <select id="modalidade" name="forma_ingresso" class="form-control form-select" required>
                        <option value="">Selecione</option>
                        <option value="Prova FAESMA">Prova do Vestibular FAESMA</option>
                        <option value="Nota ENEM">Nota do ENEM</option>
                        <option value="Transferência">Transferência de outra instituição</option>
                    </select>
                </div>

                <div class="form-group">
                    <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                        <input type="checkbox" name="termos" required>
                        <span>Declaro que li e aceito os <a href="<?php echo BASE_URL; ?>/termos.php" target="_blank"
                                style="color: var(--color-secondary);">termos e condições</a> *</span>
                    </label>
                </div>

                <button type="submit" class="btn btn-primary btn-large" style="width: 100%; margin-top: 1rem;">
                    <i class="fas fa-check"></i> Finalizar Pré-Inscrição
                </button>

                <p style="text-align: center; margin-top: 1.5rem; color: var(--color-gray-600); font-size: 0.9rem;">
                    * Campos obrigatórios
                </p>
            </form>
        </div>
    </div>
</section>

<!-- Benefits -->
<section class="section"
    style="background: linear-gradient(135deg, var(--color-primary), var(--color-primary-light)); color: white;">
    <div class="container">
        <h2 style="color: white; text-align: center; margin-bottom: 3rem;">Por que Escolher a FAESMA?</h2>

        <div class="grid grid-3">
            <div style="text-align: center;">
                <i class="fas fa-award" style="font-size: 3rem; margin-bottom: 1rem; color: var(--color-accent);"></i>
                <h4 style="color: white; margin-bottom: 0.5rem;">Qualidade Reconhecida</h4>
                <p style="color: rgba(255,255,255,0.9); font-size: 0.95rem;">Cursos autorizados pelo MEC
                </p>
            </div>

            <div style="text-align: center;">
                <i class="fas fa-chalkboard-teacher"
                    style="font-size: 3rem; margin-bottom: 1rem; color: var(--color-accent);"></i>
                <h4 style="color: white; margin-bottom: 0.5rem;">Os melhores conteúdos</h4>
                <p style="color: rgba(255,255,255,0.9); font-size: 0.95rem;">Conteúdos fornecidos pela Plataforma A+, a melhor plataforma de conteúdo EAD</p>
            </div>

            <div style="text-align: center;">
                <i class="fas fa-hand-holding-usd"
                    style="font-size: 3rem; margin-bottom: 1rem; color: var(--color-accent);"></i>
                <h4 style="color: white; margin-bottom: 0.5rem;">Estude no tempo que puder</h4>
                <p style="color: rgba(255,255,255,0.9); font-size: 0.95rem;">Os melhores conteúdos disponíveis em nossa plataforma para você estudar quando quiser
                </p>
            </div>
        </div>
    </div>
</section>

<?php
include __DIR__ . '/includes/footer.php';
?>