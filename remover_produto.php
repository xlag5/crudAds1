<?php
// remover_produto_cesta.php
session_start();
require 'db.php';

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['produto_id'])) {
    $pdo = Database::getConnection();
    $usuario_id = $_SESSION['usuario_id'];
    $produto_id = filter_var($_POST['produto_id'], FILTER_VALIDATE_INT);
    
    if ($produto_id) {
        try {
            // Buscar a cesta atual do usuário
            $stmt = $pdo->prepare("
                SELECT id FROM cesta 
                WHERE usuario_id = ? 
                ORDER BY data_criacao DESC 
                LIMIT 1
            ");
            $stmt->execute([$usuario_id]);
            $cesta = $stmt->fetch();
            
            if ($cesta) {
                // Remover o produto da cesta
                $stmt = $pdo->prepare("
                    DELETE FROM cesta_produtos 
                    WHERE cesta_id = ? AND produto_id = ?
                ");
                $stmt->execute([$cesta['id'], $produto_id]);
                
                $_SESSION['mensagem_cesta'] = "Produto removido da cesta com sucesso!";
            }
        } catch (PDOException $e) {
            $_SESSION['mensagem_cesta'] = "Erro ao remover produto: " . $e->getMessage();
        }
    }
}

header("Location: visualizar_cesta.php");
exit();
?>