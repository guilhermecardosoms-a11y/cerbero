-- ========================================================================
-- SISTEMA CERBERO - Script de Criação do Banco de Dados
-- Compatível com MySQL 5.7+ / MariaDB / InfinityFree / XAMPP
-- ========================================================================

CREATE DATABASE IF NOT EXISTS `cerbero` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `cerbero`;

-- ------------------------------------------------------------------------
-- 1. TABELA DE USUÁRIOS
-- ------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `usuarios` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `nome` VARCHAR(120) NOT NULL,
    `email` VARCHAR(150) NOT NULL UNIQUE,
    `senha` VARCHAR(255) NOT NULL,
    `nivel_acesso` ENUM('Administrador', 'Gerente', 'Operador') NOT NULL DEFAULT 'Operador',
    `status` ENUM('Ativo', 'Inativo') NOT NULL DEFAULT 'Ativo',
    `departamento` VARCHAR(100) DEFAULT 'Tecnologia da Informação',
    `ultimo_login` DATETIME NULL,
    `criado_em` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `atualizado_em` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------------------
-- 2. TABELA DE SOLICITAÇÕES DE ACESSO
-- ------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `solicitacoes` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `nome` VARCHAR(120) NOT NULL,
    `email` VARCHAR(150) NOT NULL,
    `motivo` TEXT NOT NULL,
    `departamento` VARCHAR(100) NOT NULL,
    `nivel_desejado` ENUM('Administrador', 'Gerente', 'Operador') NOT NULL DEFAULT 'Operador',
    `status` ENUM('Pendente', 'Aprovada', 'Rejeitada') NOT NULL DEFAULT 'Pendente',
    `resposta_observacao` TEXT NULL,
    `analisado_por` INT NULL,
    `analisado_em` DATETIME NULL,
    `criado_em` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_solicitacoes_usuario` FOREIGN KEY (`analisado_por`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------------------
-- 3. TABELA DE REGISTROS GERAIS (Ativos / Cadastros do Sistema)
-- ------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `registros` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `titulo` VARCHAR(160) NOT NULL,
    `categoria` VARCHAR(80) NOT NULL,
    `descricao` TEXT NOT NULL,
    `status` ENUM('Ativo', 'Pendente', 'Concluído', 'Arquivado') NOT NULL DEFAULT 'Ativo',
    `prioridade` ENUM('Baixa', 'Média', 'Alta', 'Crítica') NOT NULL DEFAULT 'Média',
    `criado_por` INT NULL,
    `criado_em` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `atualizado_em` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT `fk_registros_usuario` FOREIGN KEY (`criado_por`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------------------
-- 4. TABELA DE LOGS DE AUDITORIA E ATIVIDADE DO SISTEMA
-- ------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `logs_sistema` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `usuario_id` INT NULL,
    `acao` VARCHAR(100) NOT NULL,
    `detalhes` TEXT NULL,
    `ip` VARCHAR(45) NULL,
    `criado_em` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_logs_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------------------
-- SEED / DADOS INICIAIS
-- Senhas padrão:
-- admin@cerbero.com -> admin123
-- carlos@cerbero.com -> carlos123
-- ana@cerbero.com -> ana123
-- ------------------------------------------------------------------------

-- Inserindo Usuários Padrão
INSERT INTO `usuarios` (`id`, `nome`, `email`, `senha`, `nivel_acesso`, `status`, `departamento`) VALUES
(1, 'Administrador Cerbero', 'admin@cerbero.com', '$2y$10$wE6O6H0Xb1K2Y6T0A3/bUecD9.Yx4K9rL7e8.rW.Y2W1V8VzB1pKu', 'Administrador', 'Ativo', 'Segurança da Informação'),
(2, 'Carlos Silva', 'carlos@cerbero.com', '$2y$10$wE6O6H0Xb1K2Y6T0A3/bUecD9.Yx4K9rL7e8.rW.Y2W1V8VzB1pKu', 'Gerente', 'Ativo', 'Operações & Infraestrutura'),
(3, 'Ana Martins', 'ana@cerbero.com', '$2y$10$wE6O6H0Xb1K2Y6T0A3/bUecD9.Yx4K9rL7e8.rW.Y2W1V8VzB1pKu', 'Operador', 'Ativo', 'Atendimento ao Cliente')
ON DUPLICATE KEY UPDATE `id` = `id`;

-- Inserindo Solicitações de Acesso de Exemplo
INSERT INTO `solicitacoes` (`id`, `nome`, `email`, `motivo`, `departamento`, `nivel_desejado`, `status`, `criado_em`) VALUES
(1, 'Mariana Costa', 'mariana.costa@empresa.com', 'Necessito de acesso ao painel para auditoria trimestral de conformidade.', 'Compliance', 'Gerente', 'Pendente', NOW() - INTERVAL 2 HOUR),
(2, 'Lucas Rocha', 'lucas.rocha@empresa.com', 'Novo colaborador da equipe de suporte técnico e monitoramento.', 'Suporte Técnico', 'Operador', 'Pendente', NOW() - INTERVAL 5 HOUR),
(3, 'Beatriz Almeida', 'beatriz.almeida@empresa.com', 'Acesso solicitado para gestão de inventário de ativos da filial Sul.', 'Logística', 'Operador', 'Aprovada', NOW() - INTERVAL 1 DAY),
(4, 'Rafael Souza', 'rafael.souza@empresa.com', 'Solicitação sem justificativa clara para acesso administrativo.', 'Vendas', 'Administrador', 'Rejeitada', NOW() - INTERVAL 2 DAY)
ON DUPLICATE KEY UPDATE `id` = `id`;

-- Inserindo Registros Gerais do Sistema
INSERT INTO `registros` (`id`, `titulo`, `categoria`, `descricao`, `status`, `prioridade`, `criado_por`, `criado_em`) VALUES
(1, 'Servidor Principal Cerbero Node-01', 'Infraestrutura', 'Servidor dedicado para balanceamento de carga e gateway de autenticação.', 'Ativo', 'Crítica', 1, NOW() - INTERVAL 3 DAY),
(2, 'Firewall de Borda Fortinet 200F', 'Segurança', 'Configuração de regras de proteção contra DDoS e filtragem de portas sensíveis.', 'Ativo', 'Alta', 1, NOW() - INTERVAL 2 DAY),
(3, 'Certificado SSL Wildcard (*.cerbero.net)', 'Segurança', 'Renovação do certificado SSL TLSv1.3 com validade até outubro de 2027.', 'Ativo', 'Média', 2, NOW() - INTERVAL 1 DAY),
(4, 'Rotina Diária de Backup em Nuvem', 'Backup', 'Execução automatizada de snapshot da base MySQL para bucket S3 com criptografia AES-256.', 'Concluído', 'Alta', 1, NOW() - INTERVAL 6 HOUR),
(5, 'Revisão das Credenciais de API Externa', 'Auditoria', 'Auditoria de chaves de integração pendente de aprovação do comitê.', 'Pendente', 'Média', 2, NOW() - INTERVAL 4 HOUR)
ON DUPLICATE KEY UPDATE `id` = `id`;

-- Inserindo Logs Iniciais
INSERT INTO `logs_sistema` (`usuario_id`, `acao`, `detalhes`, `ip`) VALUES
(1, 'LOGIN_SUCESSO', 'Autenticação bem-sucedida do administrador', '192.168.1.10'),
(1, 'SISTEMA_INICIALIZADO', 'Banco de dados e tabelas do Cerbero sincronizados com sucesso', '127.0.0.1');
