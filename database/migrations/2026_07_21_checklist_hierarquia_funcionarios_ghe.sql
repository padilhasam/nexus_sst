-- NEXUS SST
-- Etapa 3: estrutura operacional do Check-list
-- Compatível com MySQL 8.x. O script pode ser executado mais de uma vez.

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

DROP PROCEDURE IF EXISTS nexus_add_column_if_missing;
DELIMITER $$
CREATE PROCEDURE nexus_add_column_if_missing(
    IN p_table_name VARCHAR(64),
    IN p_column_name VARCHAR(64),
    IN p_definition TEXT
)
BEGIN
    IF NOT EXISTS (
        SELECT 1
        FROM information_schema.columns
        WHERE table_schema = DATABASE()
          AND table_name = p_table_name
          AND column_name = p_column_name
    ) THEN
        SET @ddl = CONCAT(
            'ALTER TABLE `', REPLACE(p_table_name, '`', '``'),
            '` ADD COLUMN `', REPLACE(p_column_name, '`', '``'),
            '` ', p_definition
        );
        PREPARE nexus_stmt FROM @ddl;
        EXECUTE nexus_stmt;
        DEALLOCATE PREPARE nexus_stmt;
    END IF;
END$$
DELIMITER ;

CALL nexus_add_column_if_missing(
    'checklists_visita',
    'ultima_aba',
    'VARCHAR(30) NOT NULL DEFAULT ''dados'''
);
CALL nexus_add_column_if_missing(
    'checklists_visita',
    'atualizado_em',
    'DATETIME NULL'
);

