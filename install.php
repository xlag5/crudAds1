<?php
// install.php - Página de instalação do sistema
require_once 'config.php';

$mensagem = '';
$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Conecta ao MySQL sem selecionar o banco
        $dsn = "mysql:host=" . DB_HOST . ";charset=" . DB_CHARSET;
        $pdo = new PDO($dsn, DB_USER, DB_PASS);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Cria o banco
        $pdo->exec("CREATE DATABASE IF NOT EXISTS " . DB_NAME);
        $pdo->exec("USE " . DB_NAME);
        
        // Cria as tabelas
        $tables = [
            "CREATE TABLE IF NOT EXISTS usuarios (
                id INT AUTO_INCREMENT PRIMARY KEY,
                nome VARCHAR(100) NOT NULL,
                email VARCHAR(100) UNIQUE NOT NULL,
                senha_hash VARCHAR(255) NOT NULL,
                data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )",
            
            "CREATE TABLE IF NOT EXISTS fornecedores (
                id INT AUTO_INCREMENT PRIMARY KEY,
                nome VARCHAR(255) NOT NULL,
                cnpj VARCHAR(18) UNIQUE,
                data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )",
            
            "CREATE TABLE IF NOT EXISTS produtos (
                id INT AUTO_INCREMENT PRIMARY KEY,
                nome VARCHAR(255) NOT NULL,
                preco DECIMAL(10,2) NOT NULL,
                fornecedor_id INT,
                data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (fornecedor_id) REFERENCES fornecedores(id) ON DELETE SET NULL
            )",
            
            "CREATE TABLE IF NOT EXISTS cesta (
                id INT AUTO_INCREMENT PRIMARY KEY,
                usuario_id INT NOT NULL,
                data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
            )",
            
            "CREATE TABLE IF NOT EXISTS cesta_produtos (
                id INT AUTO_INCREMENT PRIMARY KEY,
                cesta_id INT NOT NULL,
                produto_id INT NOT NULL,
                quantidade INT DEFAULT 1,
                data_adicao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (cesta_id) REFERENCES cesta(id) ON DELETE CASCADE,
                FOREIGN KEY (produto_id) REFERENCES produtos(id) ON DELETE CASCADE,
                UNIQUE KEY unique_cesta_produto (cesta_id, produto_id)
            )"
        ];

        foreach ($tables as $table) {
            $pdo->exec($table);
        }
        
        // Insere usuário admin
        $senha_hash = password_hash('admin123', PASSWORD_DEFAULT);
        $pdo->prepare("INSERT IGNORE INTO usuarios (nome, email, senha_hash) VALUES (?, ?, ?)")
             ->execute(['Administrador', 'admin@exemplo.com', $senha_hash]);
        
        $mensagem = "Sistema instalado com sucesso!<br>
                    <strong>Usuário padrão:</strong> admin@exemplo.com<br>
                    <strong>Senha:</strong> admin123<br><br>
                    <a href='login.php' class='btn btn-success'>Ir para o Login</a>";
        
    } catch (PDOException $e) {
        $erro = "Erro na instalação: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Instalação - Gestão de Produtos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .install-container {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            max-width: 600px;
            width: 100%;
        }
    </style>
</head>
<body>
    <div class="install-container">
        <h2 class="text-center mb-4">Instalação do Sistema</h2>
        
        <?php if ($mensagem): ?>
            <div class="alert alert-success">
                <?php echo $mensagem; ?>
            </div>
        <?php elseif ($erro): ?>
            <div class="alert alert-danger">
                <?php echo $erro; ?>
            </div>
        <?php else: ?>
            <div class="alert alert-info">
                <h5>Configuração do Banco de Dados</h5>
                <p><strong>Host:</strong> <?php echo DB_HOST; ?></p>
                <p><strong>Banco:</strong> <?php echo DB_NAME; ?></p>
                <p><strong>Usuário:</strong> <?php echo DB_USER; ?></p>
                <p>Clique no botão abaixo para criar o banco de dados e as tabelas necessárias.</p>
            </div>
            
            <form method="POST">
                <button type="submit" class="btn btn-primary w-100 py-2">Instalar Sistema</button>
            </form>
        <?php endif; ?>
        
        <?php if (!$mensagem && !$erro): ?>
            <div class="mt-3 text-center">
                <small class="text-muted">
                    Certifique-se de que o MySQL está rodando e as credenciais em <code>config.php</code> estão corretas.
                </small>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>