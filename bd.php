<?php
/**
 * ========================================================================
 * SISTEMA CERBERO - Conexão Segura com Banco de Dados via PDO
 * ========================================================================
 * Regra: "um arquivo para conexão com o banco (bd.php)"
 * Regra: "Use sempre o mesmo PDO para conexão e queries no MySQL,
 *         utilizando consultas preparadas para evitar SQL Injection."
 */

// Parâmetros de Configuração do Banco de Dados
// Modifique aqui caso esteja utilizando InfinityFree, XAMPP ou outro provedor:
define('DB_HOST', getenv('DB_HOST') ?: '127.0.0.1');
define('DB_PORT', getenv('DB_PORT') ?: '3306');
define('DB_NAME', getenv('DB_NAME') ?: 'cerbero');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') !== false ? getenv('DB_PASS') : '');
define('DB_CHARSET', 'utf8mb4');

/**
 * Retorna a instância única de conexão PDO (Singleton Pattern)
 * @return PDO|null
 */
function getConexao() {
    static $pdo = null;

    if ($pdo === null) {
        $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
        
        $opcoes = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
        ];

        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $opcoes);
        } catch (PDOException $e) {
            // Em caso de falha na conexão, registramos e retornamos mensagem amigável
            error_log("Erro de Conexão Cerbero PDO: " . $e->getMessage());
            $pdo = null;
        }
    }

    return $pdo;
}

/**
 * Verifica se o banco de dados está online e funcional
 * @return bool
 */
function statusBancoDados() {
    try {
        $conn = getConexao();
        if ($conn) {
            $stmt = $conn->query("SELECT 1");
            return $stmt !== false;
        }
        return false;
    } catch (Exception $e) {
        return false;
    }
}

// Inicializa a variável global $pdo para compatibilidade direta
$pdo = getConexao();
