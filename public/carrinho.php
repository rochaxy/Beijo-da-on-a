<?php
require_once dirname(__DIR__) . "/includes/bootstrap.php";
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    check_csrf();
    $action = post("acao");
    $id = positive_id($_POST["id"] ?? null);
    if ($action === "limpar") {
        $_SESSION["cart"] = [];
        flash("Sacola esvaziada.");
    } elseif ($action === "remover") {
        unset($_SESSION["cart"][$id]);
        flash("Item removido.");
    } elseif (in_array($action, ["adicionar", "atualizar"], true)) {
        $quantity = positive_id($_POST["quantidade"] ?? null);
        $product = get_product($id);
        $desired =
            $action === "adicionar"
                ? $quantity + (int) ($_SESSION["cart"][$id] ?? 0)
                : $quantity;
        if (!$quantity || !$product || $desired > (int) $product["estoque"]) {
            flash(
                "Quantidade inválida ou superior ao estoque disponível.",
                "danger",
            );
        } else {
            $_SESSION["cart"][$id] = $desired;
            flash("Sacola atualizada.");
        }
    } else {
        flash("Operação inválida.", "danger");
    }
    redirect("carrinho.php");
}
$rows = cart_rows();
$total = array_sum(array_column($rows, "subtotal"));
$title = "Sua sacola";
require dirname(__DIR__) . "/includes/header.php";
?>
<section class="container py-5"><p class="eyebrow text-amber">SEU PRÓXIMO BRINDE</p><h1>Sua sacola</h1><?php if (
    !$rows
): ?><p class="py-4">Sua sacola está vazia.</p><a class="btn btn-amber" href="<?= e(
    url("index.php#catalogo"),
) ?>">Explorar coleção</a><?php else: ?><div class="table-responsive my-4"><table class="table align-middle"><caption>Itens na sacola. O estoque será confirmado no checkout.</caption><thead><tr><th>Produto</th><th>Preço</th><th>Quantidade</th><th>Subtotal</th><th>Ação</th></tr></thead><tbody><?php foreach (
    $rows
    as $item
): ?><tr><td><a href="<?= e(url("produto.php?id=" . $item["id"])) ?>"><?= e(
    $item["nome"],
) ?></a><?php if (
    !(int) $item["ativo"] ||
    (int) $item["estoque"] < $item["quantidade"]
): ?><br><span class="text-danger small">Disponibilidade alterada. Ajuste ou remova este item.</span><?php endif; ?></td><td><?= money(
    cents($item["preco"]),
) ?></td><td><form method="post" class="d-flex gap-2"><?= csrf_field() ?><input type="hidden" name="acao" value="atualizar"><input type="hidden" name="id" value="<?= (int) $item[
    "id"
] ?>"><input aria-label="Quantidade de <?= e(
    $item["nome"],
) ?>" type="number" class="form-control quantity" name="quantidade" value="<?= $item[
    "quantidade"
] ?>" min="1" max="<?= max(
    1,
    (int) $item["estoque"],
) ?>" required><button class="btn btn-sm btn-outline-dark" type="submit">Atualizar</button></form></td><td><?= money(
    $item["subtotal"],
) ?></td><td><form method="post"><?= csrf_field() ?><input type="hidden" name="acao" value="remover"><input type="hidden" name="id" value="<?= (int) $item[
    "id"
] ?>"><button class="btn btn-sm btn-outline-danger" type="submit">Remover</button></form></td></tr><?php endforeach; ?></tbody></table></div><div class="d-flex flex-wrap justify-content-between gap-3"><form method="post"><?= csrf_field() ?><input type="hidden" name="acao" value="limpar"><button class="btn btn-outline-dark" type="submit">Limpar sacola</button></form><div class="text-end"><p class="fs-4">Total: <strong><?= money(
    $total,
) ?></strong></p><a class="btn btn-amber" href="<?= e(
    url("checkout.php"),
) ?>">Ir para o checkout ↗</a></div></div><?php endif; ?></section>
<?php require dirname(__DIR__) . "/includes/footer.php"; ?>
