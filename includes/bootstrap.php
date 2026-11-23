<?php
declare(strict_types=1);
require_once dirname(__DIR__) . "/config/database.php";
date_default_timezone_set("America/Sao_Paulo");
ini_set("display_errors", "0");
ini_set("session.use_strict_mode", "1");
session_set_cookie_params([
    "lifetime" => 0,
    "path" => "/",
    "secure" => env_value("SESSION_SECURE", "false") === "true",
    "httponly" => true,
    "samesite" => "Lax",
]);
session_start();
header("X-Content-Type-Options: nosniff");
header("X-Frame-Options: DENY");
header("Referrer-Policy: strict-origin-when-cross-origin");
header("Cache-Control: no-store");
header(
    "Content-Security-Policy: default-src 'self'; script-src 'self' https://cdn.jsdelivr.net; style-src 'self' https://cdn.jsdelivr.net; img-src 'self' https:; object-src 'none'; base-uri 'self'; form-action 'self'; frame-ancestors 'none'",
);
set_exception_handler(function (Throwable $error): void {
    error_log((string) $error);
    http_response_code(500);
    echo '<!doctype html><html lang="pt-BR"><meta charset="utf-8"><title>Serviço indisponível</title><h1>Não foi possível concluir a operação.</h1><p>Tente novamente em instantes. Verifique a configuração do banco na instalação.</p></html>';
});
function e(mixed $value): string
{
    return htmlspecialchars(
        (string) $value,
        ENT_QUOTES | ENT_SUBSTITUTE,
        "UTF-8",
    );
}
function url(string $path = ""): string
{
    return rtrim(env_value("APP_BASE", ""), "/") . "/" . ltrim($path, "/");
}
function redirect(string $path): void
{
    header("Location: " . url($path), true, 303);
    exit();
}
function money(int $cents): string
{
    return 'R$ ' . number_format($cents / 100, 2, ",", ".");
}
/** Valores monetários são calculados como centavos inteiros, evitando erros de float. */
function cents(string $decimal): int
{
    if (!preg_match('/^(\d{1,8})(?:\.(\d{1,2}))?$/D', $decimal, $match)) {
        throw new InvalidArgumentException("Preço inválido.");
    }
    return (int) $match[1] * 100 + (int) str_pad($match[2] ?? "", 2, "0");
}
function decimal(int $value): string
{
    return intdiv($value, 100) .
        "." .
        str_pad((string) ($value % 100), 2, "0", STR_PAD_LEFT);
}
function csrf(): string
{
    return $_SESSION["csrf"] ??= bin2hex(random_bytes(32));
}
function csrf_field(): string
{
    return '<input type="hidden" name="csrf" value="' . e(csrf()) . '">';
}
function check_csrf(): void
{
    if (
        !is_string($_POST["csrf"] ?? null) ||
        !hash_equals(csrf(), $_POST["csrf"])
    ) {
        http_response_code(419);
        exit(
            "Sessão do formulário expirada. Atualize a página e tente novamente."
        );
    }
}
function post(string $key): string
{
    $value = $_POST[$key] ?? "";
    return is_string($value) ? trim($value) : "";
}
function positive_id(mixed $value): int
{
    return filter_var($value, FILTER_VALIDATE_INT, [
        "options" => ["min_range" => 1, "max_range" => 2147483647],
    ]) ?:
        0;
}
function flash(string $message, string $type = "success"): void
{
    $_SESSION["flash"] = [$message, $type];
}
function user(): ?array
{
    static $loaded = false,
        $current = null;
    if (!$loaded) {
        $loaded = true;
        if (isset($_SESSION["user_id"])) {
            $current =
                sql(
                    "SELECT id, nome, email, perfil FROM usuarios WHERE id = ?",
                    [$_SESSION["user_id"]],
                )->fetch() ?:
                null;
        }
    }
    return $current;
}
function require_login(): array
{
    $current = user();
    if (!$current) {
        flash("Entre na sua conta para continuar.", "warning");
        redirect("login.php");
    }
    return $current;
}
function require_admin(): array
{
    $current = require_login();
    if ($current["perfil"] !== "admin") {
        http_response_code(403);
        exit("Acesso restrito à administração.");
    }
    return $current;
}
function get_product(int $id, bool $active = true): array|false
{
    return sql(
        "SELECT p.*, c.nome AS categoria FROM produtos p JOIN categorias c ON c.id = p.categoria_id WHERE p.id = ?" .
            ($active ? " AND p.ativo = 1" : ""),
        [$id],
    )->fetch();
}
function cart_rows(): array
{
    $rows = [];
    foreach ($_SESSION["cart"] ?? [] as $id => $quantity) {
        $product = get_product((int) $id, false);
        if ($product) {
            $product["quantidade"] = (int) $quantity;
            $product["subtotal"] = cents($product["preco"]) * (int) $quantity;
            $rows[] = $product;
        } else {
            unset($_SESSION["cart"][$id]);
        }
    }
    return $rows;
}
function image_url(string $path): string
{
    if (
        preg_match('~^assets/[a-zA-Z0-9_./-]+$~D', $path) &&
        !str_contains($path, "..")
    ) {
        return url($path);
    }
    if (
        filter_var($path, FILTER_VALIDATE_URL) &&
        strtolower((string) parse_url($path, PHP_URL_SCHEME)) === "https"
    ) {
        return $path;
    }
    return url("assets/garrafa.svg");
}
function valid_image(string $path): bool
{
    return (bool) (preg_match('~^assets/[a-zA-Z0-9_./-]+$~D', $path) &&
        !str_contains($path, "..")) ||
        (filter_var($path, FILTER_VALIDATE_URL) &&
            strtolower((string) parse_url($path, PHP_URL_SCHEME)) === "https");
}
