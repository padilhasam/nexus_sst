-- MySQL dump 10.13  Distrib 8.0.43, for Win64 (x86_64)
--
-- Host: localhost    Database: nexus
-- ------------------------------------------------------
-- Server version	8.0.43

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `agenda_historico`
--

DROP TABLE IF EXISTS `agenda_historico`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `agenda_historico` (
  `id` int NOT NULL AUTO_INCREMENT,
  `agenda_id` int NOT NULL,
  `usuario_id` int DEFAULT NULL,
  `acao` enum('CRIADA','ALTERADA','REAGENDADA','CANCELADA','EXCLUIDA','CONFIRMADA','CONCLUIDA','VISITA_GERADA') NOT NULL,
  `descricao` text,
  `motivo` text,
  `dados_anteriores` json DEFAULT NULL,
  `dados_novos` json DEFAULT NULL,
  `criado_em` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_agenda_historico_agenda` (`agenda_id`),
  KEY `idx_agenda_historico_usuario` (`usuario_id`),
  KEY `idx_agenda_historico_acao` (`acao`),
  CONSTRAINT `fk_agenda_historico_agenda` FOREIGN KEY (`agenda_id`) REFERENCES `agendas` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_agenda_historico_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `agenda_historico`
--

LOCK TABLES `agenda_historico` WRITE;
/*!40000 ALTER TABLE `agenda_historico` DISABLE KEYS */;
/*!40000 ALTER TABLE `agenda_historico` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `agendas`
--

DROP TABLE IF EXISTS `agendas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `agendas` (
  `id` int NOT NULL AUTO_INCREMENT,
  `empresa_id` int NOT NULL,
  `unidade_id` int DEFAULT NULL,
  `tecnico_id` int NOT NULL,
  `veiculo_id` int DEFAULT NULL,
  `data_agendada` date NOT NULL,
  `hora_inicio` time DEFAULT NULL,
  `hora_fim` time DEFAULT NULL,
  `titulo` varchar(180) DEFAULT NULL,
  `objetivo` text,
  `observacoes` text,
  `responsavel_acompanhamento` varchar(150) DEFAULT NULL,
  `prioridade` enum('PADRAO','URGENTE','CRITICA') DEFAULT 'PADRAO',
  `status` enum('AGENDADO','CONFIRMADO','REAGENDADO','CANCELADO','CONCLUIDO','EXCLUIDO') DEFAULT 'AGENDADO',
  `visita_tecnica_id` int DEFAULT NULL,
  `criado_por` int DEFAULT NULL,
  `atualizado_por` int DEFAULT NULL,
  `cancelado_por` int DEFAULT NULL,
  `excluido_por` int DEFAULT NULL,
  `motivo_cancelamento` text,
  `motivo_exclusao` text,
  `criado_em` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `atualizado_em` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `cancelado_em` datetime DEFAULT NULL,
  `excluido_em` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_agendas_empresa` (`empresa_id`),
  KEY `idx_agendas_unidade` (`unidade_id`),
  KEY `idx_agendas_tecnico` (`tecnico_id`),
  KEY `idx_agendas_veiculo` (`veiculo_id`),
  KEY `idx_agendas_status` (`status`),
  KEY `idx_agendas_data` (`data_agendada`),
  KEY `idx_agendas_visita` (`visita_tecnica_id`),
  KEY `fk_agendas_criado_por` (`criado_por`),
  KEY `fk_agendas_atualizado_por` (`atualizado_por`),
  KEY `fk_agendas_cancelado_por` (`cancelado_por`),
  KEY `fk_agendas_excluido_por` (`excluido_por`),
  CONSTRAINT `fk_agendas_atualizado_por` FOREIGN KEY (`atualizado_por`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_agendas_cancelado_por` FOREIGN KEY (`cancelado_por`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_agendas_criado_por` FOREIGN KEY (`criado_por`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_agendas_empresa` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`),
  CONSTRAINT `fk_agendas_excluido_por` FOREIGN KEY (`excluido_por`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_agendas_tecnico` FOREIGN KEY (`tecnico_id`) REFERENCES `usuarios` (`id`),
  CONSTRAINT `fk_agendas_unidade` FOREIGN KEY (`unidade_id`) REFERENCES `unidades` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_agendas_veiculo` FOREIGN KEY (`veiculo_id`) REFERENCES `veiculos` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_agendas_visita` FOREIGN KEY (`visita_tecnica_id`) REFERENCES `visitas_tecnicas` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `agendas`
--

LOCK TABLES `agendas` WRITE;
/*!40000 ALTER TABLE `agendas` DISABLE KEYS */;
/*!40000 ALTER TABLE `agendas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cargos`
--

DROP TABLE IF EXISTS `cargos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cargos` (
  `id` int NOT NULL AUTO_INCREMENT,
  `codigo` varchar(30) DEFAULT NULL,
  `codigo_externo` varchar(50) DEFAULT NULL,
  `nome` varchar(150) NOT NULL,
  `cbo` varchar(20) DEFAULT NULL,
  `descricao` text,
  `ativo` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_cargo_nome` (`nome`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cargos`
--

LOCK TABLES `cargos` WRITE;
/*!40000 ALTER TABLE `cargos` DISABLE KEYS */;
INSERT INTO `cargos` VALUES (1,NULL,NULL,'Assistente Administrativo','414010','Teste',1,'2026-06-26 20:23:49','2026-06-26 20:23:49');
/*!40000 ALTER TABLE `cargos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `checklist_itens`
--

DROP TABLE IF EXISTS `checklist_itens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `checklist_itens` (
  `id` int NOT NULL AUTO_INCREMENT,
  `checklist_modelo_id` int NOT NULL,
  `categoria` varchar(100) DEFAULT NULL,
  `item` text NOT NULL,
  `obrigatorio` tinyint(1) DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `checklist_modelo_id` (`checklist_modelo_id`),
  CONSTRAINT `checklist_itens_ibfk_1` FOREIGN KEY (`checklist_modelo_id`) REFERENCES `checklist_modelos` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `checklist_itens`
--

LOCK TABLES `checklist_itens` WRITE;
/*!40000 ALTER TABLE `checklist_itens` DISABLE KEYS */;
/*!40000 ALTER TABLE `checklist_itens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `checklist_modelos`
--

DROP TABLE IF EXISTS `checklist_modelos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `checklist_modelos` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nome` varchar(150) NOT NULL,
  `descricao` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `checklist_modelos`
--

LOCK TABLES `checklist_modelos` WRITE;
/*!40000 ALTER TABLE `checklist_modelos` DISABLE KEYS */;
/*!40000 ALTER TABLE `checklist_modelos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `checklist_respostas`
--

DROP TABLE IF EXISTS `checklist_respostas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `checklist_respostas` (
  `id` int NOT NULL AUTO_INCREMENT,
  `visita_id` int NOT NULL,
  `checklist_item_id` int NOT NULL,
  `resposta` enum('SIM','NAO','NAO_APLICA') DEFAULT NULL,
  `observacao` text,
  PRIMARY KEY (`id`),
  KEY `visita_id` (`visita_id`),
  KEY `checklist_item_id` (`checklist_item_id`),
  CONSTRAINT `checklist_respostas_ibfk_1` FOREIGN KEY (`visita_id`) REFERENCES `visitas_tecnicas` (`id`) ON DELETE CASCADE,
  CONSTRAINT `checklist_respostas_ibfk_2` FOREIGN KEY (`checklist_item_id`) REFERENCES `checklist_itens` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `checklist_respostas`
--

LOCK TABLES `checklist_respostas` WRITE;
/*!40000 ALTER TABLE `checklist_respostas` DISABLE KEYS */;
/*!40000 ALTER TABLE `checklist_respostas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `checklists_visita`
--

DROP TABLE IF EXISTS `checklists_visita`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `checklists_visita` (
  `id` int NOT NULL AUTO_INCREMENT,
  `visita_id` int NOT NULL,
  `empresa_id` int NOT NULL,
  `unidade_id` int DEFAULT NULL,
  `usuario_id` int NOT NULL,
  `prioridade` enum('PADRAO','URGENTE','CRITICA') DEFAULT 'PADRAO',
  `responsavel_acompanhamento` varchar(150) DEFAULT NULL,
  `status` enum('ABERTO','EM_ANDAMENTO','CONCLUIDO','CANCELADO') DEFAULT 'ABERTO',
  `data_inicio` datetime DEFAULT CURRENT_TIMESTAMP,
  `data_fim` datetime DEFAULT NULL,
  `assinatura_responsavel` text,
  `assinatura_tecnico` text,
  `criado_em` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `visita_id` (`visita_id`),
  KEY `empresa_id` (`empresa_id`),
  KEY `unidade_id` (`unidade_id`),
  KEY `usuario_id` (`usuario_id`),
  CONSTRAINT `checklists_visita_ibfk_1` FOREIGN KEY (`visita_id`) REFERENCES `visitas_tecnicas` (`id`) ON DELETE CASCADE,
  CONSTRAINT `checklists_visita_ibfk_2` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`),
  CONSTRAINT `checklists_visita_ibfk_3` FOREIGN KEY (`unidade_id`) REFERENCES `unidades` (`id`),
  CONSTRAINT `checklists_visita_ibfk_4` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `checklists_visita`
--

LOCK TABLES `checklists_visita` WRITE;
/*!40000 ALTER TABLE `checklists_visita` DISABLE KEYS */;
/*!40000 ALTER TABLE `checklists_visita` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `empresas`
--

DROP TABLE IF EXISTS `empresas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `empresas` (
  `id` int NOT NULL AUTO_INCREMENT,
  `codigo` varchar(30) DEFAULT NULL,
  `codigo_externo` varchar(50) DEFAULT NULL,
  `razao_social` varchar(200) NOT NULL,
  `nome_fantasia` varchar(200) DEFAULT NULL,
  `cnpj` varchar(20) DEFAULT NULL,
  `inscricao_estadual` varchar(50) DEFAULT NULL,
  `cnae` varchar(30) DEFAULT NULL,
  `descricao_cnae` varchar(255) DEFAULT NULL,
  `grau_risco` varchar(10) DEFAULT NULL,
  `quantidade_funcionarios` int DEFAULT NULL,
  `telefone` varchar(30) DEFAULT NULL,
  `email` varchar(150) DEFAULT NULL,
  `responsavel` varchar(150) DEFAULT NULL,
  `cargo_responsavel` varchar(150) DEFAULT NULL,
  `contato_responsavel` varchar(100) DEFAULT NULL,
  `endereco` text,
  `cidade` varchar(100) DEFAULT NULL,
  `estado` varchar(2) DEFAULT NULL,
  `cep` varchar(20) DEFAULT NULL,
  `logradouro` varchar(200) DEFAULT NULL,
  `numero` varchar(20) DEFAULT NULL,
  `complemento` varchar(100) DEFAULT NULL,
  `bairro` varchar(100) DEFAULT NULL,
  `tecnico_responsavel` varchar(150) DEFAULT NULL,
  `supervisor_responsavel` varchar(150) DEFAULT NULL,
  `periodicidade_visitas` varchar(50) DEFAULT NULL,
  `observacoes` text,
  `ativo` tinyint(1) DEFAULT '1',
  `criado_em` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `atualizado_em` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_empresa_cnpj` (`cnpj`),
  UNIQUE KEY `uk_empresa_codigo` (`codigo`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `empresas`
--

LOCK TABLES `empresas` WRITE;
/*!40000 ALTER TABLE `empresas` DISABLE KEYS */;
INSERT INTO `empresas` VALUES (9,'EMP6A3FE0632AB0D',NULL,'CyberTech Technology Ltda','CyberTech Technology Ltda','78.136.198/0001-22',NULL,NULL,NULL,NULL,NULL,'(41) 98899-9090','tecnologia@sst.com.br','Jorge',NULL,'(41) 99898-9796','Rua Governador Ernany Sátiro, 300, Acácio Figueiredo, Campina Grande, PB','Campina Grande','PB','58421-090','Rua Governador Ernany Sátiro','300',NULL,'Acácio Figueiredo','Jeferson','Marcos','Anual',NULL,0,'2026-06-17 19:31:07','2026-06-27 14:43:04'),(10,NULL,NULL,'Santiago Saúde e Segurança Ocupacional Ltda','Santiago Saúde e Segurança Ocupacional Ltda','11.492.975/0001-09',NULL,NULL,NULL,NULL,NULL,'(41) 3027-2727','santiago@sstsantiago.com.br','Deize',NULL,'(41) 99596-9779','Rua Barcelos Rastros, 250 - Centro','Curitiba','PR','83120-300',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,'2026-06-17 19:36:46','2026-06-23 14:28:07'),(11,'EMP6A3AE38A63713',NULL,'Artemis Consultoria Ltda','Artemis Consultoria Ltda','56.802.725/0001-58',NULL,NULL,NULL,NULL,NULL,'(41) 99991-1111','comercial@artemis.com.br','Jeferson',NULL,'(41) 99898-9796','Rua das Raparigas, 580',NULL,NULL,'83708-500',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,'2026-06-23 19:50:34','2026-06-23 19:50:34'),(12,'EMP6A3AF1A3D7DA0',NULL,'Soluções Nexu Ltda','Soluções Nexu Ltda','71.455.718/0001-10',NULL,NULL,NULL,NULL,NULL,'(41) 99795-9893','comercial@nexus.com','Jeferson',NULL,'(41) 99890-9696','Rua do Rock, 200',NULL,NULL,'83654-700',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,'2026-06-23 20:50:43','2026-06-23 20:50:43'),(13,'EMP6A3FEDC7A1EAA',NULL,'MATHEUS REFFI AFFONSECA 41400520827','MATHEUS REFFI','22.917.461/0001-66',NULL,'95.29-1/99','Reparação e manutenção de outros objetos e equipamentos pessoais e domésticos não especificados anteriormente','2',200,'(41) 3032-2025','diretoria@reffi.com.br','Paula','Diretora','(41) 99645-2021','Rua Júlio Conceição, 120, Vila Mathias, Santos, SP','Santos','SP','11015-540','Rua Júlio Conceição','120',NULL,'Vila Mathias','Juliana','Marcos','Anual','Teste',1,'2026-06-27 15:35:35','2026-06-27 15:35:35'),(14,'EMP-89BFC316','EXT-EMP-20260629032507','54.017.841 ALCILEIDE PEREIRA DE ARAUJO SANTOS','54.017.841 ALCILEIDE PEREIRA DE ARAUJO SANTOS','54.017.841/0001-30',NULL,'97.00-5/00','Serviços domésticos','1',2,'(41) 3040-5659','alcileide@hotmail.com','Alcileide','Proprietário','(41) 99190-2356','Rua Maria Tavares da Silva, 330, Rincão, Mossoró, RN','Mossoró','RN','59630-505','Rua Maria Tavares da Silva','330',NULL,'Rincão','Dayane','Marcos','Anual',NULL,1,'2026-06-29 10:46:48','2026-06-29 10:46:48');
/*!40000 ALTER TABLE `empresas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `epi_detalhes`
--

DROP TABLE IF EXISTS `epi_detalhes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `epi_detalhes` (
  `id` int NOT NULL AUTO_INCREMENT,
  `medida_controle_id` int NOT NULL,
  `ca` varchar(30) DEFAULT NULL,
  `fabricante` varchar(180) DEFAULT NULL,
  `modelo` varchar(180) DEFAULT NULL,
  `aprovado_para` text,
  `protege_poeiras` tinyint(1) DEFAULT '0',
  `protege_gases` tinyint(1) DEFAULT '0',
  `protege_vapores` tinyint(1) DEFAULT '0',
  `protege_nevoas` tinyint(1) DEFAULT '0',
  `protege_fumos` tinyint(1) DEFAULT '0',
  `protege_ruido` tinyint(1) DEFAULT '0',
  `protege_quimicos` tinyint(1) DEFAULT '0',
  `protege_biologicos` tinyint(1) DEFAULT '0',
  `nivel_atenuacao` varchar(80) DEFAULT NULL,
  `tipo_protecao` varchar(120) DEFAULT NULL,
  `classe` varchar(80) DEFAULT NULL,
  `data_validade_ca` date DEFAULT NULL,
  `situacao_ca` varchar(80) DEFAULT NULL,
  `fonte_consulta` varchar(255) DEFAULT NULL,
  `data_ultima_consulta` datetime DEFAULT NULL,
  `observacoes_tecnicas` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_epi_medida` (`medida_controle_id`),
  KEY `idx_epi_ca` (`ca`),
  CONSTRAINT `fk_epi_medida` FOREIGN KEY (`medida_controle_id`) REFERENCES `medidas_controle` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `epi_detalhes`
--

LOCK TABLES `epi_detalhes` WRITE;
/*!40000 ALTER TABLE `epi_detalhes` DISABLE KEYS */;
/*!40000 ALTER TABLE `epi_detalhes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `evidencias`
--

DROP TABLE IF EXISTS `evidencias`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `evidencias` (
  `id` int NOT NULL AUTO_INCREMENT,
  `visita_id` int DEFAULT NULL,
  `quantificacao_id` int DEFAULT NULL,
  `titulo` varchar(150) DEFAULT NULL,
  `descricao` text,
  `arquivo` varchar(255) DEFAULT NULL,
  `tipo` varchar(50) DEFAULT NULL,
  `criado_em` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `evidencias`
--

LOCK TABLES `evidencias` WRITE;
/*!40000 ALTER TABLE `evidencias` DISABLE KEYS */;
/*!40000 ALTER TABLE `evidencias` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `hierarquias`
--

DROP TABLE IF EXISTS `hierarquias`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `hierarquias` (
  `id` int NOT NULL AUTO_INCREMENT,
  `empresa_id` int NOT NULL,
  `unidade_id` int NOT NULL,
  `setor_id` int NOT NULL,
  `cargo_id` int NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_hierarquia` (`empresa_id`,`unidade_id`,`setor_id`,`cargo_id`),
  KEY `fk_hierarquia_unidade` (`unidade_id`),
  KEY `fk_hierarquia_setor` (`setor_id`),
  KEY `fk_hierarquia_cargo` (`cargo_id`),
  CONSTRAINT `fk_hierarquia_cargo` FOREIGN KEY (`cargo_id`) REFERENCES `cargos` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_hierarquia_empresa` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_hierarquia_setor` FOREIGN KEY (`setor_id`) REFERENCES `setores` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_hierarquia_unidade` FOREIGN KEY (`unidade_id`) REFERENCES `unidades` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `hierarquias`
--

LOCK TABLES `hierarquias` WRITE;
/*!40000 ALTER TABLE `hierarquias` DISABLE KEYS */;
/*!40000 ALTER TABLE `hierarquias` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `importacoes`
--

DROP TABLE IF EXISTS `importacoes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `importacoes` (
  `id` int NOT NULL AUTO_INCREMENT,
  `usuario_id` int DEFAULT NULL,
  `tipo` varchar(100) DEFAULT NULL,
  `arquivo` varchar(255) DEFAULT NULL,
  `total_registros` int DEFAULT '0',
  `total_importados` int DEFAULT '0',
  `total_erros` int DEFAULT '0',
  `status` enum('PROCESSANDO','FINALIZADO','ERRO') DEFAULT 'PROCESSANDO',
  `log_erros` longtext,
  `criado_em` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `importacoes`
--

LOCK TABLES `importacoes` WRITE;
/*!40000 ALTER TABLE `importacoes` DISABLE KEYS */;
/*!40000 ALTER TABLE `importacoes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `levantamentos`
--

DROP TABLE IF EXISTS `levantamentos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `levantamentos` (
  `id` int NOT NULL AUTO_INCREMENT,
  `visita_id` int NOT NULL,
  `cargo_id` int NOT NULL,
  `risco_id` int NOT NULL,
  `criado_em` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `visita_id` (`visita_id`),
  KEY `cargo_id` (`cargo_id`),
  KEY `risco_id` (`risco_id`),
  CONSTRAINT `levantamentos_ibfk_1` FOREIGN KEY (`visita_id`) REFERENCES `visitas_tecnicas` (`id`) ON DELETE CASCADE,
  CONSTRAINT `levantamentos_ibfk_2` FOREIGN KEY (`cargo_id`) REFERENCES `cargos` (`id`),
  CONSTRAINT `levantamentos_ibfk_3` FOREIGN KEY (`risco_id`) REFERENCES `riscos` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `levantamentos`
--

LOCK TABLES `levantamentos` WRITE;
/*!40000 ALTER TABLE `levantamentos` DISABLE KEYS */;
/*!40000 ALTER TABLE `levantamentos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `logs_sistema`
--

DROP TABLE IF EXISTS `logs_sistema`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `logs_sistema` (
  `id` int NOT NULL AUTO_INCREMENT,
  `usuario_id` int DEFAULT NULL,
  `acao` varchar(255) DEFAULT NULL,
  `tabela_afetada` varchar(100) DEFAULT NULL,
  `registro_id` int DEFAULT NULL,
  `ip` varchar(100) DEFAULT NULL,
  `criado_em` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `usuario_id` (`usuario_id`),
  CONSTRAINT `logs_sistema_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `logs_sistema`
--

LOCK TABLES `logs_sistema` WRITE;
/*!40000 ALTER TABLE `logs_sistema` DISABLE KEYS */;
/*!40000 ALTER TABLE `logs_sistema` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `medidas_controle`
--

DROP TABLE IF EXISTS `medidas_controle`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `medidas_controle` (
  `id` int NOT NULL AUTO_INCREMENT,
  `tipo_medida_id` int NOT NULL,
  `codigo` varchar(50) DEFAULT NULL,
  `codigo_externo` varchar(80) DEFAULT NULL,
  `nome` varchar(180) NOT NULL,
  `descricao` text,
  `observacoes` text,
  `ativo` tinyint(1) DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_medidas_codigo` (`codigo`),
  KEY `idx_medidas_tipo` (`tipo_medida_id`),
  KEY `idx_medidas_nome` (`nome`),
  KEY `idx_medidas_ativo` (`ativo`),
  CONSTRAINT `fk_medidas_tipo` FOREIGN KEY (`tipo_medida_id`) REFERENCES `tipos_medidas_controle` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `medidas_controle`
--

LOCK TABLES `medidas_controle` WRITE;
/*!40000 ALTER TABLE `medidas_controle` DISABLE KEYS */;
/*!40000 ALTER TABLE `medidas_controle` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `nao_conformidades`
--

DROP TABLE IF EXISTS `nao_conformidades`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `nao_conformidades` (
  `id` int NOT NULL AUTO_INCREMENT,
  `empresa_id` int NOT NULL,
  `visita_id` int DEFAULT NULL,
  `descricao` text NOT NULL,
  `classificacao` enum('LEVE','MODERADA','GRAVE','CRITICA') DEFAULT NULL,
  `prioridade` enum('BAIXA','MEDIA','ALTA') DEFAULT NULL,
  `prazo` date DEFAULT NULL,
  `responsavel` varchar(150) DEFAULT NULL,
  `status` enum('ABERTA','EM_ANDAMENTO','FINALIZADA') DEFAULT 'ABERTA',
  `criado_em` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `empresa_id` (`empresa_id`),
  KEY `visita_id` (`visita_id`),
  CONSTRAINT `nao_conformidades_ibfk_1` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`),
  CONSTRAINT `nao_conformidades_ibfk_2` FOREIGN KEY (`visita_id`) REFERENCES `visitas_tecnicas` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `nao_conformidades`
--

LOCK TABLES `nao_conformidades` WRITE;
/*!40000 ALTER TABLE `nao_conformidades` DISABLE KEYS */;
/*!40000 ALTER TABLE `nao_conformidades` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `planos_acao`
--

DROP TABLE IF EXISTS `planos_acao`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `planos_acao` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nao_conformidade_id` int NOT NULL,
  `acao` text NOT NULL,
  `responsavel` varchar(150) DEFAULT NULL,
  `prazo` date DEFAULT NULL,
  `status` enum('PENDENTE','EM_EXECUCAO','CONCLUIDO') DEFAULT 'PENDENTE',
  PRIMARY KEY (`id`),
  KEY `nao_conformidade_id` (`nao_conformidade_id`),
  CONSTRAINT `planos_acao_ibfk_1` FOREIGN KEY (`nao_conformidade_id`) REFERENCES `nao_conformidades` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `planos_acao`
--

LOCK TABLES `planos_acao` WRITE;
/*!40000 ALTER TABLE `planos_acao` DISABLE KEYS */;
/*!40000 ALTER TABLE `planos_acao` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `quantificacao_resultados`
--

DROP TABLE IF EXISTS `quantificacao_resultados`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `quantificacao_resultados` (
  `id` int NOT NULL AUTO_INCREMENT,
  `quantificacao_id` int NOT NULL,
  `limite_tolerancia` varchar(100) DEFAULT NULL,
  `resultado` varchar(100) DEFAULT NULL,
  `unidade` varchar(50) DEFAULT NULL,
  `acima_nr15` tinyint(1) DEFAULT '0',
  `acima_acgih` tinyint(1) DEFAULT '0',
  `conclusao` text,
  `recomendacao` text,
  PRIMARY KEY (`id`),
  KEY `quantificacao_id` (`quantificacao_id`),
  CONSTRAINT `quantificacao_resultados_ibfk_1` FOREIGN KEY (`quantificacao_id`) REFERENCES `quantificacoes` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `quantificacao_resultados`
--

LOCK TABLES `quantificacao_resultados` WRITE;
/*!40000 ALTER TABLE `quantificacao_resultados` DISABLE KEYS */;
/*!40000 ALTER TABLE `quantificacao_resultados` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `quantificacoes`
--

DROP TABLE IF EXISTS `quantificacoes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `quantificacoes` (
  `id` int NOT NULL AUTO_INCREMENT,
  `empresa_id` int NOT NULL,
  `visita_id` int NOT NULL,
  `unidade_id` int DEFAULT NULL,
  `setor_id` int DEFAULT NULL,
  `cargo_id` int DEFAULT NULL,
  `usuario_id` int NOT NULL,
  `risco_id` int NOT NULL,
  `metodologia` varchar(255) DEFAULT NULL,
  `equipamento` varchar(255) DEFAULT NULL,
  `estrategia_amostragem` text,
  `observacoes` text,
  `status` enum('PENDENTE','EM_ANALISE','CONCLUIDA') DEFAULT 'PENDENTE',
  `criado_em` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `empresa_id` (`empresa_id`),
  KEY `unidade_id` (`unidade_id`),
  KEY `setor_id` (`setor_id`),
  KEY `cargo_id` (`cargo_id`),
  KEY `risco_id` (`risco_id`),
  KEY `quantificacoes_ibfk_7` (`visita_id`),
  KEY `quantificacoes_ibfk_5` (`usuario_id`),
  CONSTRAINT `quantificacoes_ibfk_1` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`),
  CONSTRAINT `quantificacoes_ibfk_2` FOREIGN KEY (`unidade_id`) REFERENCES `unidades` (`id`),
  CONSTRAINT `quantificacoes_ibfk_3` FOREIGN KEY (`setor_id`) REFERENCES `setores` (`id`),
  CONSTRAINT `quantificacoes_ibfk_4` FOREIGN KEY (`cargo_id`) REFERENCES `cargos` (`id`),
  CONSTRAINT `quantificacoes_ibfk_5` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`),
  CONSTRAINT `quantificacoes_ibfk_6` FOREIGN KEY (`risco_id`) REFERENCES `riscos` (`id`),
  CONSTRAINT `quantificacoes_ibfk_7` FOREIGN KEY (`visita_id`) REFERENCES `visitas_tecnicas` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `quantificacoes`
--

LOCK TABLES `quantificacoes` WRITE;
/*!40000 ALTER TABLE `quantificacoes` DISABLE KEYS */;
/*!40000 ALTER TABLE `quantificacoes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `relatorios`
--

DROP TABLE IF EXISTS `relatorios`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `relatorios` (
  `id` int NOT NULL AUTO_INCREMENT,
  `empresa_id` int NOT NULL,
  `usuario_id` int NOT NULL,
  `titulo` varchar(255) DEFAULT NULL,
  `tipo` varchar(100) DEFAULT NULL,
  `conteudo` longtext,
  `arquivo_pdf` varchar(255) DEFAULT NULL,
  `criado_em` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `empresa_id` (`empresa_id`),
  KEY `relatorios_ibfk_2` (`usuario_id`),
  CONSTRAINT `relatorios_ibfk_1` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`),
  CONSTRAINT `relatorios_ibfk_2` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `relatorios`
--

LOCK TABLES `relatorios` WRITE;
/*!40000 ALTER TABLE `relatorios` DISABLE KEYS */;
/*!40000 ALTER TABLE `relatorios` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `riscos`
--

DROP TABLE IF EXISTS `riscos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `riscos` (
  `id` int NOT NULL AUTO_INCREMENT,
  `codigo` varchar(50) DEFAULT NULL,
  `codigo_externo` varchar(80) DEFAULT NULL,
  `categoria` enum('fisico','quimico','biologico','ergonomico','acidente','psicossocial') NOT NULL,
  `nome` varchar(150) NOT NULL,
  `tipo_avaliacao` enum('Qualitativo','Quantitativo','Qualitativo/Quantitativo') DEFAULT 'Qualitativo',
  `descricao` text,
  `normas_aplicaveis` text,
  `metodologia` text,
  `limite_nr15` varchar(100) DEFAULT NULL,
  `limite_acgih` varchar(100) DEFAULT NULL,
  `nivel_acao` varchar(100) DEFAULT NULL,
  `unidade_medida` varchar(50) DEFAULT NULL,
  `exige_quantificacao` tinyint(1) DEFAULT '0',
  `severidade_padrao` int DEFAULT '1',
  `probabilidade_padrao` int DEFAULT '1',
  `ativo` tinyint(1) DEFAULT '1',
  `criado_em` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `atualizado_em` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_riscos_codigo` (`codigo`),
  KEY `idx_riscos_categoria` (`categoria`),
  KEY `idx_riscos_nome` (`nome`),
  KEY `idx_riscos_ativo` (`ativo`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `riscos`
--

LOCK TABLES `riscos` WRITE;
/*!40000 ALTER TABLE `riscos` DISABLE KEYS */;
INSERT INTO `riscos` VALUES (1,'TESTE','TESTE','fisico','Teste','Qualitativo',NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,1,1,0,'2026-06-30 19:38:45','2026-06-30 20:22:02'),(2,'RIS-AE591643','EXT-RIS-20260630213308','fisico','Ruído','Qualitativo','Teste','Teste','Teste','85dB(A)',NULL,'80dB(A)','dB(A)',1,1,1,1,'2026-06-30 19:52:16','2026-06-30 19:52:16'),(3,'RIS-D30D8FD2','EXT-RIS-20260630215440','fisico','Calor','Quantitativo','Teste','Teste','teste','31.5',NULL,'17.0','ºC',1,1,1,1,'2026-06-30 19:55:39','2026-06-30 19:55:39'),(4,'RIS-FDAB4F77','EXT-RIS-20260630215805','fisico','Frio','Qualitativo','Teste','Teste','Teste',NULL,NULL,NULL,'ºC',0,1,1,1,'2026-06-30 19:58:38','2026-06-30 19:58:38'),(5,'RIS-A12FF94D','EXT-RIS-20260630221842','quimico','Hidróxido de Cálcio','Quantitativo','Teste','Teste','Teste','0,20',NULL,'0,10','ppm',1,1,1,1,'2026-06-30 20:19:24','2026-06-30 20:19:24');
/*!40000 ALTER TABLE `riscos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `setores`
--

DROP TABLE IF EXISTS `setores`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `setores` (
  `id` int NOT NULL AUTO_INCREMENT,
  `codigo` varchar(30) DEFAULT NULL,
  `codigo_externo` varchar(50) DEFAULT NULL,
  `nome` varchar(150) NOT NULL,
  `descricao` text,
  `ativo` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_setor_nome` (`nome`),
  UNIQUE KEY `uk_setor_codigo` (`codigo`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `setores`
--

LOCK TABLES `setores` WRITE;
/*!40000 ALTER TABLE `setores` DISABLE KEYS */;
INSERT INTO `setores` VALUES (1,NULL,NULL,'Administrativo',NULL,1,'2026-06-23 14:34:36','2026-06-23 14:34:36'),(2,'SET-6BD95E49','EXT-SET-20260629031709','Almoxarifado','Teste',1,'2026-06-29 01:17:23','2026-06-29 01:17:23');
/*!40000 ALTER TABLE `setores` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tipos_medidas_controle`
--

DROP TABLE IF EXISTS `tipos_medidas_controle`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tipos_medidas_controle` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nome` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `icone` varchar(80) DEFAULT NULL,
  `cor` varchar(30) DEFAULT NULL,
  `ativo` tinyint(1) DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_tipos_medidas_slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tipos_medidas_controle`
--

LOCK TABLES `tipos_medidas_controle` WRITE;
/*!40000 ALTER TABLE `tipos_medidas_controle` DISABLE KEYS */;
/*!40000 ALTER TABLE `tipos_medidas_controle` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `unidades`
--

DROP TABLE IF EXISTS `unidades`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `unidades` (
  `id` int NOT NULL AUTO_INCREMENT,
  `empresa_id` int DEFAULT NULL,
  `codigo` varchar(50) DEFAULT NULL,
  `codigo_externo` varchar(80) DEFAULT NULL,
  `nome` varchar(150) NOT NULL,
  `razao_social` varchar(200) DEFAULT NULL,
  `nome_fantasia` varchar(200) DEFAULT NULL,
  `cnpj` varchar(20) DEFAULT NULL,
  `inscricao_estadual` varchar(50) DEFAULT NULL,
  `cnae` varchar(30) DEFAULT NULL,
  `descricao_cnae` varchar(255) DEFAULT NULL,
  `grau_risco` varchar(10) DEFAULT NULL,
  `quantidade_funcionarios` int DEFAULT NULL,
  `endereco` text,
  `numero` varchar(20) DEFAULT NULL,
  `complemento` varchar(100) DEFAULT NULL,
  `bairro` varchar(100) DEFAULT NULL,
  `cidade` varchar(100) DEFAULT NULL,
  `estado` varchar(2) DEFAULT NULL,
  `cep` varchar(20) DEFAULT NULL,
  `telefone` varchar(30) DEFAULT NULL,
  `contato_responsavel` varchar(30) DEFAULT NULL,
  `email` varchar(150) DEFAULT NULL,
  `responsavel` varchar(150) DEFAULT NULL,
  `tecnico_responsavel` varchar(150) DEFAULT NULL,
  `supervisor_responsavel` varchar(150) DEFAULT NULL,
  `periodicidade_visitas` varchar(50) DEFAULT NULL,
  `observacoes` text,
  `cargo_responsavel` varchar(150) DEFAULT NULL,
  `ativo` tinyint(1) DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_unidade_codigo` (`codigo`),
  KEY `fk_unidades_empresas` (`empresa_id`),
  CONSTRAINT `fk_unidades_empresas` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `unidades`
--

LOCK TABLES `unidades` WRITE;
/*!40000 ALTER TABLE `unidades` DISABLE KEYS */;
INSERT INTO `unidades` VALUES (1,NULL,NULL,NULL,'Santiago Saúde e Segurança Ocupacional Ltda',NULL,NULL,'66.863.625/0001-95',NULL,NULL,NULL,NULL,NULL,'Av. das Nações, 2500 - Distrito Industrial',NULL,NULL,NULL,'Araucária','PR',NULL,'(41) 99795-9893',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,'2026-06-23 14:38:58','2026-06-23 14:38:58');
/*!40000 ALTER TABLE `unidades` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `usuarios`
--

DROP TABLE IF EXISTS `usuarios`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `usuarios` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nome` varchar(150) NOT NULL,
  `email` varchar(150) NOT NULL,
  `senha` varchar(255) NOT NULL,
  `telefone` varchar(30) DEFAULT NULL,
  `tipo` enum('ADMIN','TECNICO','CLIENTE','VISUALIZADOR') DEFAULT 'TECNICO',
  `registro_profissional` varchar(100) DEFAULT NULL,
  `conselho` varchar(50) DEFAULT NULL,
  `uf` char(2) DEFAULT NULL,
  `assinatura` text,
  `ativo` tinyint(1) DEFAULT '1',
  `ultimo_login` datetime DEFAULT NULL,
  `criado_em` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `ultimo_acesso` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `usuarios`
--

LOCK TABLES `usuarios` WRITE;
/*!40000 ALTER TABLE `usuarios` DISABLE KEYS */;
INSERT INTO `usuarios` VALUES (13,'Administrador','admin@seudominio.com','$2y$10$D.E3MbeG.DRI2bgc2X2sdewXTr32gDnAE9hCDMIqbk9gnoY7ocaL.',NULL,'ADMIN',NULL,NULL,NULL,NULL,1,NULL,'2026-06-14 17:24:31','2026-07-08 16:49:44'),(17,'Marcos Guilherme Rutz','tecnico1@ssosantiago.com.br','$2y$10$BycBeSMajVIaTKGWnkItq.eVRNx/dnsK6eSEjWjKzf9CjDYaJEsMO',NULL,'TECNICO',NULL,NULL,NULL,NULL,1,NULL,'2026-06-17 17:07:09',NULL);
/*!40000 ALTER TABLE `usuarios` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `veiculos`
--

DROP TABLE IF EXISTS `veiculos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `veiculos` (
  `id` int NOT NULL AUTO_INCREMENT,
  `modelo` varchar(100) NOT NULL,
  `placa` varchar(20) NOT NULL,
  `cor` varchar(50) DEFAULT NULL,
  `ativo` tinyint(1) DEFAULT '1',
  `criado_em` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `placa` (`placa`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `veiculos`
--

LOCK TABLES `veiculos` WRITE;
/*!40000 ALTER TABLE `veiculos` DISABLE KEYS */;
INSERT INTO `veiculos` VALUES (2,'Fiat Uno Mille','ABC3242','Vermelho',1,'2026-06-17 19:32:43'),(3,'Wolksvagen Gol','ABC3A42','Branco',1,'2026-06-22 17:11:09'),(4,'Renault Clio','BBC4520','Preto',1,'2026-06-29 01:18:31');
/*!40000 ALTER TABLE `veiculos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `visita_historico`
--

DROP TABLE IF EXISTS `visita_historico`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `visita_historico` (
  `id` int NOT NULL AUTO_INCREMENT,
  `visita_id` int NOT NULL,
  `usuario_id` int DEFAULT NULL,
  `acao` varchar(100) NOT NULL,
  `status_anterior` varchar(50) DEFAULT NULL,
  `status_novo` varchar(50) DEFAULT NULL,
  `motivo` text,
  `criado_em` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `visita_id` (`visita_id`),
  KEY `usuario_id` (`usuario_id`),
  CONSTRAINT `visita_historico_ibfk_1` FOREIGN KEY (`visita_id`) REFERENCES `visitas_tecnicas` (`id`) ON DELETE CASCADE,
  CONSTRAINT `visita_historico_ibfk_2` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `visita_historico`
--

LOCK TABLES `visita_historico` WRITE;
/*!40000 ALTER TABLE `visita_historico` DISABLE KEYS */;
/*!40000 ALTER TABLE `visita_historico` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `visitas_tecnicas`
--

DROP TABLE IF EXISTS `visitas_tecnicas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `visitas_tecnicas` (
  `id` int NOT NULL AUTO_INCREMENT,
  `empresa_id` int NOT NULL,
  `unidade_id` int DEFAULT NULL,
  `usuario_id` int NOT NULL,
  `data_visita` date NOT NULL,
  `hora_visita` time DEFAULT NULL,
  `veiculo_id` int DEFAULT NULL,
  `responsavel_acompanhamento` varchar(150) DEFAULT NULL,
  `objetivo` text,
  `observacoes` text,
  `latitude` varchar(50) DEFAULT NULL,
  `longitude` varchar(50) DEFAULT NULL,
  `assinatura_cliente` text,
  `assinatura_tecnico` text,
  `status` enum('ABERTA','AGENDADA','CONFIRMADA','EM_ANDAMENTO','CHECKLIST_INICIADO','FINALIZADA','CANCELADA','EXCLUIDA') DEFAULT 'ABERTA',
  `criado_em` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `empresa_id` (`empresa_id`),
  KEY `unidade_id` (`unidade_id`),
  KEY `visitas_tecnicas_ibfk_4` (`veiculo_id`),
  KEY `visitas_tecnicas_ibfk_3` (`usuario_id`),
  CONSTRAINT `visitas_tecnicas_ibfk_1` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`),
  CONSTRAINT `visitas_tecnicas_ibfk_2` FOREIGN KEY (`unidade_id`) REFERENCES `unidades` (`id`),
  CONSTRAINT `visitas_tecnicas_ibfk_3` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`),
  CONSTRAINT `visitas_tecnicas_ibfk_4` FOREIGN KEY (`veiculo_id`) REFERENCES `veiculos` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `visitas_tecnicas`
--

LOCK TABLES `visitas_tecnicas` WRITE;
/*!40000 ALTER TABLE `visitas_tecnicas` DISABLE KEYS */;
INSERT INTO `visitas_tecnicas` VALUES (3,9,1,17,'2026-06-18','10:00:00',2,'Luciano','Visita técnica',NULL,NULL,NULL,NULL,NULL,'ABERTA','2026-06-18 00:18:54'),(4,10,1,13,'2026-06-18','14:00:00',2,'Robson','Teste',NULL,NULL,NULL,NULL,NULL,'ABERTA','2026-06-18 00:24:14'),(5,9,NULL,17,'2026-06-27','10:00:00',2,'','','',NULL,NULL,NULL,NULL,'CANCELADA','2026-06-18 00:28:49'),(6,9,1,13,'2026-06-20','09:00:00',2,'Fernanda','Teste3',NULL,NULL,NULL,NULL,NULL,'ABERTA','2026-06-18 00:32:48'),(7,10,1,13,'2026-06-21','13:00:00',2,'Fer','Teste',NULL,NULL,NULL,NULL,NULL,'ABERTA','2026-06-18 00:37:23'),(8,10,NULL,17,'2026-06-19','14:00:00',2,'Fernanda','Teste',NULL,NULL,NULL,NULL,NULL,'FINALIZADA','2026-06-18 00:59:09'),(9,9,NULL,13,'2026-07-01','10:00:00',2,'','','',NULL,NULL,NULL,NULL,'CANCELADA','2026-06-22 13:30:58'),(10,9,1,13,'2026-06-22','15:00:00',2,'Teste','Teste',NULL,NULL,NULL,NULL,NULL,'ABERTA','2026-06-22 16:24:28'),(11,10,1,13,'2026-06-22','15:00:00',2,'Hoje','Teste novo',NULL,NULL,NULL,NULL,NULL,'FINALIZADA','2026-06-22 16:58:22'),(12,9,NULL,17,'2026-06-25','16:30:00',3,'','','',NULL,NULL,NULL,NULL,'CANCELADA','2026-06-22 17:23:14'),(13,9,1,17,'2026-06-22','17:45:00',2,'Teste','Teste','Teste',NULL,NULL,NULL,NULL,'ABERTA','2026-06-22 17:27:28');
/*!40000 ALTER TABLE `visitas_tecnicas` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-07-12 13:42:05
