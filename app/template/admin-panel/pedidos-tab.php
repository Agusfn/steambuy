<?php
$active_orders = $mysql->fetch_value("SELECT COUNT(*) FROM `orders` WHERE `order_status`=1");
$sent_orders_24hs = $mysql->fetch_value("SELECT COUNT(*) FROM `orders` WHERE `order_status`=2 AND `order_status_change` > (NOW() - INTERVAL 1 DAY)");
$canceled_orders_24hs = $mysql->fetch_value("SELECT COUNT(*) FROM `orders` WHERE `order_status`=3 AND `order_status_change` > (NOW() - INTERVAL 1 DAY)");
$lim_discount_active_orders = $mysql->fetch_value("SELECT COUNT(*) FROM `orders` WHERE `order_status`=1 AND `product_limited_discount`=1 AND `order_reserved_game`=0 AND (`order_confirmed_payment`=1 OR `order_informedpayment`=1)");

?>
<div class="clearfix">
    <table style="float:left;font-size:18px;">
    <col width="350">
    <tr>
        <td>Pedidos activos:</td>
        <td><?php echo $active_orders; ?></td>
    </tr>
    <tr>
        <td>Pedidos concretados en últ. 24hs:</td>
        <td><?php echo $sent_orders_24hs; ?></td>
    </tr>
    <tr>
        <td>Pedidos cancelados en últ. 24hs:</td>
        <td><?php echo $canceled_orders_24hs; ?></td>
    </tr>
       <tr>
        <td style="padding-top:20px;">Pedidos pendientes p/ reservar:</td>
        <td><?php echo $lim_discount_active_orders; ?></td></td>
    </tr> 
    </table>
    
    
    <div class="list-group" style="float:right;width: 380px;margin: 10px 50px 0 0;">
      <a href="pedidos/" class="list-group-item">Ver listado de pedidos</a>
      <a href="pedidos/cargar-pago.php" class="list-group-item">Cargar pago manualmente</a>
      <a href="pedidos/pagos-anteriores.php" class="list-group-item">Ver pagos días anteriores (p/ sheets)</a>
    </div>
</div>