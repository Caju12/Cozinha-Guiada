-- ==============================================================================
-- PROJETO: Cozinha Guiada
-- ARQUIVO: database/schema.sql
-- DESCRIÇÃO: Criação do banco de dados e de todas as tabelas normalizadas.
-- ATENDE AOS REQUISITOS: RF01 a RF53, RNF05, RNF06, RNF11, RNF14, RNF18, RNF19
-- ==============================================================================

CREATE DATABASE IF NOT EXISTS `cozinha_guiada`
  DEFAULT CHARACTER SET utf8mb4
  DEFAULT COLLATE utf8mb4_unicode_ci;

USE `cozinha_guiada`;

-- Desativa temporariamente a verificação de chaves estrangeiras para permitir recriação limpa
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS `denuncias`;
DROP TABLE IF EXISTS `avaliacoes`;
DROP TABLE IF EXISTS `comentarios`;
DROP TABLE IF EXISTS `historico_receitas`;
DROP TABLE IF EXISTS `favoritos`;
DROP TABLE IF EXISTS `receita_categorias`;
DROP TABLE IF EXISTS `receita_compatibilidades`;
DROP TABLE IF EXISTS `receita_utensilios`;
DROP TABLE IF EXISTS `utensilios`;
DROP TABLE IF EXISTS `substituicoes_ingrediente`;
DROP TABLE IF EXISTS `receita_ingredientes`;
DROP TABLE IF EXISTS `ingredientes`;
DROP TABLE IF EXISTS `imagens_receita`;
DROP TABLE IF EXISTS `etapas_receita`;
DROP TABLE IF EXISTS `receitas`;
DROP TABLE IF EXISTS `categorias`;
DROP TABLE IF EXISTS `usuario_preferencias`;
DROP TABLE IF EXISTS `compatibilidades_alimentares`;
DROP TABLE IF EXISTS `usuarios`;

SET FOREIGN_KEY_CHECKS = 1;

-- ------------------------------------------------------------------------------
-- 1. TABELA DE USUÁRIOS (RF04, RF05, RF06, RF30, RF46, RF47, RF53, RNF06, RNF13)
-- ------------------------------------------------------------------------------
CREATE TABLE `usuarios` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `nome` VARCHAR(120) NOT NULL,
  `email` VARCHAR(180) NOT NULL UNIQUE,
  `senha` VARCHAR(255) NOT NULL, -- Armazenada via password_hash() (RNF06)
  `perfil` ENUM('visitante', 'cadastrado', 'produtor', 'administrador') NOT NULL DEFAULT 'cadastrado',
  `bio` TEXT NULL, -- Informações públicas do produtor (RF53)
  `ativo` TINYINT(1) NOT NULL DEFAULT 1, -- Para bloqueio/moderação pelo admin (RF46)
  `criado_em` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `atualizado_em` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ------------------------------------------------------------------------------
-- 2. TABELA DE COMPATIBILIDADES ALIMENTARES (RF07, RF10, RF41, RNF19)
-- Permite inserção de novas compatibilidades sem alterar o esquema (RNF19).
-- ------------------------------------------------------------------------------
CREATE TABLE `compatibilidades_alimentares` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `nome` VARCHAR(60) NOT NULL UNIQUE, -- 'Vegetariana', 'Vegana', 'Sem lactose', 'Sem glúten'
  `slug` VARCHAR(60) NOT NULL UNIQUE,
  `descricao` VARCHAR(255) NULL
) ENGINE=InnoDB;

