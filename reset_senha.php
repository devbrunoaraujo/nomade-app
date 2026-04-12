#!/usr/bin/env php
<?php
/**
 * Utilitário de linha de comando para redefinir senhas
 * Uso: php reset_senha.php
 *
 * Também pode ser usado para gerar hashes para inserir diretamente no banco.
 */

echo "\n=== CondoAgenda – Gerador de Senha ===\n\n";

if ($argc >= 2) {
    $senha = $argv[1];
} else {
    echo "Digite a senha desejada: ";
    $senha = trim(fgets(STDIN));
}

if (strlen($senha) < 4) {
    echo "Erro: a senha deve ter pelo menos 4 caracteres.\n";
    exit(1);
}

$hash = password_hash($senha, PASSWORD_BCRYPT, ['cost' => 10]);

echo "\nSenha:  $senha\n";
echo "Hash:   $hash\n";
echo "\nSQL para atualizar admin:\n";
echo "  UPDATE admins SET senha_hash = '$hash' WHERE usuario = 'admin';\n\n";
echo "SQL para atualizar morador (ex: Apto 101):\n";
echo "  UPDATE moradores SET senha_hash = '$hash' WHERE apartamento = '101';\n\n";

// Verifica
$ok = password_verify($senha, $hash);
echo "Verificação: " . ($ok ? "✅ OK" : "❌ FALHOU") . "\n\n";
