<?php
require_once dirname(__DIR__, 2) . "/includes/bootstrap.php";
require_admin();
$error = "";
$editing = positive_id($_GET["editar"] ?? null);
$values = [
    "id" => "",
    "nome" => "",
    "categoria_id" => "",
    "resumo" => "",
    "descricao" => "",
    "preco" => "",
    "estoque" => "0",
    "imagem" => "assets/garrafa.svg",
    "ativo" => "1",
];
if ($editing) {
    $found = get_product($editing, false);
    if (!$found) {
        http_response_code(404);
        exit("Produto não encontrado.");
    }
    $values = $found;
}
$categories = sql("SELECT id,nome FROM categorias ORDER BY nome")->fetchAll();
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    check_csrf();
    $action = post("acao");
    $id = positive_id($_POST["id"] ?? null);
    if (in_array($action, ["excluir", "desativar"], true)) {
        if ($action === "desativar") {
            sql("UPDATE produtos SET ativo = 0 WHERE id = ?", [$id]);
            flash("Produto desativado.");
        } else {
            try {
                sql("DELETE FROM produtos WHERE id = ?", [$id]);
                flash("Produto excluído.");
            } catch (PDOException $exception) {
                if ($exception->getCode() !== "23000") {
                    throw $exception;
                }
                sql("UPDATE produtos SET ativo = 0 WHERE id = ?", [$id]);
                flash(
                    "Produto vinculado a pedidos: desativado para preservar o histórico.",
                    "warning",
                );
            }
        }
        redirect("admin/produtos.php");
    }
    if ($action !== "salvar") {
        http_response_code(400);
        exit("Operação inválida.");
    }
    foreach (array_keys($values) as $key) {
        $values[$key] = post($key);
    }
    $values["id"] = $id ?: "";
    $stock = filter_var(post("estoque"), FILTER_VALIDATE_INT, [
        "options" => ["min_range" => 0, "max_range" => 1000000],
    ]);
    $category = positive_id($_POST["categoria_id"] ?? null);
    $price = str_replace(",", ".", post("preco"));
    if (
        strlen(post("nome")) < 3 ||
        strlen(post("nome")) > 150 ||
        strlen(post("resumo")) < 3 ||
        strlen(post("resumo")) > 255 ||
        strlen(post("descricao")) < 10 ||
        strlen(post("descricao")) > 10000
    ) {
        $error =
            "Verifique o nome (3–150), resumo (3–255) e descrição (10–10000 bytes).";
    } elseif (
        !preg_match('/^\d{1,8}(?:\.\d{1,2})?$/D', $price) ||
        cents($price) < 1 ||
        $stock === false
    ) {
        $error =
            "Informe preço positivo com até duas casas decimais e estoque inteiro entre 0 e 1.000.000.";
    } elseif (
        !in_array(
            $category,
            array_map("intval", array_column($categories, "id")),
            true,
        )
    ) {
        $error = "Escolha uma categoria válida.";
    } elseif (strlen(post("imagem")) > 500 || !valid_image(post("imagem"))) {
        $error = "Use uma imagem HTTPS ou um caminho dentro de assets/.";
    } else {
        $params = [
            post("nome"),
            $category,
            post("resumo"),
            post("descricao"),
            decimal(cents($price)),
            $stock,
            post("imagem"),
            post("ativo") === "1" ? 1 : 0,
        ];
        if ($id) {
            if (!get_product($id, false)) {
                http_response_code(404);
                exit("Produto não encontrado.");
            }
            $params[] = $id;
            sql(
                "UPDATE produtos SET nome=?,categoria_id=?,resumo=?,descricao=?,preco=?,estoque=?,imagem=?,ativo=? WHERE id=?",
                $params,
            );
        } else {
            sql(
                "INSERT INTO produtos (nome,categoria_id,resumo,descricao,preco,estoque,imagem,ativo) VALUES (?,?,?,?,?,?,?,?)",
                $params,
            );
        }
        flash("Produto salvo.");
        redirect("admin/produtos.php");
    }
}
$products = sql(
    "SELECT p.*,c.nome AS categoria FROM produtos p JOIN categorias c ON p.categoria_id=c.id ORDER BY p.id DESC",
)->fetchAll();
$title = "Gerenciar produtos";
require dirname(__DIR__, 2) . "/includes/header.php";
?>
<section class="container py-5"><a href="<?= e(
    url("admin/index.php"),
) ?>">← Administração</a><h1 class="mt-3">Gerenciar produtos</h1><?php if (
    $error
): ?><div class="alert alert-danger" role="alert"><?= e(
    $error,
) ?></div><?php endif; ?>
<div class="summary-card my-4"><h2 class="h4"><?= $values["id"]
    ? "Editar produto #" . (int) $values["id"]
    : "Cadastrar produto" ?></h2><form method="post" class="row g-3"><?= csrf_field() ?><input type="hidden" name="acao" value="salvar"><input type="hidden" name="id" value="<?= e(
    $values["id"],
) ?>">
<div class="col-md-8"><label class="form-label" for="name">Nome</label><input id="name" class="form-control" name="nome" required minlength="3" maxlength="150" value="<?= e(
    $values["nome"],
) ?>"></div><div class="col-md-4"><label for="category" class="form-label">Categoria</label><select class="form-select" id="category" name="categoria_id" required><option value="">Selecione</option><?php foreach (
    $categories
    as $category
): ?><option value="<?= (int) $category["id"] ?>" <?= (int) $values[
    "categoria_id"
] === (int) $category["id"]
    ? "selected"
    : "" ?>><?= e(
    $category["nome"],
) ?></option><?php endforeach; ?></select></div>
<div class="col-12"><label for="summary" class="form-label">Resumo</label><input id="summary" class="form-control" name="resumo" required minlength="3" maxlength="255" value="<?= e(
    $values["resumo"],
) ?>"></div><div class="col-12"><label for="description" class="form-label">Descrição sensorial</label><textarea id="description" class="form-control" name="descricao" required minlength="10" maxlength="10000" rows="4"><?= e(
    $values["descricao"],
) ?></textarea></div><div class="col-sm-4"><label for="price" class="form-label">Preço (R$)</label><input id="price" type="number" class="form-control" name="preco" min="0.01" max="99999999.99" step="0.01" required value="<?= e(
    $values["preco"],
) ?>"></div><div class="col-sm-4"><label for="stock" class="form-label">Estoque</label><input id="stock" type="number" class="form-control" name="estoque" min="0" max="1000000" required value="<?= e(
    $values["estoque"],
) ?>"></div><div class="col-sm-4"><label for="active" class="form-label">Status</label><select id="active" class="form-select" name="ativo"><option value="1" <?= (int) $values[
    "ativo"
] === 1
    ? "selected"
    : "" ?>>Ativo</option><option value="0" <?= (int) $values["ativo"] === 0
    ? "selected"
    : "" ?>>Inativo</option></select></div><div class="col-12"><label for="image" class="form-label">Imagem (URL HTTPS ou assets/arquivo.svg)</label><input id="image" class="form-control" name="imagem" required maxlength="500" value="<?= e(
    $values["imagem"],
) ?>"></div><div class="col-12 d-flex gap-3"><button class="btn btn-amber" type="submit">Salvar produto</button><a class="btn btn-outline-dark" href="<?= e(
    url("admin/produtos.php"),
) ?>">Cancelar / novo</a></div></form></div>
<div class="table-responsive"><table class="table align-middle"><caption>Catálogo completo, incluindo produtos inativos</caption><thead><tr><th>Produto</th><th>Categoria</th><th>Preço</th><th>Estoque</th><th>Status</th><th>Ações</th></tr></thead><tbody><?php foreach (
    $products
    as $product
): ?><tr><td><?= e($product["nome"]) ?></td><td><?= e(
    $product["categoria"],
) ?></td><td class="text-nowrap"><?= money(
    cents($product["preco"]),
) ?></td><td><?= (int) $product["estoque"] ?></td><td><?= (int) $product[
    "ativo"
]
    ? "Ativo"
    : "Inativo" ?></td><td><div class="d-flex flex-wrap gap-2"><a class="btn btn-sm btn-outline-dark" href="<?= e(
    url("admin/produtos.php?editar=" . $product["id"]),
) ?>">Editar</a><?php foreach (
    ["desativar" => "Desativar", "excluir" => "Excluir"]
    as $action => $label
): ?><form method="post" data-confirm="<?= e(
    $label,
) ?> este produto?"><?= csrf_field() ?><input type="hidden" name="id" value="<?= (int) $product[
    "id"
] ?>"><input type="hidden" name="acao" value="<?= $action ?>"><button class="btn btn-sm btn-outline-danger" type="submit"><?= $label ?></button></form><?php endforeach; ?></div></td></tr><?php endforeach; ?></tbody></table></div></section>
<?php require dirname(__DIR__, 2) . "/includes/footer.php"; ?>