-- ------------------------------------------------------------------------------
-- 3. PREFERÊNCIAS ALIMENTARES DO USUÁRIO (RF07, CA07)
-- Relação N:N entre Usuário e Compatibilidade
-- ------------------------------------------------------------------------------
CREATE TABLE `usuario_preferencias` (
  `usuario_id` INT NOT NULL,
  `compatibilidade_id` INT NOT NULL,
  PRIMARY KEY (`usuario_id`, `compatibilidade_id`),
  CONSTRAINT `fk_pref_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_pref_compatibilidade` FOREIGN KEY (`compatibilidade_id`) REFERENCES `compatibilidades_alimentares` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ------------------------------------------------------------------------------
-- 4. CATEGORIAS DE RECEITAS (RF51, CA51)
-- ------------------------------------------------------------------------------
CREATE TABLE `categorias` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `nome` VARCHAR(80) NOT NULL UNIQUE,
  `slug` VARCHAR(80) NOT NULL UNIQUE,
  `descricao` VARCHAR(255) NULL,
  `criado_em` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ------------------------------------------------------------------------------
-- 5. RECEITAS (RF01, RF12, RF16, RF17, RF31, RF34, RF36, RF38, RF39, RF40, RF48, RF50)
-- ------------------------------------------------------------------------------
CREATE TABLE `receitas` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `usuario_id` INT NOT NULL, -- Produtor autor da receita
  `titulo` VARCHAR(150) NOT NULL,
  `descricao` TEXT NOT NULL,
  `tempo_preparo_minutos` INT NOT NULL DEFAULT 0, -- RF16
  `rendimento` VARCHAR(60) NULL, -- ex: "4 porções"
  `temperatura` VARCHAR(50) NULL, -- RF17 (ex: "180°C")
  `intensidade_fogo` VARCHAR(50) NULL, -- RF17 (ex: "Fogo médio")
  `ponto_esperado` VARCHAR(120) NULL, -- RF17 (ex: "Dourado por fora e macio por dentro")
  `visibilidade` ENUM('publica', 'privada') NOT NULL DEFAULT 'publica', -- RF40
  `status_moderacao` ENUM('ativo', 'removido') NOT NULL DEFAULT 'ativo', -- RF50
  `criado_em` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `atualizado_em` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT `fk_receita_autor` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ------------------------------------------------------------------------------
-- 6. ETAPAS DO MODO DE PREPARO (RF15, RF18, RF33)
-- ------------------------------------------------------------------------------
CREATE TABLE `etapas_receita` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `receita_id` INT NOT NULL,
  `ordem` INT NOT NULL DEFAULT 1,
  `descricao` TEXT NOT NULL,
  CONSTRAINT `fk_etapa_receita` FOREIGN KEY (`receita_id`) REFERENCES `receitas` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ------------------------------------------------------------------------------
-- 7. IMAGENS DA RECEITA (RF35, RF36)
-- ------------------------------------------------------------------------------
CREATE TABLE `imagens_receita` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `receita_id` INT NOT NULL,
  `caminho` VARCHAR(255) NOT NULL,
  `ordem` INT NOT NULL DEFAULT 1,
  `criado_em` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT `fk_imagem_receita` FOREIGN KEY (`receita_id`) REFERENCES `receitas` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ------------------------------------------------------------------------------
-- 8. CATÁLOGO GERAL DE INGREDIENTES (RF08, RF32)
-- ------------------------------------------------------------------------------
CREATE TABLE `ingredientes` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `nome` VARCHAR(100) NOT NULL UNIQUE
) ENGINE=InnoDB;

-- ------------------------------------------------------------------------------
-- 9. INGREDIENTES DA RECEITA COM QUANTIDADE E UNIDADE (RF14, RF32)
-- Relação N:N entre Receita e Ingrediente
-- ------------------------------------------------------------------------------
CREATE TABLE `receita_ingredientes` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `receita_id` INT NOT NULL,
  `ingrediente_id` INT NOT NULL,
  `quantidade` DECIMAL(10, 2) NULL,
  `unidade` VARCHAR(50) NULL, -- ex: "g", "ml", "xícaras", "colheres de sopa", "unidades"
  `observacao` VARCHAR(120) NULL, -- ex: "picado finamente", "em temperatura ambiente"
  CONSTRAINT `fk_rec_ing_receita` FOREIGN KEY (`receita_id`) REFERENCES `receitas` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_rec_ing_ingrediente` FOREIGN KEY (`ingrediente_id`) REFERENCES `ingredientes` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ------------------------------------------------------------------------------
-- 10. ALTERNATIVAS DE SUBSTITUIÇÃO DE INGREDIENTES (RF13, RF43, RF44, RF45)
-- ------------------------------------------------------------------------------
CREATE TABLE `substituicoes_ingrediente` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `receita_ingrediente_id` INT NOT NULL,
  `ingrediente_substituto_id` INT NULL, -- Opcional: link com ingrediente existente
  `texto_substituto` VARCHAR(150) NOT NULL, -- ex: "Leite de amêndoas ou leite de aveia"
  `proporcao` VARCHAR(100) NULL, -- ex: "Mesma quantidade (1:1)"
  `observacao` TEXT NULL,
  CONSTRAINT `fk_subst_rec_ing` FOREIGN KEY (`receita_ingrediente_id`) REFERENCES `receita_ingredientes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_subst_ingrediente` FOREIGN KEY (`ingrediente_substituto_id`) REFERENCES `ingredientes` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ------------------------------------------------------------------------------
-- 11. UTENSÍLIOS CULINÁRIOS (RF16, RF34)
-- ------------------------------------------------------------------------------
CREATE TABLE `utensilios` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `nome` VARCHAR(100) NOT NULL UNIQUE
) ENGINE=InnoDB;

CREATE TABLE `receita_utensilios` (
  `receita_id` INT NOT NULL,
  `utensilio_id` INT NOT NULL,
  PRIMARY KEY (`receita_id`, `utensilio_id`),
  CONSTRAINT `fk_rec_ut_receita` FOREIGN KEY (`receita_id`) REFERENCES `receitas` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_rec_ut_utensilio` FOREIGN KEY (`utensilio_id`) REFERENCES `utensilios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ------------------------------------------------------------------------------
-- 12. COMPATIBILIDADES DA RECEITA (RF10, RF11, RF12, RF41, RF42)
-- Relação N:N entre Receita e Compatibilidade Alimentar
-- ------------------------------------------------------------------------------
CREATE TABLE `receita_compatibilidades` (
  `receita_id` INT NOT NULL,
  `compatibilidade_id` INT NOT NULL,
  PRIMARY KEY (`receita_id`, `compatibilidade_id`),
  CONSTRAINT `fk_rec_comp_receita` FOREIGN KEY (`receita_id`) REFERENCES `receitas` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_rec_comp_compat` FOREIGN KEY (`compatibilidade_id`) REFERENCES `compatibilidades_alimentares` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ------------------------------------------------------------------------------
