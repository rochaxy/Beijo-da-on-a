<?php
require_once dirname(__DIR__) . "/includes/bootstrap.php";
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    header("Allow: POST");
    exit();
}
check_csrf();
unset($_SESSION["user_id"], $_SESSION["checkout_token"]);
session_regenerate_id(true);
$_SESSION["csrf"] = bin2hex(random_bytes(32));
flash("Você saiu da sua conta.");
redirect("index.php");
