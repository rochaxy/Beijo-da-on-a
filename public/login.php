<?php
require_once dirname(__DIR__) . "/includes/bootstrap.php";
$error = "";
$email = "";
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    check_csrf();
    $email = strtolower(post("email"));
    $password = $_POST["senha"] ?? "";
    $attempts = $_SESSION["login_attempts"] ?? ["count" => 0, "time" => time()];
    if (time() - $attempts["time"] > 900) {
        $attempts = ["count" => 0, "time" => time()];
    }
    if ($attempts["count"] >= 10) {
        $error = "Muitas tentativas nesta sessão. Aguarde 15 minutos.";
    } else {
        $account = sql("SELECT id,senha FROM usuarios WHERE email = ?", [
            $email,
        ])->fetch();
        // Hash fictício mantém a verificação de custo mesmo para e-mail inexistente.
        $hash =
            $account["senha"] ??
            '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2uheWG/igi.';
        if (
            is_string($password) &&
            strlen($password) <= 72 &&
            password_verify($password, $hash) &&
            $account
        ) {
            session_regenerate_id(true);
            $_SESSION["user_id"] = (int) $account["id"];
            unset($_SESSION["login_attempts"]);
            $_SESSION["csrf"] = bin2hex(random_bytes(32));
            flash("Bem-vindo! Você já pode finalizar sua compra.");
            redirect(!empty($_SESSION["cart"]) ? "checkout.php" : "index.php");
        }
        $attempts["count"]++;
        $_SESSION["login_attempts"] = $attempts;
        $error = "E-mail ou senha inválidos.";
    }
}
$title = "Entrar";
require dirname(__DIR__) . "/includes/header.php";
?>
<section class="container py-5"><div class="auth-card mx-auto"><p class="eyebrow text-amber">BOM TER VOCÊ POR AQUI</p><h1>Entre na sua conta</h1><?php if (
    $error
): ?><div class="alert alert-danger" role="alert"><?= e(
    $error,
) ?></div><?php endif; ?><form method="post" class="vstack gap-3"><?= csrf_field() ?><div><label for="email" class="form-label">E-mail</label><input id="email" class="form-control" type="email" name="email" required maxlength="254" autocomplete="email" value="<?= e(
    $email,
) ?>"></div><div><label for="password" class="form-label">Senha</label><input id="password" class="form-control" type="password" name="senha" required maxlength="72" autocomplete="current-password"></div><button class="btn btn-amber" type="submit">Entrar</button><a href="<?= e(
    url("cadastro.php"),
) ?>">Ainda não tem conta? Cadastre-se</a></form></div></section>
<?php require dirname(__DIR__) . "/includes/footer.php"; ?>
