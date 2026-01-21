<?php
/**
 * FAESMA - Contact Page
 * Contact form and information
 */

define('FAESMA_ACCESS', true);

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/Database.php';
require_once __DIR__ . '/includes/functions.php';

// Page meta information
$page_title = 'Contato';
$meta_description = 'Entre em contato com a FAESMA. Estamos prontos para atender você.';

// Form submission handling
$success = false;
$error = null;
$form_data = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get and sanitize form data
    $form_data = [
        'nome' => sanitize($_POST['nome'] ?? ''),
        'email' => sanitize($_POST['email'] ?? ''),
        'telefone' => sanitize($_POST['telefone'] ?? ''),
        'assunto' => sanitize($_POST['assunto'] ?? ''),
        'mensagem' => sanitize($_POST['mensagem'] ?? '')
    ];
    
    // Validation
    $errors = [];
    
    if (empty($form_data['nome'])) {
        $errors['nome'] = 'Nome é obrigatório';
    }
    
    if (empty($form_data['email']) || !validateEmail($form_data['email'])) {
        $errors['email'] = 'E-mail válido é obrigatório';
    }
    
    if (empty($form_data['mensagem'])) {
        $errors['mensagem'] = 'Mensagem é obrigatória';
    }
    
    // If no errors, save to database
    if (empty($errors)) {
        if (saveContact($form_data)) {
            $success = true;
            $form_data = []; // Clear form
        } else {
            $error = 'Erro ao enviar mensagem. Por favor, tente novamente.';
        }
    }
}

include __DIR__ . '/includes/header.php';
?>

<!-- Page Header -->
<section style="background: linear-gradient(135deg, var(--color-primary), var(--color-primary-light)); color: white; padding: 4rem 0; text-align: center;">
    <div class="container">
        <h1 style="color: white; margin-bottom: 1rem;">Entre em Contato</h1>
        <p style="font-size: 1.2rem; color: rgba(255,255,255,0.9);">
            Estamos prontos para atender você, mande sua mensagem
        </p>
    </div>
</section>

