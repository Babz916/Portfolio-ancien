  <footer class="site-footer container">
    <span>&copy; <?= date('Y') ?> <?= h($profile['full_name'] ?? SITE_NAME) ?></span>
    <span>
      <?php if (!empty($profile['email'])): ?>
        <a href="mailto:<?= h($profile['email']) ?>"><?= h($profile['email']) ?></a>
      <?php endif; ?>
    </span>
  </footer>

  <script src="<?= BASE_URL ?>/assets/js/main.js"></script>
  <script>
    function googleTranslateElementInit() {
      new google.translate.TranslateElement(
        { pageLanguage: 'fr', includedLanguages: 'en', autoDisplay: false },
        'google_translate_element'
      );
    }
  </script>
  <script src="https://translate.google.com/translate_a/element.js?cb=googleTranslateElementInit" async></script>
</body>
</html>
