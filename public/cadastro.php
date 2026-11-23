<?php
require_once dirname(__DIR__) . "/includes/bootstrap.php";
$error = "";
$name = "";
$email = "";
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    check_csrf();
    $name = post("nome");
    $email = strtolower(post("email"));
    $password = $_POST["senha"] ?? "";
    if (
        strlen($name) < 2 ||
        strlen($name) > 100 ||
        !filter_var($email, FILTER_VALIDATE_EMAIL) ||
        strlen($email) > 254 ||
        !is_string($password) ||
        strlen($password) < 8 ||
        strlen($password) > 72 ||
        str_contains($password, "\0")
    ) {
        $error = "Informe nome, e-mail válido e senha entre 8 e 72 bytes.";
    } elseif (post("maioridade") !== "sim") {
        $error = "É necessário ter 18 anos ou mais para criar uma conta.";
    } else {
        try {
            sql(
                "INSERT INTO usuarios (nome,email,senha,perfil) VALUES (?,?,?,?)",
                [
                    $name,
                    $email,
                    password_hash($password, PASSWORD_BCRYPT),
                    "cliente",
                ],
            );
            flash("Cadastro realizado. Entre com sua conta.");
            redirect("login.php");
        } catch (PDOException $exception) {
            if ($exception->getCode() !== "23000") {
                throw $exception;
            }
            $error =
                "Não foi possível cadastrar este e-mail. Utilize outro endereço ou entre na sua conta.";
        }
    }
}
$title = "Criar conta";
require dirname(__DIR__) . "/includes/header.php";
?>
<section class="container py-5"><div class="auth-card mx-auto"><p class="eyebrow text-amber">BEM-VINDO À COLEÇÃO</p><h1>Criar conta</h1><?php if (
    $error
): ?><div class="alert alert-danger" role="alert"><?= e(
    $error,
) ?></div><?php endif; ?><form method="post" class="vstack gap-3"><?= csrf_field() ?><div><label for="name" class="form-label">Nome</label><input id="name" class="form-control" name="nome" required minlength="2" maxlength="100" autocomplete="name" value="<?= e(
    $name,
) ?>"></div><div><label for="email" class="form-label">E-mail</label><input id="email" class="form-control" type="email" name="email" required maxlength="254" autocomplete="email" value="<?= e(
    $email,
) ?>"></div><div><label for="password" class="form-label">Senha (8 a 72 bytes)</label><input id="password" class="form-control" type="password" name="senha" minlength="8" maxlength="72" autocomplete="new-password" required></div><div class="form-check"><input id="age" class="form-check-input" type="checkbox" name="maioridade" value="sim" required><label for="age" class="form-check-label">Declaro ter 18 anos ou mais.</label></div><button class="btn btn-amber" type="submit">Criar minha conta</button><a href="<?= e(
    url("login.php"),
) ?>">Já tenho uma conta</a></form></div></section>
<?php require dirname(__DIR__) . "/includes/footer.php"; ?>
