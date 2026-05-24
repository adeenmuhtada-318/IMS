<?php
if (!defined('BASE_PATH')) {
    // detect depth
    $current = str_replace('\\', '/', $_SERVER['SCRIPT_FILENAME']);
    $base    = str_replace('\\', '/', realpath(__DIR__ . '/..'));
    $depth   = substr_count(str_replace($base, '', $current), '/') - 1;
    $prefix  = $depth > 0 ? str_repeat('../', $depth) : '';
}
?>
    </main>
</div>
<script src="<?= $prefix ?>assets/js/theme_controller.js"></script>
</body>
</html>