-- 13. CATEGORIZAÇÃO DAS RECEITAS (RF03, RF51)
-- Relação N:N entre Receita e Categoria
-- ------------------------------------------------------------------------------
CREATE TABLE `receita_categorias` (
  `receita_id` INT NOT NULL,
  `categoria_id` INT NOT NULL,
  PRIMARY KEY (`receita_id`, `categoria_id`),
  CONSTRAINT `fk_rec_cat_receita` FOREIGN KEY (`receita_id`) REFERENCES `receitas` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_rec_cat_categoria` FOREIGN KEY (`categoria_id`) REFERENCES `categorias` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ------------------------------------------------------------------------------
-- 14. RECEITAS FAVORITAS (RF20, RF21, RF22)
-- ------------------------------------------------------------------------------
CREATE TABLE `favoritos` (
  `usuario_id` INT NOT NULL,
  `receita_id` INT NOT NULL,
  `criado_em` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`usuario_id`, `receita_id`),
  CONSTRAINT `fk_fav_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_fav_receita` FOREIGN KEY (`receita_id`) REFERENCES `receitas` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ------------------------------------------------------------------------------
-- 15. HISTÓRICO DE VISUALIZAÇÕES (RF23, RF24)
-- ------------------------------------------------------------------------------
CREATE TABLE `historico_receitas` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `usuario_id` INT NOT NULL,
  `receita_id` INT NOT NULL,
  `visualizado_em` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT `fk_hist_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_hist_receita` FOREIGN KEY (`receita_id`) REFERENCES `receitas` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ------------------------------------------------------------------------------
-- 16. COMENTÁRIOS NAS RECEITAS (RF25, RF26, RF27, RF49, RF50)
-- ------------------------------------------------------------------------------
CREATE TABLE `comentarios` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `usuario_id` INT NOT NULL,
  `receita_id` INT NOT NULL,
  `texto` TEXT NOT NULL,
  `status` ENUM('ativo', 'removido') NOT NULL DEFAULT 'ativo',
  `criado_em` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `atualizado_em` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT `fk_coment_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_coment_receita` FOREIGN KEY (`receita_id`) REFERENCES `receitas` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ------------------------------------------------------------------------------
-- 17. AVALIAÇÕES (NOTAS DE 1 A 5) (RF28, CA28)
-- ------------------------------------------------------------------------------
CREATE TABLE `avaliacoes` (
  `usuario_id` INT NOT NULL,
  `receita_id` INT NOT NULL,
  `nota` TINYINT UNSIGNED NOT NULL,
  `criado_em` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `atualizado_em` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`usuario_id`, `receita_id`),
  CONSTRAINT `chk_nota_intervalo` CHECK (`nota` BETWEEN 1 AND 5),
  CONSTRAINT `fk_aval_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_aval_receita` FOREIGN KEY (`receita_id`) REFERENCES `receitas` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ------------------------------------------------------------------------------
-- 18. DENÚNCIAS DE RECEITAS E COMENTÁRIOS (RF29, RF52, CA29, CA52)
-- ------------------------------------------------------------------------------
CREATE TABLE `denuncias` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `usuario_id` INT NOT NULL, -- Quem denunciou
  `tipo_alvo` ENUM('receita', 'comentario') NOT NULL,
  `alvo_id` INT NOT NULL, -- ID da receita ou do comentário denunciado
  `motivo` TEXT NOT NULL,
  `status` ENUM('pendente', 'analisada', 'descartada', 'conteudo_removido') NOT NULL DEFAULT 'pendente',
  `resolvido_por` INT NULL, -- Administrador que moderou
  `resolvido_em` TIMESTAMP NULL,
  `criado_em` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT `fk_denuncia_autor` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_denuncia_admin` FOREIGN KEY (`resolvido_por`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB;
