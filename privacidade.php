<?php
/**
 * FAESMA - Política de Privacidade
 * Required legal information
 */

define('FAESMA_ACCESS', true);

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/Database.php';
require_once __DIR__ . '/includes/functions.php';

$page_title = 'Política de Privacidade';
$meta_description = 'Conheça a política de privacidade e proteção de dados da FAESMA - em conformidade com a LGPD.';

include __DIR__ . '/includes/header.php';
?>

<!-- Page Header -->
<section
    style="background: linear-gradient(135deg, var(--color-primary), var(--color-primary-light)); color: white; padding: 4rem 0; text-align: center;">
    <div class="container">
        <h1 style="color: white; margin-bottom: 1rem;">Política de Privacidade</h1>
        <p style="font-size: 1.2rem; color: rgba(255,255,255,0.9);">
            Proteção de dados e transparência
        </p>
    </div>
</section>

<!-- Terms Content -->
<section class="section">
    <div class="container">
        <div style="max-width: 900px; margin: 0 auto; color: var(--color-gray-800); line-height: 1.8;">
            <p style="margin-bottom: 1.5rem;">
                A **FAESMA - Faculdade Alcance de Ensino Superior do Maranhão** valoriza a privacidade dos seus alunos,
                colaboradores e visitantes. Esta política descreve como coletamos, usamos e protegemos suas informações
                pessoais em conformidade com a Lei Geral de Proteção de Dados (LGPD).
            </p>

            <h2 style="color: var(--color-primary); margin: 2rem 0 1rem; font-size: 1.5rem;">1. Coleta de Informações
            </h2>
            <p style="margin-bottom: 1rem;">
                Coletamos informações através de:
            </p>
            <ul style="margin-bottom: 1.5rem; padding-left: 2rem;">
                <li>Formulários de contato (nome, e-mail, telefone).</li>
                <li>Fichas de pré-inscrição em cursos e vestibular.</li>
                <li>Cookies de navegação para melhorar a experiência do usuário.</li>
            </ul>

            <h2 style="color: var(--color-primary); margin: 2rem 0 1rem; font-size: 1.5rem;">2. Uso dos Dados</h2>
            <p style="margin-bottom: 1rem;">
                Seus dados são utilizados exclusivamente para:
            </p>
            <ul style="margin-bottom: 1.5rem; padding-left: 2rem;">
                <li>Responder a solicitações de informação.</li>
                <li>Processar inscrições em processos seletivos.</li>
                <li>Enviar comunicados acadêmicos e novidades institucionais.</li>
                <li>Garantir a segurança dos nossos sistemas.</li>
            </ul>

            <h2 style="color: var(--color-primary); margin: 2rem 0 1rem; font-size: 1.5rem;">3. Proteção de Dados</h2>
            <p style="margin-bottom: 1.5rem;">
                Implementamos medidas de segurança técnicas e administrativas para proteger seus dados contra acessos
                não autorizados, perda ou alteração. Todos os dados coletados são armazenados em servidores seguros com
                acesso restrito.
            </p>

            <h2 style="color: var(--color-primary); margin: 2rem 0 1rem; font-size: 1.5rem;">4. Compartilhamento de
                Informações</h2>
            <p style="margin-bottom: 1.5rem;">
                A FAESMA não vende ou comercializa dados pessoais de terceiros. Informações podem ser compartilhadas com
                órgãos governamentais (MEC) por exigência legal ou com prestadores de serviços tecnológicos restritos à
                finalidade de manutenção dos nossos sistemas.
            </p>

            <h2 style="color: var(--color-primary); margin: 2rem 0 1rem; font-size: 1.5rem;">5. Seus Direitos</h2>
            <p style="margin-bottom: 1.5rem;">
                Você tem o direito de solicitar a qualquer momento o acesso, correção ou exclusão de seus dados pessoais
                dos nossos sistemas. Para isso, entre em contato através do e-mail: <a href="mailto:contato@faesma.com.br"
                    style="color: var(--color-secondary);">contato@faesma.com.br</a>.
            </p>

            <hr style="margin: 3rem 0; border: 0; border-top: 1px solid var(--color-gray-200);">

            <p style="font-size: 0.9rem; color: var(--color-gray-500); text-align: center;">
                Última atualização: 15 de Janeiro de 2026.
            </p>
        </div>
    </div>
</section>

<?php
include __DIR__ . '/includes/footer.php';
?>