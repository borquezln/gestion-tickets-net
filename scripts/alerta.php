<?php

function alerta($mensaje, $icon = "success")
{ ?>
    <script>
        Swal.fire({
            position: 'bottom-end',
            icon: <?= $icon ?>,
            title: <?= $mensaje ?>,
            showConfirmButton: false,
            timer: 1500
        })
    </script>
<?php
}
