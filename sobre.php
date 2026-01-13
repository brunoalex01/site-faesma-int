<?php
/**
 * FAESMA - Sobre a Instituição
 * History, mission, vision, and values
 */

define('FAESMA_ACCESS', true);

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/Database.php';
require_once __DIR__ . '/includes/functions.php';

$page_title = 'Sobre a FAESMA';
$meta_description = 'Conheça a história, missão, visão e os valores da FAESMA - Faculdade Alcance de Ensino Superior do Maranhão.';

include __DIR__ . '/includes/header.php';
?>

<!-- Page Header -->
<section style="background: linear-gradient(135deg, var(--color-primary), var(--color-secondary)); color: white; padding: 4rem 0; text-align: center;">
    <div class="header-logo">
        <img src="assets/img/FAESMA SEM FUNDO LETRA BRANCA.png" alt="Logo letras brancas">
    </div>
</section>

<!-- History Section -->
<section class="section">
    <div class="container">
        <div style="align-items: center;">
            <div>
                <h2 style="color: var(--color-primary); margin-bottom: 1.5rem;">Nossa História</h2>
                <div style="color: var(--color-gray-700); line-height: 1.8;">
                    <p style="margin-bottom: 1rem; text-align:justify;">
                        A Faculdade São Fidélis foi criada em 6 de abril de 2011, por meio da Portaria de Credenciamento n° 344, na cidade de São Fidélis, estado do Rio de Janeiro, constituindo-se como um marco relevante para a educação superior na região Norte fluminense.
                    </p>
                    <p style="margin-bottom: 1rem; text-align:justify;">
                        Suas atividades acadêmicas tiveram início com o curso de Enfermagem e a licenciatura em Ciências Sociais. Em 10 de maio de 2011, realizou-se a aula inaugural da primeira turma de Enfermagem, consolidando o início de sua trajetória educacional. Em 2013, a instituição obteve a autorização para oferta do curso de Nutrição e, no ano seguinte, do curso de Educação Física, ampliando seu portfólio acadêmico.
                    </p>
                    <p style="margin-bottom: 1rem; text-align:justify;">
                        O ano de 2014 foi particularmente significativo, marcado pela formatura da primeira turma de Ciências Sociais, que também representou a primeira colação de grau da Faculdade. Ainda em 2014, a instituição passou a integrar o Grupo Educacional Censupeg, momento que impulsionou sua expansão e fortalecimento acadêmico.
                    </p>
                    <p style="margin-bottom: 1rem; text-align:justify;">
                        Nesse contexto, foi protocolado o credenciamento para a modalidade de Educação a Distância (EAD), com a autorização inicial de cinco cursos: Pedagogia, Tecnólogo em Processos Gerenciais, Tecnólogo em Recursos Humanos, Tecnólogo em Logística e Tecnólogo em Gestão Ambiental. A partir daí, a instituição ampliou sua presença para outras regiões do Brasil, em especial o Sul e o Sudeste, com a oferta de cursos reconhecidos pelo Ministério da Educação (MEC).                    </p>
                    <p style="margin-bottom: 1rem; text-align:justify;">
                        Em 2024, a Faculdade São Fidélis passou por uma nova etapa de transição, com suas operações assumidas pelo Instituto Alcance. Nesse processo, adotou a denominação <b>Faculdade Alcance de Ensino Superior do Maranhão – FAESMA</b>, ampliando sua área de atuação para as regiões Nordeste e Norte do país, com presença destacada no estado do Maranhão.
                    </p>
                    <p style="margin-bottom: 1rem; text-align:justify;">
                        Atualmente, a instituição encontra-se em fase final de tramitação junto ao Ministério da Educação (MEC) para a oficialização da mudança de mantença, reafirmando seu compromisso em oferecer educação superior de excelência, voltada para o desenvolvimento regional e nacional.
                    </p>
                </div>
            </div>
            <! -- div style="position: relative;">
                <!--div
                    style="background: var(--color-accent); width: 100%; height: 400px; border-radius: var(--radius-lg); position: absolute; top: 20px; left: 20px; z-index: -1;">
                </div>
                <div
                    style="background: var(--color-gray-300); width: 100%; height: 400px; border-radius: var(--radius-lg); overflow: hidden; display: flex; align-items: center; justify-content: center;">
                    <i class="fas fa-university" style="font-size: 8rem; color: var(--color-primary);"></i -->
                    <!-- Replace with real image in production -->
                <!--/div -->
            <!-- /div -->
        </div>
    </div>
</section>

<!-- Mission, Vision, Values -->
<section class="section section-light">
    <div class="container">
        <div class="grid grid-3">
            <div
                style="background: white; padding: 2.5rem; border-radius: var(--radius-lg); box-shadow: var(--shadow-md); text-align: center;">
                <i class="fa-solid fa-school"
                    style="font-size: 3rem; color: var(--color-secondary); margin-bottom: 1.5rem;"></i>
                <h3 style="color: var(--color-primary); margin-bottom: 1rem;">Conceituada no MEC</h3>
                <p style="color: var(--color-gray-600); line-height: 1.6;">
                    Cursos autorizados no MEC com diploma igual ao presencial.
                    Acesse aqui o <a href="https://emec.mec.gov.br/emec/consulta-cadastro/detalhamento/d96957f455f6405d14c6542552b0f6eb/MTM2MzE?__cf_chl_rt_tk=xBH4pitGlCC0pPkr67niBg.BgjWDqjT0cv2VeOucbSQ-1758912394-1.0.1.1-S7BdIQrjZK74mjUBtfoQ9kgEROtp5QVSaRg68BU5F2I">e-Mec</a>.
                </p>
            </div>

            <div
                style="background: white; padding: 2.5rem; border-radius: var(--radius-lg); box-shadow: var(--shadow-md); text-align: center;">
                <i class="fa-solid fa-clock"
                    style="font-size: 3rem; color: var(--color-secondary); margin-bottom: 1.5rem;"></i>
                <h3 style="color: var(--color-primary); margin-bottom: 1rem;">Estude quando e onde quiser</h3>
                <p style="color: var(--color-gray-600); line-height: 1.6;">
                    Gerencie seu tempo e ganhe flexibilidade nos estudos.
                </p>
            </div>

            <div
                style="background: white; padding: 2.5rem; border-radius: var(--radius-lg); box-shadow: var(--shadow-md); text-align: center;">
                <i class="fas fa-star"
                    style="font-size: 3rem; color: var(--color-secondary); margin-bottom: 1.5rem;"></i>
                <h3 style="color: var(--color-primary); margin-bottom: 1rem;">Valores</h3>
                <p style="color: var(--color-gray-600); line-height: 1.6;">
                    Ética, Transparência, Inovação, Responsabilidade Social, Valorização Humana e Compromisso com a
                    Verdade.
                </p>
            </div>
        </div>
    </div>
</section>

<!-- Call to Action -->
<section class="section"
    style="background: linear-gradient(135deg, var(--color-secondary), var(--color-secondary-light)); color: white; text-align: center;">
    <div class="container">
        <h2 style="color: #ffffff; margin-bottom: 1.5rem;">Mais informações?</h2>
        <a href="<?php echo BASE_URL; ?>/contato.php" class="btn btn-primary btn-large">Fale Conosco</a>
    </div>
</section>

<?php
include __DIR__ . '/includes/footer.php';
?>