<!-- Contact Section -->
<section class="section">
    <div class="container">
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 3rem;">
            <!-- Contact Form -->
            <div>
                <h2 style="color: var(--color-primary); margin-bottom: 1.5rem;">Envie uma Mensagem</h2>
                
                <?php if ($success): ?>
                    <div style="background: #d4edda; border: 1px solid #c3e6cb; color: #155724; padding: 1rem; border-radius: var(--radius-md); margin-bottom: 1.5rem;">
                        <i class="fas fa-check-circle"></i> Mensagem enviada com sucesso! Entraremos em contato em breve.
                    </div>
                <?php endif; ?>
                
                <?php if ($error): ?>
                    <div style="background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; padding: 1rem; border-radius: var(--radius-md); margin-bottom: 1.5rem;">
                        <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                    </div>
                <?php endif; ?>
                
                <form method="POST" class="validate-form" style="background: white; padding: 2rem; border-radius: var(--radius-lg); box-shadow: var(--shadow-md);">
                    <div class="form-group">
                        <label for="nome" class="form-label">Nome Completo *</label>
                        <input type="text" id="nome" name="nome" class="form-control" required 
                               value="<?php echo htmlspecialchars($form_data['nome'] ?? ''); ?>">
                        <?php if (isset($errors['nome'])): ?>
                            <div class="form-error"><?php echo $errors['nome']; ?></div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="form-group">
                        <label for="email" class="form-label">E-mail *</label>
                        <input type="email" id="email" name="email" class="form-control" required 
                               value="<?php echo htmlspecialchars($form_data['email'] ?? ''); ?>">
                        <?php if (isset($errors['email'])): ?>
                            <div class="form-error"><?php echo $errors['email']; ?></div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="form-group">
                        <label for="telefone" class="form-label">Telefone</label>
                        <input type="tel" id="telefone" name="telefone" class="form-control" 
                               placeholder="(98) 98765-4321"
                               value="<?php echo htmlspecialchars($form_data['telefone'] ?? ''); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="assunto" class="form-label">Assunto</label>
                        <select id="assunto" name="assunto" class="form-control form-select">
                            <option value="">Selecione um assunto</option>
                            <option value="Informações sobre Cursos" <?php echo (isset($form_data['assunto']) && $form_data['assunto'] === 'Informações sobre Cursos') ? 'selected' : ''; ?>>Informações sobre Cursos</option>
                            <option value="Processo Seletivo" <?php echo (isset($form_data['assunto']) && $form_data['assunto'] === 'Processo Seletivo') ? 'selected' : ''; ?>>Processo Seletivo</option>
                            <option value="Matrícula" <?php echo (isset($form_data['assunto']) && $form_data['assunto'] === 'Matrícula') ? 'selected' : ''; ?>>Matrícula</option>
                            <option value="Financeiro" <?php echo (isset($form_data['assunto']) && $form_data['assunto'] === 'Financeiro') ? 'selected' : ''; ?>>Financeiro</option>
                            <option value="Outros" <?php echo (isset($form_data['assunto']) && $form_data['assunto'] === 'Outros') ? 'selected' : ''; ?>>Outros</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="mensagem" class="form-label">Mensagem *</label>
                        <textarea id="mensagem" name="mensagem" class="form-control" rows="6" required><?php echo htmlspecialchars($form_data['mensagem'] ?? ''); ?></textarea>
                        <?php if (isset($errors['mensagem'])): ?>
                            <div class="form-error"><?php echo $errors['mensagem']; ?></div>
                        <?php endif; ?>
                    </div>
                    
                    <button type="submit" class="btn btn-primary btn-large" style="width: 100%;">
                        <i class="fas fa-paper-plane"></i> Enviar Mensagem
                    </button>
                </form>
            </div>
            
            <!-- Contact Information -->
            <div>
                <h2 style="color: var(--color-primary); margin-bottom: 1.5rem;">Informações de Contato</h2>
                
                <!-- Contact Cards -->
                <div style="display: flex; flex-direction: column; gap: 1.5rem;">
                    <!-- Address -->
                    <div style="background: white; padding: 1.5rem; border-radius: var(--radius-lg); box-shadow: var(--shadow-md); display: flex; gap: 1rem;">
                        <div style="width: 50px; height: 50px; background: linear-gradient(135deg, var(--color-primary), var(--color-primary-light)); border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                            <i class="fas fa-map-marker-alt" style="color: white; font-size: 1.2rem;"></i>
                        </div>
                        <div>
                            <h4 style="color: var(--color-primary); margin-bottom: 0.5rem;">Endereço</h4>
                            <p style="color: var(--color-secondary); margin: 0;">
                                <?php echo $site_settings['site_address'] ?? ''; ?>
                            </p>
                        </div>
                    </div>
                    
                    <!-- Phone -->
                    <div style="background: white; padding: 1.5rem; border-radius: var(--radius-lg); box-shadow: var(--shadow-md); display: flex; gap: 1rem;">
                        <div style="width: 50px; height: 50px; background: linear-gradient(135deg, var(--color-primary), var(--color-primary-light)); border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                            <i class="fas fa-phone" style="color: white; font-size: 1.2rem;"></i>
                        </div>
                        <div>
                            <h4 style="color: var(--color-primary); margin-bottom: 0.5rem;">Telefone</h4>
                            <p style="color: var(--color-gray-600); margin: 0;">
                                <a href="https://wa.me/98988487847" style="color: var(--color-secondary);"><?php echo SITE_PHONE; ?></a>
                            </p>
                        </div>
                    </div>
                    
                    <!-- Email -->
                    <div style="background: white; padding: 1.5rem; border-radius: var(--radius-lg); box-shadow: var(--shadow-md); display: flex; gap: 1rem;">
                        <div style="width: 50px; height: 50px; background: linear-gradient(135deg, var(--color-primary), var(--color-primary-light)); border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                            <i class="fas fa-envelope" style="color: white; font-size: 1.2rem;"></i>
                        </div>
                        <div>
                            <h4 style="color: var(--color-primary); margin-bottom: 0.5rem;">E-mail</h4>
                            <p style="color: var(--color-gray-600); margin: 0;">
                                <a href="mailto:<?php echo SITE_EMAIL; ?>" style="color: var(--color-secondary);"><?php echo SITE_EMAIL; ?></a>
                            </p>
                        </div>
                    </div>
                </div>
                
                <!-- Social Media -->
                <div style="margin-top: 2rem; background: var(--color-gray-100); padding: 1.5rem; border-radius: var(--radius-lg); text-align: center;">
                    <h4 style="color: var(--color-primary); margin-bottom: 1rem;">Siga-nos nas Redes Sociais</h4>
                    <div style="display: flex; justify-content: center; gap: 1rem;">
                        <a href="#" style="width: 45px; height: 45px; background: var(--color-primary); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-size: 1.2rem;">
                            <i class="fab fa-facebook-f"></i>
                        </a>
                        <a href="https://www.instagram.com/faculdadefaesma" style="width: 45px; height: 45px; background: var(--color-primary); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-size: 1.2rem;">
                            <i class="fab fa-instagram"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php
include __DIR__ . '/includes/footer.php';
?>
