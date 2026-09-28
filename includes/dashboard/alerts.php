<div class="max-w-6xl mx-auto">
    <?php
    $flash_types = ['success' => 'emerald', 'error' => 'red', 'warning' => 'amber'];
    foreach ($flash_types as $type => $color) {
        if (isset($_SESSION['flash_' . $type])): ?>
            <div class="mb-6 p-4 border rounded-xl text-sm animate-pulse
                <?php if ($type == 'success') echo 'bg-emerald-900/20 border-emerald-800 text-emerald-400'; ?>
                <?php if ($type == 'error') echo 'bg-red-900/20 border-red-800 text-red-400'; ?>
                <?php if ($type == 'warning') echo 'bg-amber-900/20 border-amber-800 text-amber-400'; ?>">
                <?= string_sanitize($_SESSION['flash_' . $type]); unset($_SESSION['flash_' . $type]); ?>
            </div>
        <?php endif;
    } ?>
    <div id="ajax-message-container"></div>
</div>