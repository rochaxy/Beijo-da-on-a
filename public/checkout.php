<?php
require_once dirname(__DIR__) . "/includes/bootstrap.php";
$customer = require_login();
if (empty($_SESSION["cart"])) {
    flash("Adicione produtos à sacola antes de continuar.", "warning");
    redirect("carrinho.php");
}
$error = "";
$method = "PIX";
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    check_csrf();
    $method = post("pagamento");
    if (!in_array($method, ["PIX", "Boleto", "Cartão de Crédito"], true)) {
        $error = "Escolha uma forma de pagamento válida.";
    } elseif (post("maioridade") !== "sim") {
        $error = "Confirme que possui 18 anos ou mais.";
    } elseif (
        !isset($_SESSION["checkout_token"]) ||
        !hash_equals($_SESSION["checkout_token"], post("checkout_token"))
    ) {
        $error =
            "Este checkout já foi processado ou expirou. Atualize a página.";
    } else {
        try {
            db()->beginTransaction();
            $locked = [];
            $total = 0;
            $cart = $_SESSION["cart"];
            ksort($cart, SORT_NUMERIC); // Ordem de bloqueio estável reduz deadlocks.
            foreach ($cart as $id => $quantity) {
                $product = sql(
                    "SELECT id,nome,preco,estoque,ativo FROM produtos WHERE id = ? FOR UPDATE",
                    [(int) $id],
                )->fetch();
                if (
                    !$product ||
                    !(int) $product["ativo"] ||
                    $quantity < 1 ||
                    (int) $product["estoque"] < $quantity
                ) {
                    throw new DomainException(
                        "O estoque de um item mudou. Revise sua sacola antes de concluir.",
                    );
                }
                $price = cents($product["preco"]);
                $total += $price * $quantity;
                if ($total > 9999999999) {
                    throw new DomainException(
                        "O valor do pedido ultrapassa o limite permitido. Reduza as quantidades.",
                    );
                }
                $locked[] = [$product, (int) $quantity, $price];
            }
            sql(
                "INSERT INTO pedidos (usuario_id,metodo_pagamento,total,status,checkout_token) VALUES (?,?,?,?,?)",
                [
                    $customer["id"],
                    $method,
                    decimal($total),
                    "confirmado_simulado",
                    $_SESSION["checkout_token"],
                ],
            );
            $orderId = (int) db()->lastInsertId();
            foreach ($locked as [$product, $quantity, $price]) {
                sql(
                    "INSERT INTO itens_pedido (pedido_id,produto_id,nome_produto,quantidade,preco_unitario) VALUES (?,?,?,?,?)",
                    [
                        $orderId,
                        $product["id"],
                        $product["nome"],
                        $quantity,
                        decimal($price),
                    ],
                );
                $updated = sql(
                    "UPDATE produtos SET estoque = estoque - ? WHERE id = ? AND estoque >= ?",
                    [$quantity, $product["id"], $quantity],
                );
                if ($updated->rowCount() !== 1) {
                    throw new DomainException(
                        "Estoque insuficiente. Nenhum valor foi cobrado.",
                    );
                }
            }
            db()->commit(); // Carrinho só é apagado depois da confirmação da transação.
            $_SESSION["cart"] = [];
            unset($_SESSION["checkout_token"]);
            redirect("pedido.php?id=" . $orderId);
        } catch (Throwable $exception) {
            if (db()->inTransaction()) {
                db()->rollBack();
            }
            if ($exception instanceof DomainException) {
                $error = $exception->getMessage();
            } else {
                error_log((string) $exception);
                $error =
                    "Não foi possível finalizar. Sua sacola foi preservada. Tente novamente.";
            }
        }
    }
}
$_SESSION["checkout_token"] ??= bin2hex(random_bytes(32));
$rows = cart_rows();
$total = array_sum(array_column($rows, "subtotal"));
$title = "Finalizar pedido";
require dirname(__DIR__) . "/includes/header.php";
?>
<section class="container py-5"><p class="eyebrow text-amber">QUASE LÁ</p><h1>Finalizar pedido</h1><?php if (
    $error
): ?><div class="alert alert-danger" role="alert"><?= e(
    $error,
) ?></div><?php endif; ?><div class="row gx-4 gy-5 mt-2"><div class="col-lg-7"><p>Comprando como <strong><?= e(
    $customer["nome"],
) ?></strong>.</p><div class="alert alert-info">Pagamento demonstrativo, com confirmação imediata. Não informe dados de cartão. Frete e entrega não fazem parte desta simulação.</div><form method="post" id="checkout-form" class="vstack gap-3"><?= csrf_field() ?><input type="hidden" name="checkout_token" value="<?= e(
    $_SESSION["checkout_token"],
) ?>"><fieldset><legend class="h5">Forma de pagamento</legend><?php foreach (
    ["PIX", "Boleto", "Cartão de Crédito"]
    as $index => $option
): ?><div class="form-check my-2"><input class="form-check-input" id="pay-<?= $index ?>" type="radio" name="pagamento" value="<?= e(
    $option,
) ?>" <?= $method === $option
    ? "checked"
    : "" ?> required><label class="form-check-label" for="pay-<?= $index ?>"><?= e(
    $option,
) ?></label></div><?php endforeach; ?></fieldset><div class="form-check"><input id="age" class="form-check-input" type="checkbox" name="maioridade" value="sim" required><label for="age" class="form-check-label">Confirmo que tenho 18 anos ou mais.</label></div><button class="btn btn-amber align-self-start" type="submit">Confirmar compra simulada</button></form></div><div class="col-lg-5"><div class="summary-card"><h2 class="h4">Resumo do pedido</h2><?php foreach (
    $rows
    as $row
): ?><div class="d-flex justify-content-between gap-3 py-2 border-bottom"><span><?= (int) $row[
    "quantidade"
] ?> × <?= e($row["nome"]) ?></span><span class="text-nowrap"><?= money(
    $row["subtotal"],
) ?></span></div><?php endforeach; ?><p class="fs-4 mt-4 mb-0">Total: <strong><?= money(
    $total,
) ?></strong></p><p class="text-muted small">Preço e disponibilidade serão validados novamente ao confirmar.</p></div></div></div></section>
<?php require dirname(__DIR__) . "/includes/footer.php"; ?>
