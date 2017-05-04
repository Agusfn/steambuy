<?php
$usuarios_total = $mysql->fetch_value("SELECT COUNT(*) FROM `users`");
$usuarios_24hs = $mysql->fetch_value("SELECT COUNT(*) FROM `users` WHERE `register_date` > (NOW() - INTERVAL 24 HOUR)");
$usuarios_7day = $mysql->fetch_value("SELECT COUNT(*) FROM `users` WHERE `register_date` > (NOW() - INTERVAL 7 DAY)");
$usuarios_month = $mysql->fetch_value("SELECT COUNT(*) FROM `users` WHERE MONTH(`register_date`) = ".date("m")." AND YEAR(`register_date`)=".date("Y"));


$usuarios_activos_24hs = $mysql->fetch_value("SELECT COUNT(*) FROM `users` WHERE `last_visit_date` > (NOW() - INTERVAL 24 HOUR)");
$usuarios_activos_7day = $mysql->fetch_value("SELECT COUNT(*) FROM `users` WHERE `last_visit_date` > (NOW() - INTERVAL 7 DAY)");
$usuarios_activos_month = $mysql->fetch_value("SELECT COUNT(*) FROM `users` WHERE MONTH(`last_visit_date`) = ".date("m")." AND YEAR(`last_visit_date`)=".date("Y"));

?>
<div class="clearfix">
    <table style="font-size:17px;">
    <thead style="font-size:15px;"><th></th><th>Total</th><th>Referidos</th></thead>
    <col width="600">
    <col width="125">
    <tr>
        <td style="padding-top:10px;">Usuarios totales:</td>
        <td><?php echo $usuarios_total; ?></td>
        <td>XXX</td>
    </tr>
    <tr>
        <td>Usuarios nuevos 24hs:</td>
        <td><?php echo $usuarios_24hs; ?></td>
        <td>XXX</td>
    </tr>
    <tr>
        <td>Usuarios nuevos 7 días:</td>
        <td><?php echo $usuarios_7day; ?></td>
        <td>XXX</td>
    </tr>
    <tr>
        <td>Usuarios nuevos este mes:</td>
        <td><?php echo $usuarios_month; ?></td>
        <td>XXX</td>
    </tr>
    <tr>
        <td style="padding-top:20px">Usuarios activos 24hs:</td>
        <td><?php echo $usuarios_activos_24hs; ?></td>
    </tr>
    <tr>
        <td>Usuarios activos 7 dias:</td>
        <td><?php echo $usuarios_activos_7day; ?></td>
    </tr>
    <tr>
        <td>Usuarios activos este mes:</td>
        <td><?php echo $usuarios_activos_month; ?></td>
    </tr>
    </table>
    
    
    <div class="list-group" style="margin-top:40px;text-align:center;">
      	<a href="#" class="list-group-item">Ver listado de usuarios</a>
        <a href="#" class="list-group-item">Ver info referidos</a>
		<a href="#" class="list-group-item">Ver banlist</a>
    </div>
</div>