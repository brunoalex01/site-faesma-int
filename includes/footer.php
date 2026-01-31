<?php
/**
 * FAESMA - Footer Template
 * Common footer for all pages
 */

$site_settings = getAllSettings();
?>

</main>
<!-- End Main Content -->

<!-- Footer -->
<footer class="site-footer">
    <div class="footer-main">
        <div class="container">
            <div class="footer-grid">
                <!-- About Column -->
                <div class="footer-column">
                    <div class="footer-logo">
                        <img src="assets/img/FAESMA SEM FUNDO LETRA BRANCA.png" width=200px>
                    </div>
                    <p class="footer-about" style="text-align: left;">
                        Formando profissionais de excelência para o mercado de trabalho com ensino de qualidade e
                        infraestrutura moderna.
                    </p>
                    <div class="footer-social">
                        <?php if (!empty($site_settings['facebook_url'])): ?>
                            <a href="<?php echo $site_settings['facebook_url']; ?>" target="_blank" rel="noopener"
                                aria-label="Facebook">
                                <i class="fab fa-facebook-f"></i>
                            </a>
                        <?php endif; ?>
                        <?php if (!empty($site_settings['instagram_url'])): ?>
                            <a href="<?php echo $site_settings['instagram_url']; ?>" target="_blank" rel="noopener"
                                aria-label="Instagram">
                                <i class="fab fa-instagram"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Quick Links Column -->
                <div class="footer-column">
                    <h3 class="footer-title">Links Rápidos</h3>
                    <ul class="footer-links">
                        <li><a href="<?php echo BASE_URL; ?>/">Início</a></li>
                        <li><a href="<?php echo BASE_URL; ?>/cursos.php">Cursos</a></li>
                        <li><a href="<?php echo BASE_URL; ?>/sobre.php">Sobre Nós</a></li>
                        <li><a href="<?php echo BASE_URL; ?>/vestibular.php">Processo Seletivo</a></li>
                    </ul>
                </div>

                <!-- Courses Column -->
                <div class="footer-column">
                    <h3 class="footer-title">Cursos</h3>
                    <ul class="footer-links">
                        <?php
                        $categories = getCourseCategories();
                        foreach ($categories as $category):
                            ?>
                            <li>
                                <a href="<?php echo BASE_URL; ?>/cursos.php?categoria=<?php echo $category['nome']; ?>">
                                    <?php echo $category['nome']; ?>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>

                <!-- Contact Column -->
                <div class="footer-column">
                    <h3 class="footer-title">Contato</h3>
                    <ul class="footer-contact">
                        <li>
                            <i class="fas fa-map-marker-alt"></i>
                            <span>
                                <?php echo $site_settings['site_address'] ?? ''; ?>
                            </span>
                        </li>
                        <li>
                            <i class="fas fa-phone"></i>
                            <a href="tel:<?php echo preg_replace('/[^0-9]/', '', SITE_PHONE); ?>">
                                <?php echo SITE_PHONE; ?>
                            </a>
                        </li>
                        <li>
                            <i class="fas fa-envelope"></i>
                            <a href="mailto:<?php echo SITE_EMAIL; ?>">
                                <?php echo SITE_EMAIL; ?>
                            </a>
                        </li>
                        <li>
                            <i class="fa-solid fa-building-columns"></i>
                            <p>CNPJ: 56.939.781/0001-39</p>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer Bottom -->
    <div class="footer-bottom">
        <div class="container">
            <div class="footer-bottom-content">
                <p class="copyright">
                    &copy;
                    <?php echo date('Y'); ?> FAESMA - Todos os direitos reservados. - Desenvolvido por Bruno Alx
                </p>
                <ul class="footer-legal">
                    <li><a href="<?php echo BASE_URL; ?>/privacidade.php">Política de Privacidade</a></li>
                    <li><a href="<?php echo BASE_URL; ?>/termos.php">Termos de Uso</a></li>
                </ul>
            </div>
        </div>
    </div>
</footer>

<!-- Back to Top Button -->
<button id="back-to-top" class="back-to-top" aria-label="Voltar ao topo">
    <i class="fas fa-chevron-up"></i>
</button>

<!-- Main JavaScript -->
<script src="<?php echo ASSETS_URL; ?>/js/main.js"></script>

<!-- Additional JavaScript if specified -->
<?php if (isset($additional_js)): ?>
    <?php foreach ($additional_js as $js): ?>
        <script src="<?php echo $js; ?>"></script>
    <?php endforeach; ?>
<?php endif; ?>

</body>

</html>
<?php
// Flush output buffer
if (ob_get_level())
    ob_end_flush();
?>