<div style="font-family: Arial, sans-serif; background:#f4f4f4; padding:20px;">
    <div style="max-width:600px; margin:auto; background:#fff; padding:20px; border-radius:8px;">
        <h2>Bienvenido al Sistema Contable</h2>
        <p>Hola <strong><?php echo e($nombre); ?></strong>,</p>
        <p>Para activar tu cuenta y crear tu contraseña, haz clic en el botón:</p>
        <a href="<?php echo e($link); ?>" 
           style="display:inline-block; padding:10px 20px; background:#0070f3; color:#fff; border-radius:5px; text-decoration:none;">
           Activar Cuenta
        </a>
        <p>Este enlace es válido solo una vez.</p>
    </div>
</div>
<?php /**PATH /var/www/resources/views/emails/activacion.blade.php ENDPATH**/ ?>