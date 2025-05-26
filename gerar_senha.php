<?php
// Script para gerar e atualizar hashes de senhas no MySQL via terminal
require_once __DIR__ . '/config/config.php';

$senha_admin = "admin123";  // Nova senha do administrador
$senha_usuario = "user123"; // Nova senha do usuário normal

$hash_admin = password_hash($senha_admin, PASSWORD_DEFAULT);
$hash_usuario = password_hash($senha_usuario, PASSWORD_DEFAULT);

echo "Hash para admin (senha: $senha_admin): " . $hash_admin . PHP_EOL;
echo "Hash para usuario (senha: $senha_usuario): " . $hash_usuario . PHP_EOL . PHP_EOL;

// SQL que será executado
echo "SQL para atualização de usuários:" . PHP_EOL;
echo "UPDATE usuarios SET password = '$hash_admin' WHERE username = 'admin';" . PHP_EOL;
echo "UPDATE usuarios SET password = '$hash_usuario' WHERE username = 'usuario';" . PHP_EOL . PHP_EOL;

// Agora vamos realmente atualizar as senhas no banco de dados
try {
    $pdo = getConnection();
    
    // Atualiza as senhas no banco
    $stmt = $pdo->prepare("UPDATE usuarios SET password = ? WHERE username = ?");
    $admin_result = $stmt->execute([$hash_admin, 'admin']);
    $user_result = $stmt->execute([$hash_usuario, 'usuario']);
    
    // Verifica os resultados das atualizações
    if ($admin_result) {
        echo "✅ Senha do admin atualizada com sucesso!" . PHP_EOL;
    } else {
        echo "❌ Falha ao atualizar senha do admin" . PHP_EOL;
    }
    
    if ($user_result) {
        echo "✅ Senha do usuario atualizada com sucesso!" . PHP_EOL;
    } else {
        echo "❌ Falha ao atualizar senha do usuario" . PHP_EOL;
    }
    
} catch (PDOException $e) {
    echo "Erro ao atualizar senhas: " . $e->getMessage() . PHP_EOL;
}
