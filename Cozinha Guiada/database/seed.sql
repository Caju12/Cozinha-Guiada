-- ==============================================================================
-- PROJETO: Cozinha Guiada
-- ARQUIVO: database/seed.sql
-- DESCRIÇÃO: Dados iniciais para categorias, compatibilidades, usuário admin,
--            produtor de teste e receitas de exemplo completas.
-- ==============================================================================

USE `cozinha_guiada`;

-- 1. COMPATIBILIDADES ALIMENTARES OBRIGATÓRIAS (RF07, RF10, RF41, RNF19)
INSERT INTO `compatibilidades_alimentares` (`id`, `nome`, `slug`, `descricao`) VALUES
(1, 'Vegetariana', 'vegetariana', 'Não contém carnes, peixes ou frutos do mar.'),
(2, 'Vegana', 'vegana', 'Não contém nenhum produto ou ingrediente de origem animal.'),
(3, 'Sem lactose', 'sem-lactose', 'Livre de leite e derivados com lactose.'),
(4, 'Sem glúten', 'sem-gluten', 'Não contém trigo, aveia com contaminação, cevada ou centeio.');

-- 2. CATEGORIAS DE RECEITAS (RF51)
INSERT INTO `categorias` (`id`, `nome`, `slug`, `descricao`) VALUES
(1, 'Bolos e Tortas', 'bolos-e-tortas', 'Receitas de bolos caseiros, recheados e tortas doces e salgadas.'),
(2, 'Massas', 'massas', 'Macarrões, lasanhas, nhoques e molhos especiais.'),
(3, 'Carnes e Aves', 'carnes-e-aves', 'Preparo prático de carnes bovinas, suínas e aves.'),
(4, 'Sobremesas', 'sobremesas', 'Doces, mousses, pudins e sobremesas fáceis.'),
(5, 'Sopas e Caldos', 'sopas-e-caldos', 'Caldos revigorantes e sopas nutritivas.'),
(6, 'Lanches Rápidos', 'lanches-rapidos', 'Sanduíches, crepiocas, snacks e opções rápidas.'),
(7, 'Saladas e Entradas', 'saladas-e-entradas', 'Saladas frescas, molhos e aperitivos leves.'),
(8, 'Bebidas e Sucos', 'bebidas-e-sucos', 'Sucos naturais, vitaminas e smoothies.');

-- 3. UTENSÍLIOS BÁSICOS (RF16, RF34)
INSERT INTO `utensilios` (`id`, `nome`) VALUES
(1, 'Liquidificador'),
(2, 'Forno'),
(3, 'Frigideira antiaderente'),
(4, 'Panela média'),
(5, 'Forma com furo central'),
(6, 'Espátula de silicone'),
(7, 'Faca de chef'),
(8, 'Tábua de corte'),
(9, 'Batedeira'),
(10, 'Tigela grande');

-- 4. USUÁRIOS INICIAIS
-- Senha padrão para todos os 3 usuários iniciais: Admin@2026
-- Hash bcrypt gerado de forma segura via password_hash('Admin@2026', PASSWORD_BCRYPT)
INSERT INTO `usuarios` (`id`, `nome`, `email`, `senha`, `perfil`, `bio`, `ativo`) VALUES
(1, 'Administrador do Sistema', 'admin@cozinhaguiada.com', '$2y$10$p2n4VxrCRTAkroI6/mjBAOTczguZVX9TTR6.MQRKzXgx7Ysa0pUaW', 'administrador', 'Equipe de moderação e suporte da plataforma Cozinha Guiada.', 1),
(2, 'Chef André Luiz', 'chef@cozinhaguiada.com', '$2y$10$p2n4VxrCRTAkroI6/mjBAOTczguZVX9TTR6.MQRKzXgx7Ysa0pUaW', 'produtor', 'Especialista em culinária prática para iniciantes e receitas inclusivas.', 1),
(3, 'Lucas Iniciante', 'leitor@cozinhaguiada.com', '$2y$10$p2n4VxrCRTAkroI6/mjBAOTczguZVX9TTR6.MQRKzXgx7Ysa0pUaW', 'cadastrado', 'Aprendendo a cozinhar pratos fáceis no dia a dia.', 1);

