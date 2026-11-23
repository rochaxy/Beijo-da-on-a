<?php
require_once dirname(__DIR__, 2) . "/includes/bootstrap.php";
require_admin();
$orders = sql(
    "SELECT p.*,u.nome AS cliente,u.email FROM pedidos p JOIN usuarios u ON p.usuario_id=u.id ORDER BY p.id DESC",
)->fetchAll();
$id = positive_id($_GET["id"] ?? null);
$selected = null;
$items = [];
if ($id) {
    $selected = sql(
        "SELECT p.*,u.nome AS cliente FROM pedidos p JOIN usuarios u ON p.usuario_id=u.id WHERE p.id=?",
        [$id],
    )->fetch();
    if (!$selected) {
        http_response_code(404);
        exit("Pedido não encontrado.");
    }
    $items = sql("SELECT * FROM itens_pedido WHERE pedido_id=? ORDER BY id", [
        $id,
    ])->fetchAll();
}
$title = "Gestão de pedidos";
require dirname(__DIR__, 2) . "/includes/header.php";
?>
<section class="container py-5"><a href="<?= e(
    url("admin/index.php"),
) ?>">← Administração</a><h1 class="mt-3">Pedidos</h1><?php if (
    $selected
): ?><div class="summary-card my-4"><h2 class="h4">Pedido #<?= $id ?> • <?= e(
     $selected["cliente"],
 ) ?></h2><p><?= e($selected["data"]) ?> • <?= e(
     $selected["metodo_pagamento"],
 ) ?> • <?= e(
     $selected["status"],
 ) ?></p><div class="table-responsive"><table class="table"><thead><tr><th>Produto</th><th>Quantidade</th><th>Preço na compra</th><th>Subtotal</th></tr></thead><tbody><?php foreach (
    $items
    as $item
): ?><tr><td><?= e($item["nome_produto"]) ?></td><td><?= (int) $item[
    "quantidade"
] ?></td><td><?= money(cents($item["preco_unitario"])) ?></td><td><?= money(
    cents($item["preco_unitario"]) * (int) $item["quantidade"],
) ?></td></tr><?php endforeach; ?></tbody></table></div><strong>Total: <?= money(
    cents($selected["total"]),
) ?></strong></div><?php endif; ?>
<div class="table-responsive my-4"><table class="table align-middle"><caption>Histórico de compras simuladas</caption><thead><tr><th>Pedido</th><th>Cliente</th><th>Data</th><th>Pagamento</th><th>Total</th><th>Itens</th></tr></thead><tbody><?php
foreach ($orders as $order): ?><tr><td>#<?= (int) $order["id"] ?></td><td><?= e(
    $order["cliente"],
) ?><br><small><?= e($order["email"]) ?></small></td><td><?= e(
    $order["data"],
) ?></td><td><?= e(
    $order["metodo_pagamento"],
) ?></td><td class="text-nowrap"><?= money(
    cents($order["total"]),
) ?></td><td><a href="<?= e(
    url("admin/pedidos.php?id=" . $order["id"]),
) ?>">Detalhar</a></td></tr><?php endforeach;
if (
    !$orders
): ?><tr><td colspan="6">Nenhum pedido realizado.</td></tr><?php endif;
?></tbody></table></div></section>
<?php require dirname(__DIR__, 2) . "/includes/footer.php"; ?>
