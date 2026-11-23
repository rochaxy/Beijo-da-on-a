<?php
require_once dirname(__DIR__) . "/includes/bootstrap.php";
$customer = require_login();
$orders = sql(
    "SELECT id,data,total,metodo_pagamento FROM pedidos WHERE usuario_id = ? ORDER BY id DESC",
    [$customer["id"]],
)->fetchAll();
$title = "Meus pedidos";
require dirname(__DIR__) . "/includes/header.php";
?>
<section class="container py-5"><h1>Meus pedidos</h1><div class="table-responsive"><table class="table"><thead><tr><th>Pedido</th><th>Data</th><th>Pagamento</th><th>Total</th><th>Detalhes</th></tr></thead><tbody><?php
foreach ($orders as $order): ?><tr><td>#<?= (int) $order["id"] ?></td><td><?= e(
    $order["data"],
) ?></td><td><?= e($order["metodo_pagamento"]) ?></td><td><?= money(
    cents($order["total"]),
) ?></td><td><a href="<?= e(
    url("pedido.php?id=" . $order["id"]),
) ?>">Ver pedido</a></td></tr><?php endforeach;
if (
    !$orders
): ?><tr><td colspan="5">Você ainda não realizou pedidos.</td></tr><?php endif;
?></tbody></table></div></section>
<?php require dirname(__DIR__) . "/includes/footer.php"; ?>
