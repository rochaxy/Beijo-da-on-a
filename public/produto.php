<?php
require_once dirname(__DIR__) . "/includes/bootstrap.php";
$product = get_product(positive_id($_GET["id"] ?? null));
if (!$product) {
    http_response_code(404);
    $title = "Produto não encontrado";
    require dirname(__DIR__) . "/includes/header.php";
    echo '<div class="container py-5"><h1>Produto não encontrado.</h1></div>';
    require dirname(__DIR__) . "/includes/footer.php";
    exit();
}
$title = $product["nome"];
require dirname(__DIR__) . "/includes/header.php";
?>
<section class="container py-5"><a href="<?= e(
    url("index.php#catalogo"),
) ?>">← Voltar à coleção</a><div class="row gx-4 gy-5 align-items-center mt-2"><div class="col-md-6"><img class="detail-image" src="<?= e(
    image_url($product["imagem"]),
) ?>" alt="<?= e(
    $product["nome"],
) ?>" width="600" height="650"></div><div class="col-md-6"><p class="eyebrow text-amber"><?= e(
     $product["categoria"],
 ) ?></p><h1><?= e(
    $product["nome"],
) ?></h1><p class="text-muted pre-line"><?= e(
    $product["descricao"],
) ?></p><p class="price fs-2"><?= money(
    cents($product["preco"]),
) ?></p><p><?= (int) $product["estoque"] > 0
    ? "Disponível • " . (int) $product["estoque"] . " unidades em estoque"
    : "Produto esgotado" ?></p>
<?php if ((int) $product["estoque"] > 0): ?><form method="post" action="<?= e(
    url("carrinho.php"),
) ?>" class="d-flex flex-wrap gap-3 align-items-end"><?= csrf_field() ?><input type="hidden" name="acao" value="adicionar"><input type="hidden" name="id" value="<?= (int) $product[
    "id"
] ?>"><div><label class="form-label" for="quantity">Quantidade</label><input id="quantity" class="form-control quantity" type="number" name="quantidade" value="1" min="1" max="<?= (int) $product[
    "estoque"
] ?>" required></div><button class="btn btn-amber" type="submit">Adicionar à sacola</button></form><?php endif; ?><p class="small text-muted mt-4">Venda exclusiva para maiores de 18 anos.</p></div></div></section>
<?php require dirname(__DIR__) . "/includes/footer.php"; ?>