CREATE TABLE IF NOT EXISTS `funcionarios` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `empresa_id` INT NOT NULL,
    `unidade_id` INT DEFAULT NULL,
    `hierarquia_id` INT NOT NULL,
    `codigo` VARCHAR(30) DEFAULT NULL,
    `codigo_externo` VARCHAR(80) DEFAULT NULL,
    `matricula` VARCHAR(50) DEFAULT NULL,
    `nome` VARCHAR(180) NOT NULL,
    `cpf` VARCHAR(20) DEFAULT NULL,
    `data_admissao` DATE DEFAULT NULL,
    `data_desligamento` DATE DEFAULT NULL,
    `motivo_inativacao` VARCHAR(255) DEFAULT NULL,
    `observacoes` TEXT,
    `ativo` TINYINT(1) NOT NULL DEFAULT 1,
    `inativado_por` INT DEFAULT NULL,
    `criado_em` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    `atualizado_em` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_funcionario_empresa_unidade` (`empresa_id`, `unidade_id`, `ativo`),
    KEY `idx_funcionario_hierarquia` (`hierarquia_id`),
    KEY `idx_funcionario_nome` (`nome`),
    KEY `idx_funcionario_cpf` (`cpf`),
    KEY `idx_funcionario_matricula` (`matricula`),
    CONSTRAINT `fk_funcionario_empresa` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT `fk_funcionario_unidade` FOREIGN KEY (`unidade_id`) REFERENCES `unidades` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT `fk_funcionario_hierarquia` FOREIGN KEY (`hierarquia_id`) REFERENCES `hierarquias` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT `fk_funcionario_inativado_por` FOREIGN KEY (`inativado_por`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CALL nexus_add_column_if_missing('funcionarios', 'empresa_id', 'INT NOT NULL');
CALL nexus_add_column_if_missing('funcionarios', 'unidade_id', 'INT NULL');
CALL nexus_add_column_if_missing('funcionarios', 'hierarquia_id', 'INT NOT NULL');
CALL nexus_add_column_if_missing('funcionarios', 'codigo', 'VARCHAR(30) NULL');
CALL nexus_add_column_if_missing('funcionarios', 'codigo_externo', 'VARCHAR(80) NULL');
CALL nexus_add_column_if_missing('funcionarios', 'matricula', 'VARCHAR(50) NULL');
CALL nexus_add_column_if_missing('funcionarios', 'nome', 'VARCHAR(180) NOT NULL');
CALL nexus_add_column_if_missing('funcionarios', 'cpf', 'VARCHAR(20) NULL');
CALL nexus_add_column_if_missing('funcionarios', 'data_admissao', 'DATE NULL');
CALL nexus_add_column_if_missing('funcionarios', 'data_desligamento', 'DATE NULL');
CALL nexus_add_column_if_missing('funcionarios', 'motivo_inativacao', 'VARCHAR(255) NULL');
CALL nexus_add_column_if_missing('funcionarios', 'observacoes', 'TEXT NULL');
CALL nexus_add_column_if_missing('funcionarios', 'ativo', 'TINYINT(1) NOT NULL DEFAULT 1');
CALL nexus_add_column_if_missing('funcionarios', 'inativado_por', 'INT NULL');
CALL nexus_add_column_if_missing('funcionarios', 'criado_em', 'TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP');
CALL nexus_add_column_if_missing('funcionarios', 'atualizado_em', 'TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP');

CREATE TABLE IF NOT EXISTS `ghes` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `checklist_id` INT NOT NULL,
    `empresa_id` INT NOT NULL,
    `unidade_id` INT DEFAULT NULL,
    `codigo` VARCHAR(40) NOT NULL,
    `nome` VARCHAR(180) NOT NULL,
    `descricao` TEXT,
    `observacoes` TEXT,
    `ativo` TINYINT(1) NOT NULL DEFAULT 1,
    `criado_por` INT NOT NULL,
    `criado_em` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    `atualizado_em` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_ghe_checklist_codigo` (`checklist_id`, `codigo`),
    KEY `idx_ghe_checklist` (`checklist_id`, `ativo`),
    KEY `idx_ghe_empresa_unidade` (`empresa_id`, `unidade_id`),
    CONSTRAINT `fk_ghe_checklist` FOREIGN KEY (`checklist_id`) REFERENCES `checklists_visita` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_ghe_empresa` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT `fk_ghe_unidade` FOREIGN KEY (`unidade_id`) REFERENCES `unidades` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT `fk_ghe_criado_por` FOREIGN KEY (`criado_por`) REFERENCES `usuarios` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CALL nexus_add_column_if_missing('ghes', 'checklist_id', 'INT NOT NULL');
CALL nexus_add_column_if_missing('ghes', 'empresa_id', 'INT NOT NULL');
CALL nexus_add_column_if_missing('ghes', 'unidade_id', 'INT NULL');
CALL nexus_add_column_if_missing('ghes', 'codigo', 'VARCHAR(40) NOT NULL');
CALL nexus_add_column_if_missing('ghes', 'nome', 'VARCHAR(180) NOT NULL');
CALL nexus_add_column_if_missing('ghes', 'descricao', 'TEXT NULL');
CALL nexus_add_column_if_missing('ghes', 'observacoes', 'TEXT NULL');
CALL nexus_add_column_if_missing('ghes', 'ativo', 'TINYINT(1) NOT NULL DEFAULT 1');
CALL nexus_add_column_if_missing('ghes', 'criado_por', 'INT NOT NULL');
CALL nexus_add_column_if_missing('ghes', 'criado_em', 'TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP');
CALL nexus_add_column_if_missing('ghes', 'atualizado_em', 'TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP');

CREATE TABLE IF NOT EXISTS `ghe_cargos` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `ghe_id` INT NOT NULL,
    `hierarquia_id` INT NOT NULL,
    `criado_em` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_ghe_hierarquia` (`ghe_id`, `hierarquia_id`),
    KEY `idx_ghe_cargo_hierarquia` (`hierarquia_id`),
    CONSTRAINT `fk_ghe_cargo_ghe` FOREIGN KEY (`ghe_id`) REFERENCES `ghes` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_ghe_cargo_hierarquia` FOREIGN KEY (`hierarquia_id`) REFERENCES `hierarquias` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CALL nexus_add_column_if_missing('ghe_cargos', 'ghe_id', 'INT NOT NULL');
CALL nexus_add_column_if_missing('ghe_cargos', 'hierarquia_id', 'INT NOT NULL');
CALL nexus_add_column_if_missing('ghe_cargos', 'criado_em', 'TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP');

CREATE TABLE IF NOT EXISTS `ghe_riscos` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `ghe_id` INT NOT NULL,
    `risco_id` INT NOT NULL,
    `fonte_geradora` VARCHAR(255) DEFAULT NULL,
    `meio_propagacao` VARCHAR(180) DEFAULT NULL,
    `frequencia` ENUM('EVENTUAL','ESPORADICA','INTERMITENTE','HABITUAL','PERMANENTE') DEFAULT NULL,
    `tempo_exposicao` ENUM('MUITO_BAIXO','BAIXO','MODERADO','ALTO','MUITO_ALTO') DEFAULT NULL,
    `intensidade` VARCHAR(100) DEFAULT NULL,
    `unidade_medida` VARCHAR(50) DEFAULT NULL,
    `exige_quantificacao` TINYINT(1) NOT NULL DEFAULT 0,
    `observacoes` TEXT,
    `criado_em` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    `atualizado_em` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_ghe_risco_ghe` (`ghe_id`),
    KEY `idx_ghe_risco_risco` (`risco_id`),
    KEY `idx_ghe_risco_quantificacao` (`exige_quantificacao`),
    CONSTRAINT `fk_ghe_risco_ghe` FOREIGN KEY (`ghe_id`) REFERENCES `ghes` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_ghe_risco_risco` FOREIGN KEY (`risco_id`) REFERENCES `riscos` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CALL nexus_add_column_if_missing('ghe_riscos', 'ghe_id', 'INT NOT NULL');
CALL nexus_add_column_if_missing('ghe_riscos', 'risco_id', 'INT NOT NULL');
CALL nexus_add_column_if_missing('ghe_riscos', 'fonte_geradora', 'VARCHAR(255) NULL');
CALL nexus_add_column_if_missing('ghe_riscos', 'meio_propagacao', 'VARCHAR(180) NULL');
CALL nexus_add_column_if_missing('ghe_riscos', 'frequencia', 'ENUM(''EVENTUAL'',''ESPORADICA'',''INTERMITENTE'',''HABITUAL'',''PERMANENTE'') NULL');
CALL nexus_add_column_if_missing('ghe_riscos', 'tempo_exposicao', 'ENUM(''MUITO_BAIXO'',''BAIXO'',''MODERADO'',''ALTO'',''MUITO_ALTO'') NULL');
CALL nexus_add_column_if_missing('ghe_riscos', 'intensidade', 'VARCHAR(100) NULL');
CALL nexus_add_column_if_missing('ghe_riscos', 'unidade_medida', 'VARCHAR(50) NULL');
CALL nexus_add_column_if_missing('ghe_riscos', 'exige_quantificacao', 'TINYINT(1) NOT NULL DEFAULT 0');
CALL nexus_add_column_if_missing('ghe_riscos', 'observacoes', 'TEXT NULL');
CALL nexus_add_column_if_missing('ghe_riscos', 'criado_em', 'TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP');
CALL nexus_add_column_if_missing('ghe_riscos', 'atualizado_em', 'TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP');

DROP PROCEDURE IF EXISTS nexus_add_column_if_missing;

SET FOREIGN_KEY_CHECKS = 1;