-- Preferência alimentar do usuário cadastrado (ex: Sem lactose)
INSERT INTO `usuario_preferencias` (`usuario_id`, `compatibilidade_id`) VALUES
(3, 3);

-- 5. CATÁLOGO INICIAL DE INGREDIENTES (RF08)
INSERT INTO `ingredientes` (`id`, `nome`) VALUES
(1, 'Cenoura'),
(2, 'Ovo'),
(3, 'Óleo de girassol'),
(4, 'Açúcar'),
(5, 'Farinha de trigo'),
(6, 'Fermento em pó químico'),
(7, 'Chocolate em pó'),
(8, 'Leite condensado'),
(9, 'Manteiga'),
(10, 'Banana madura'),
(11, 'Aveia em flocos finos'),
(12, 'Canela em pó'),
(13, 'Leite de amêndoas'),
(14, 'Farinha de arroz'),
(15, 'Azeite de oliva'),
(16, 'Alho'),
(17, 'Cebola'),
(18, 'Tomate'),
(19, 'Sal');

-- 6. RECEITA 1: Bolo de Cenoura Fofinho com Chocolate (Vegetariana)
INSERT INTO `receitas` (`id`, `usuario_id`, `titulo`, `descricao`, `tempo_preparo_minutos`, `rendimento`, `temperatura`, `intensidade_fogo`, `ponto_esperado`, `visibilidade`, `status_moderacao`) VALUES
(1, 2, 'Bolo de Cenoura Fofinho com Chocolate', 'Um clássico irresistível da culinária brasileira, super macio e com cobertura crocante de chocolate.', 45, '12 fatias', '180°C', 'Fogo médio para a cobertura', 'Palito limpo ao espetar no centro', 'publica', 'ativo');

-- Categorias da Receita 1
INSERT INTO `receita_categorias` (`receita_id`, `categoria_id`) VALUES (1, 1), (1, 4);

-- Compatibilidade da Receita 1 (Vegetariana)
INSERT INTO `receita_compatibilidades` (`receita_id`, `compatibilidade_id`) VALUES (1, 1);

-- Utensílios da Receita 1
INSERT INTO `receita_utensilios` (`receita_id`, `utensilio_id`) VALUES (1, 1), (1, 2), (1, 5), (1, 6);

-- Ingredientes da Receita 1
INSERT INTO `receita_ingredientes` (`id`, `receita_id`, `ingrediente_id`, `quantidade`, `unidade`, `observacao`) VALUES
(1, 1, 1, 3.00, 'unidades', 'médias, descascadas e picadas'),
(2, 1, 2, 4.00, 'unidades', 'inteiros'),
(3, 1, 3, 0.50, 'xícara', 'ou óleo de milho'),
(4, 1, 4, 2.00, 'xícaras', 'açúcar refinado'),
(5, 1, 5, 2.50, 'xícaras', 'peneiradas'),
(6, 1, 6, 1.00, 'colher de sopa', 'de fermento químico'),
(7, 1, 7, 4.00, 'colheres de sopa', 'para a cobertura'),
(8, 1, 8, 1.00, 'lata', 'para a cobertura'),
(9, 1, 9, 1.00, 'colher de sopa', 'para a cobertura');

-- Substituição do ingrediente trigo por farinha de arroz (RF13, RF43)
INSERT INTO `substituicoes_ingrediente` (`id`, `receita_ingrediente_id`, `ingrediente_substituto_id`, `texto_substituto`, `proporcao`, `observacao`) VALUES
(1, 5, 14, 'Farinha de arroz ou mix sem glúten', 'Mesma proporção (2.5 xícaras)', 'Adicionar 1/2 colher de chá de goma xantana para melhor estrutura se desejar versão sem glúten.');

