<!-- ============================================================
     CINERATE — footer.php
     Rodapé global incluído em todas as páginas.
     Responsabilidades:
       - Apresentar informação de copyright
       - Carregar o JavaScript principal (main.js)
       - Fechar correctamente as tags <body> e <html>
     ============================================================ -->

<footer class="site-footer">
    <!-- Texto de copyright — o ano está fixo conforme o projecto -->
    <p>&copy; 2026 CineRate &mdash; Programação de Sistemas Web</p>
</footer>

<!-- ============================================================
     SCRIPTS JAVASCRIPT
     Os scripts são carregados no fim do <body> (antes de </body>)
     e não no <head>, por duas razões:
       1. O HTML é renderizado primeiro — a página aparece mais depressa
       2. O DOM já existe quando o JS corre — não é necessário esperar
          pelo evento DOMContentLoaded para encontrar os elementos
     ============================================================ -->

<!-- Script principal: notificações, feed AJAX, validação de forms, toggle de tema -->
<script src="/cinerate/assets/js/main.js"></script>

</body>
</html>