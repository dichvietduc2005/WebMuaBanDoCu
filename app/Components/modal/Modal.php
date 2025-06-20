<?php
function modal($id, $title, $content, $footer = '', $size = '') {
    $modalSize = $size ? "modal-$size" : '';
    ?>
    <div class="modal fade" id="<?= $id ?>" tabindex="-1">
        <div class="modal-dialog <?= $modalSize ?>">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><?= htmlspecialchars($title) ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <?= $content ?>
                </div>
                <?php if ($footer): ?>
                    <div class="modal-footer">
                        <?= $footer ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php
}