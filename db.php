<?php
require_once 'config.php';

class Database {
    private static $pdo;

    public static function getConnection() {
        if (!self::$pdo) {
            try {
                // Primeiro tenta conectar sem especificar o banco
                $dsn = "mysql:host=" . DB_HOST . ";charset=" . DB_CHARSET;
                self::$pdo = new PDO($dsn, DB_USER, DB_PASS);
                self::$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                
                // Tenta usar o banco, se não existir, cria
                self::$pdo->exec("CREATE DATABASE IF NOT EXISTS " . DB_NAME);
                self::$pdo->exec("USE " . DB_NAME);
                
                // Cria as tabelas se não existirem
                self::createTables();
                
            } catch (PDOException $e) {
                // Se falhar, tenta criar o banco
                if ($e->getCode() == 1049) { // Unknown database
                    self::createDatabaseAndTables();
                } else {
                    throw new PDOException($e->getMessage(), (int)$e->getCode());
                }
            }
        }
        return self::$pdo;
    }

    private static function createDatabaseAndTables() {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";charset=" . DB_CHARSET;
            self::$pdo = new PDO($dsn, DB_USER, DB_PASS);
            self::$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // Cria o banco de dados
            self::$pdo->exec("CREATE DATABASE IF NOT EXISTS " . DB_NAME);
            self::$pdo->exec("USE " . DB_NAME);
            
            // Cria as tabelas
            self::createTables();
            
        } catch (PDOException $e) {
            die("Erro crítico ao criar banco de dados: " . $e->getMessage());
        }
    }

    private static function createTables() {
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
            self::$pdo->exec($table);
        }

        // Insere alguns dados de exemplo
        self::insertSampleData();
    }

    private static function insertSampleData() {
        // Verifica se já existem usuários
        $stmt = self::$pdo->query("SELECT COUNT(*) as count FROM usuarios");
        $result = $stmt->fetch();
        
        if ($result['count'] == 0) {
            // Insere usuário admin
            $senha_hash = password_hash('admin123', PASSWORD_DEFAULT);
            self::$pdo->prepare("INSERT INTO usuarios (nome, email, senha_hash) VALUES (?, ?, ?)")
                     ->execute(['Administrador', 'admin@exemplo.com', $senha_hash]);
            
            // Insere fornecedores de exemplo
            self::$pdo->prepare("INSERT INTO fornecedores (nome, cnpj) VALUES (?, ?)")
                     ->execute(['Americanas', '12.345.678/0001-90']);
            self::$pdo->prepare("INSERT INTO fornecedores (nome, cnpj) VALUES (?, ?)")
                     ->execute(['Unipar', '98.765.432/0001-10']);
            
            // Insere produtos de exemplo
            self::$pdo->prepare("INSERT INTO produtos (nome, preco, fornecedor_id) VALUES (?, ?, ?)")
                     ->execute(['Notebook Dell', 2500.00, 1]);
            self::$pdo->prepare("INSERT INTO produtos (nome, preco, fornecedor_id) VALUES (?, ?, ?)")
                     ->execute(['Mouse Logitech', 89.90, 1]);
            self::$pdo->prepare("INSERT INTO produtos (nome, preco, fornecedor_id) VALUES (?, ?, ?)")
                     ->execute(['Teclado Mecânico', 199.90, 2]);
        }
    }
}
?>