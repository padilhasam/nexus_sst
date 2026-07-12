
    </div>
  </section>
</div>
<nav class="mobile-bottom">
  <a class="active" href="<?= BASE_URL ?>/dashboard"><i class="fa-solid fa-house"></i><span>Dashboard</span></a>
  <a href="<?= BASE_URL ?>/visitas"><i class="fa-regular fa-calendar-check"></i><span>Visitas</span></a>
  <a
    href="<?= BASE_URL ?>/agenda/criar"
    class="mobile-add-button"
    aria-label="Novo agendamento"
>
    <i class="fa-solid fa-plus"></i>
</a>
  <a href="<?= BASE_URL ?>/mensagens" class="with-dot"><i class="fa-regular fa-message"></i><span>Mensagens</span></a>
  <a href="<?= BASE_URL ?>/usuarios"><i class="fa-regular fa-user"></i><span>Perfil</span></a>
</nav>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<?php if (!empty($js) && is_array($js)): foreach ($js as $arquivoJs): ?><script src="<?= BASE_URL ?>/js/pages/<?= $arquivoJs ?>"></script><?php endforeach; endif; ?>
<script src="<?= BASE_URL ?>/js/core/app.js?v=<?= time() ?>"></script>
</body>
</html>
