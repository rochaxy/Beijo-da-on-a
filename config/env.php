<?php
declare(strict_types=1);
/** Parser intencionalmente simples: KEY=value, aspas externas e comentários de linha.
 * Variáveis do processo têm prioridade. Não expande comandos nem variáveis. */
function load_env(string $path): void
{
    if (!is_file($path)) {
        return;
    }
    foreach (file($path, FILE_IGNORE_NEW_LINES) ?: [] as $line) {
        $line = trim($line);
        if ($line === "" || str_starts_with($line, "#")) {
            continue;
        }
        if (!preg_match('/^([A-Z][A-Z0-9_]*)\s*=\s*(.*)$/', $line, $parts)) {
            throw new RuntimeException(
                "Linha inválida no arquivo de ambiente.",
            );
        }
        [, $key, $value] = $parts;
        $value = trim($value);
        if (
            strlen($value) >= 2 &&
            (($value[0] === '"' && str_ends_with($value, '"')) ||
                ($value[0] === "'" && str_ends_with($value, "'")))
        ) {
            $value = substr($value, 1, -1);
        }
        if (getenv($key) === false) {
            putenv($key . "=" . $value);
        }
    }
}
function env_value(string $key, ?string $fallback = null): string
{
    $value = getenv($key);
    if ($value !== false) {
        return $value;
    }
    if ($fallback !== null) {
        return $fallback;
    }
    throw new RuntimeException(
        "Variável de ambiente obrigatória ausente: " . $key,
    );
}
load_env(dirname(__DIR__) . "/.env");
