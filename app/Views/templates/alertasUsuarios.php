<!-- ALERTAS UNIFICADAS -->
<?php
$errors  = session()->getFlashdata('errors');
$mensaje = session()->getFlashdata('success');
$error   = session()->getFlashdata('error');
?>

<?php if ($errors || $mensaje || $error): ?>
    <div class="space-y-4">

        <?php if ($errors): ?>
            <div id="alerta-error" class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative">
                <strong class="font-bold">⚠ Errores de validación:</strong>
                <ul class="list-disc pl-5 mt-2">
                    <?php foreach ($errors as $e): ?>
                        <li><?= esc($e) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <?php if ($mensaje): ?>
            <div id="alerta-mensaje" class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative transition-opacity duration-500">
                <strong class="font-bold">✔ ¡Éxito!</strong>
                <span class="block sm:inline"><?= esc($mensaje) ?></span>
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div id="alerta-error" class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative transition-opacity duration-500">
                <strong class="font-bold">⚠ Error:</strong>
                <span class="block sm:inline"><?= esc($error) ?></span>
            </div>
        <?php endif; ?>

    </div>
<?php endif; ?>
<!-- FIN ALERTAS -->