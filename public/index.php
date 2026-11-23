<?php
require_once dirname(__DIR__) . "/includes/bootstrap.php";
$title = "Cachaças, histórias e encontros";
$search = is_string($_GET["q"] ?? null) ? substr(trim($_GET["q"]), 0, 100) : "";
$category = positive_id($_GET["categoria"] ?? null);
$categories = sql("SELECT id,nome FROM categorias ORDER BY nome")->fetchAll();
$query =
    "SELECT p.*, c.nome AS categoria FROM produtos p JOIN categorias c ON p.categoria_id = c.id WHERE p.ativo = 1";
$params = [];
if ($search !== "") {
    $query .= " AND (p.nome LIKE ? OR p.descricao LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}
if ($category) {
    $query .= " AND p.categoria_id = ?";
    $params[] = $category;
}
$products = sql($query . " ORDER BY p.id", $params)->fetchAll();
require dirname(__DIR__) . "/includes/header.php";
?>
<section class="store-hero"><div class="container"><div class="row align-items-center g-4"><div class="col-md-7"><p class="eyebrow">DA NOSSA TERRA, PARA O SEU BRINDE</p><h1>O tempo apura.<br>A origem encanta.</h1><p class="hero-copy">Cachaças artesanais, kits para presente e acessórios da Beijo da Onça. Descubra os sabores da nossa coleção.</p><a href="#catalogo" class="btn btn-amber mt-3">Explore os sabores ↗</a></div><div class="col-md-5"><img class="hero-bottle" src="<?= e(
    url("assets/garrafa.svg"),
) ?>" width="600" height="650" alt="Ilustração de garrafa da coleção Beijo da Onça"></div></div></div></section>
<section id="catalogo" class="container py-5"><div class="d-flex flex-wrap justify-content-between gap-3 align-items-end mb-4"><div><p class="eyebrow text-amber">NOSSA COLEÇÃO</p><h2>Escolha seu próximo brinde.</h2></div><span class="text-muted small"><?= count(
    $products,
) ?> produtos encontrados</span></div>
<form method="get" class="row g-3 mb-4"><div class="col-md-6"><label for="q" class="form-label">Buscar por nome ou descrição</label><input id="q" name="q" class="form-control" maxlength="100" value="<?= e(
    $search,
) ?>" placeholder="Experimente: amburana"></div><div class="col-md-4"><label for="category" class="form-label">Categoria</label><select id="category" name="categoria" class="form-select"><option value="">Todas as categorias</option><?php foreach (
    $categories
    as $cat
): ?><option value="<?= (int) $cat["id"] ?>" <?= $category === (int) $cat["id"]
    ? "selected"
    : "" ?>><?= e(
    $cat["nome"],
) ?></option><?php endforeach; ?></select></div><div class="col-md-2 d-flex align-items-end"><button class="btn btn-dark w-100" type="submit">Filtrar</button></div></form>
<div class="row g-4"><?php
foreach (
    $products
    as $product
): ?><div class="col-sm-6 col-lg-3"><article class="card product-card h-100"><a href="<?= e(
    url("produto.php?id=" . $product["id"]),
) ?>"><img src="<?= e(
    image_url($product["imagem"]),
) ?>" class="card-img-top" width="600" height="650" alt="<?= e(
    $product["nome"],
) ?>" loading="lazy"></a><div class="card-body d-flex flex-column"><p class="product-category"><?= e(
     $product["categoria"],
 ) ?></p><h3 class="h5"><a href="<?= e(
    url("produto.php?id=" . $product["id"]),
) ?>"><?= e(
    $product["nome"],
) ?></a></h3><p class="text-muted small flex-grow-1"><?= e(
    $product["resumo"],
) ?></p><p class="price"><?= money(
    cents($product["preco"]),
) ?></p><a class="btn btn-outline-dark" href="<?= e(
    url("produto.php?id=" . $product["id"]),
) ?>"><?= (int) $product["estoque"] > 0
    ? "Conhecer produto ↗"
    : "Ver produto • esgotado" ?></a></div></article></div><?php endforeach;
if (!$products): ?><p>Nenhum produto encontrado. <a href="<?= e(
    url("index.php"),
) ?>">Limpar filtros</a></p><?php endif;
?></div><p class="text-muted small mt-4">Catálogo acadêmico com fotos de Beijo da Onça. Estoque e compras simulados.</p></section>
<?php require dirname(__DIR__) . "/includes/footer.php"; ?>
