<?php
// bildirim.php - Oturumdaki tek seferlik bildirimi (toast) gösterir.
// Çağıran sayfa $bildirim değişkenini bildirimAl() ile doldurur.
if (!empty($bildirim)):
    $hataMi = $bildirim['tur'] === 'hata'; ?>
    <div class="toast<?php echo $hataMi ? ' hata' : ''; ?>" role="<?php echo $hataMi ? 'alert' : 'status'; ?>" data-toast>
        <i class="bi <?php echo $hataMi ? 'bi-exclamation-triangle-fill' : 'bi-check2-circle'; ?> toast-ikon" aria-hidden="true"></i>
        <div>
            <strong><?php echo $hataMi ? 'Dikkat' : 'Başarılı'; ?></strong>
            <span><?php echo e($bildirim['mesaj']); ?></span>
        </div>
        <button type="button" class="toast-kapat" aria-label="Bildirimi kapat" data-toast-kapat>
            <i class="bi bi-x-lg" aria-hidden="true"></i>
        </button>
    </div>
<?php endif; ?>