-- Etapas da Receita 1 (RF15, RF18, RF33)
INSERT INTO `etapas_receita` (`id`, `receita_id`, `ordem`, `descricao`) VALUES
(1, 1, 1, 'Preaqueça o forno a 180°C e unte uma forma com furo central com manteiga e farinha.'),
(2, 1, 2, 'No liquidificador, bata as cenouras picadas, os ovos e o óleo até obter uma mistura completamente homogênea (cerca de 3 minutos).'),
(3, 1, 3, 'Transfira a mistura para uma tigela grande e adicione o açúcar, mexendo bem com uma espátula.'),
(4, 1, 4, 'Acrescente a farinha de trigo aos poucos, misturando delicadamente até incorporar. Por último, adicione o fermento e mexa de forma suave.'),
(5, 1, 5, 'Despeje a massa na forma untada e asse por 35 a 40 minutos a 180°C. Faça o teste do palito no centro antes de retirar do forno.'),
(6, 1, 6, 'Para a cobertura: em uma panela pequena, misture o leite condensado, o chocolate em pó e a manteiga. Cozinhe em fogo baixo mexendo sempre até soltar do fundo da panela.'),
(7, 1, 7, 'Desenforme o bolo já morno e despeje a cobertura de chocolate ainda quente por cima.');

-- 7. RECEITA 2: Panqueca de Banana e Aveia (Vegana, Sem lactose, Sem glúten, Vegetariana)
INSERT INTO `receitas` (`id`, `usuario_id`, `titulo`, `descricao`, `tempo_preparo_minutos`, `rendimento`, `temperatura`, `intensidade_fogo`, `ponto_esperado`, `visibilidade`, `status_moderacao`) VALUES
(2, 2, 'Panqueca de Banana e Aveia Fit', 'Uma opção saudável, rápida e naturalmente doce para café da manhã ou lanche.', 15, '4 panquecas médias', 'Fogo baixo', 'Fogo baixo para não queimar', 'Superfície com bolhinhas e dourada embaixo', 'publica', 'ativo');

-- Categorias da Receita 2
INSERT INTO `receita_categorias` (`receita_id`, `categoria_id`) VALUES (2, 6);

-- Compatibilidades da Receita 2 (Atende a TODOS os 4 filtros!)
INSERT INTO `receita_compatibilidades` (`receita_id`, `compatibilidade_id`) VALUES
(2, 1), -- Vegetariana
(2, 2), -- Vegana
(2, 3), -- Sem lactose
(2, 4); -- Sem glúten (com aveia certificada)

-- Utensílios da Receita 2
INSERT INTO `receita_utensilios` (`receita_id`, `utensilio_id`) VALUES (2, 3), (2, 6), (2, 10);

-- Ingredientes da Receita 2
INSERT INTO `receita_ingredientes` (`id`, `receita_id`, `ingrediente_id`, `quantidade`, `unidade`, `observacao`) VALUES
(10, 2, 10, 2.00, 'unidades', 'bananas bem maduras amassadas'),
(11, 2, 11, 1.00, 'xícara', 'aveia em flocos finos (sem glúten)'),
(12, 2, 12, 1.00, 'colher de café', 'canela em pó'),
(13, 2, 13, 50.00, 'ml', 'leite vegetal se necessário para dar ponto');

-- Etapas da Receita 2
INSERT INTO `etapas_receita` (`id`, `receita_id`, `ordem`, `descricao`) VALUES
(8, 2, 1, 'Em uma tigela, amasse muito bem as bananas com auxílio de um garfo até formar um purê liso.'),
(9, 2, 2, 'Adicione a aveia e a canela, misturando com uma espátula até obter uma massa espessa e homogênea.'),
(10, 2, 3, 'Aqueça uma frigideira antiaderente untada com um pingo de óleo de coco ou azeite em fogo bem baixo.'),
(11, 2, 4, 'Coloque porções de 2 colheres de sopa da massa na frigideira, modelando discos redondos.'),
(12, 2, 5, 'Deixe dourar por cerca de 2 a 3 minutos até formar pequenas bolhas na superfície. Vire com a espátula com cuidado e doure o outro lado por mais 1 a 2 minutos.');

-- 8. AVALIAÇÕES E COMENTÁRIOS DE EXEMPLO (RF25, RF28)
INSERT INTO `avaliacoes` (`usuario_id`, `receita_id`, `nota`) VALUES
(3, 1, 5),
(3, 2, 5);

INSERT INTO `comentarios` (`id`, `usuario_id`, `receita_id`, `texto`, `status`) VALUES
(1, 3, 1, 'Fiz essa receita no fim de semana e o bolo ficou extremamente fofinho! As instruções foram muito claras para um iniciante como eu.', 'ativo'),
(2, 3, 2, 'Adorei a praticidade! Perfeito para quem busca algo saudável e sem lactose.', 'ativo');
