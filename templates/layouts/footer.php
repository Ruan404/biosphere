<footer>
    <?php if (defined('DEBUG_TIME')): ?>

        temps d'affichage : <?= round(1000 * (microtime(true) - DEBUG_TIME)) ?> ms

    <?php endif ?>
</footer>