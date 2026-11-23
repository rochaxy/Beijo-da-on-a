<?php $current = user();
$cartCount = array_sum($_SESSION["cart"] ?? []);
?>
<!doctype html><html lang="pt-BR"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><meta name="description" content="Beijo da Onça: catálogo demonstrativo de cachaças artesanais, licores e presentes. Venda exclusiva para maiores de 18 anos."><title><?= e(
    $title ?? "Sabores de origem",
) ?> | Beijo da Onça</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous"><link href="<?= e(
    url("assets/style.css"),
) ?>" rel="stylesheet">
<script defer src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script><script defer src="<?= e(
    url("assets/app.js"),
) ?>"></script></head><body>
<a class="visually-hidden-focusable" href="#conteudo">Pular para o conteúdo</a><div class="age-bar">+18 • Venda exclusiva para maiores de 18 anos. Beba com moderação.</div>
<nav class="navbar navbar-expand-lg"><div class="container"><a class="navbar-brand" href="<?= e(
    url("index.php"),
) ?>">BEIJO DA ONÇA<span>ARTESANAL POR NATUREZA</span></a><button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navigation" aria-expanded="false" aria-controls="navigation" aria-label="Abrir menu"><span class="navbar-toggler-icon"></span></button><div class="collapse navbar-collapse" id="navigation"><ul class="navbar-nav ms-auto align-items-lg-center gap-lg-3"><li class="nav-item"><a class="nav-link" href="<?= e(
    url("index.php#catalogo"),
) ?>">Nossa coleção</a></li><li class="nav-item"><a class="nav-link" href="<?= e(
    url("carrinho.php"),
) ?>">Sacola (<?= (int) $cartCount ?>)</a></li><?php if (
    $current
): ?><li class="nav-item"><a class="nav-link" href="<?= e(
    url("meus-pedidos.php"),
) ?>">Meus pedidos</a></li><?php if (
    $current["perfil"] === "admin"
): ?><li class="nav-item"><a class="nav-link" href="<?= e(
    url("admin/index.php"),
) ?>">Administração</a></li><?php endif; ?><li class="nav-item"><form method="post" action="<?= e(
    url("logout.php"),
) ?>"><?= csrf_field() ?><button class="btn btn-sm btn-outline-dark" type="submit">Sair</button></form></li><?php else: ?><li class="nav-item"><a class="nav-link" href="<?= e(
    url("login.php"),
) ?>">Entrar</a></li><?php endif; ?></ul></div></div></nav>
<main id="conteudo">
<?php if (isset($_SESSION["flash"])):

    [$message, $type] = $_SESSION["flash"];
    unset($_SESSION["flash"]);
    ?><div class="container mt-4"><div class="alert alert-<?= e(
    $type,
) ?>" role="status"><?= e($message) ?></div></div><?php
endif; ?>
