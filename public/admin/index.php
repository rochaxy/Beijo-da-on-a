<?php
require_once dirname(__DIR__, 2) . "/includes/bootstrap.php";
require_admin();
$products = sql("SELECT COUNT(*) AS total FROM produtos")->fetch()["total"];
$orders = sql(
    "SELECT COUNT(*) AS total, COALESCE(SUM(total),0) AS valor FROM pedidos",
)->fetch();
$title = "Administração";
require dirname(__DIR__, 2) . "/includes/header.php";
?>
<section class="container py-5"><p class="eyebrow text-amber">GESTÃO DA LOJA</p><h1>Administração</h1><div class="row g-4 my-3"><div class="col-md-4"><div class="summary-card"><h2 class="h6">Produtos</h2><p class="fs-2 mb-0"><?= (int) $products ?></p></div></div><div class="col-md-4"><div class="summary-card"><h2 class="h6">Pedidos simulados</h2><p class="fs-2 mb-0"><?= (int) $orders[
    "total"
] ?></p></div></div><div class="col-md-4"><div class="summary-card"><h2 class="h6">Valor simulado</h2><p class="fs-2 mb-0"><?= money(
    cents($orders["valor"]),
) ?></p></div></div></div><div class="d-flex gap-3"><a class="btn btn-amber" href="<?= e(
    url("admin/produtos.php"),
) ?>">Gerenciar produtos</a><a class="btn btn-outline-dark" href="<?= e(
    url("admin/pedidos.php"),
) ?>">Consultar pedidos</a></div></section>
<?php require dirname(__DIR__, 2) . "/includes/footer.php"; ?>
