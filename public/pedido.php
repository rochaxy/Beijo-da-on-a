<?php
require_once dirname(__DIR__) . "/includes/bootstrap.php";
$customer = require_login();
$id = positive_id($_GET["id"] ?? null);
$order = sql("SELECT * FROM pedidos WHERE id = ? AND usuario_id = ?", [
    $id,
    $customer["id"],
])->fetch();
if (!$order) {
    http_response_code(404);
    exit("Pedido não encontrado.");
}
$items = sql("SELECT * FROM itens_pedido WHERE pedido_id = ? ORDER BY id", [
    $id,
])->fetchAll();
$title = "Pedido #" . $id;
require dirname(__DIR__) . "/includes/header.php";
?>
<section class="container py-5"><p class="eyebrow text-amber">PEDIDO #<?= $id ?></p><h1>Seu brinde está confirmado.</h1><div class="alert alert-success">Compra simulada concluída. Nenhuma cobrança real foi realizada.</div><p><?= e(
    $order["data"],
) ?> • <?= e($order["metodo_pagamento"]) ?> • <?= e(
     $order["status"],
 ) ?></p><div class="table-responsive"><table class="table"><thead><tr><th>Produto</th><th>Quantidade</th><th>Preço na compra</th><th>Subtotal</th></tr></thead><tbody><?php foreach (
    $items
    as $item
): ?><tr><td><?= e($item["nome_produto"]) ?></td><td><?= (int) $item[
    "quantidade"
] ?></td><td><?= money(cents($item["preco_unitario"])) ?></td><td><?= money(
    cents($item["preco_unitario"]) * (int) $item["quantidade"],
) ?></td></tr><?php endforeach; ?></tbody></table></div><p class="fs-4">Total: <strong><?= money(
    cents($order["total"]),
) ?></strong></p><a class="btn btn-amber" href="<?= e(
    url("index.php"),
) ?>">Voltar à coleção</a></section>
<?php require dirname(__DIR__) . "/includes/footer.php"; ?>
