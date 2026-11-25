-- ==============================================================================
-- PROJETO TCC: SKETCHBOOK EDUCACIONAL INTERATIVO (ENSINO FUNDAMENTAL 1)
-- SCRIPT SQL ÚNICO: RECRIAÇÃO TOTAL DA ESTRUTURA E INSERÇÃO DE DADOS (SEEDING)
-- Arquivo: banco/banco_completo.sql
-- Compatível com: MySQL 5.7+, MySQL 8.x, MariaDB, Linux (cPanel/ext4) e Windows (Laragon)
-- ==============================================================================

-- ------------------------------------------------------------------------------
-- CONFIGURAÇÕES DE SESSÃO E CARACTERES UNIVERSAIS
-- ------------------------------------------------------------------------------
SET NAMES utf8mb4;
SET CHARACTER SET utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

-- ------------------------------------------------------------------------------
-- 0. CRIAÇÃO E SELEÇÃO DO BANCO DE DADOS
-- ------------------------------------------------------------------------------
CREATE DATABASE IF NOT EXISTS `tccdb`
  DEFAULT CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE `tccdb`;

-- ------------------------------------------------------------------------------
-- 1. LIMPEZA PREVENTIVA DE TABELAS (DROP NA ORDEM CORRETA)
-- ------------------------------------------------------------------------------
DROP TABLE IF EXISTS `gemgame_sala_resposta`;
DROP TABLE IF EXISTS `gemgame_sala_jogador`;
DROP TABLE IF EXISTS `gemgame_sala`;
DROP TABLE IF EXISTS `gemgame_partida`;
DROP TABLE IF EXISTS `gemgame_resposta`;
DROP TABLE IF EXISTS `gemgame_pergunta`;
DROP TABLE IF EXISTS `gemgame_quiz`;
DROP TABLE IF EXISTS `palavras`;
DROP TABLE IF EXISTS `Frases`;
DROP TABLE IF EXISTS `Conjunto de respostas erradas`;
DROP TABLE IF EXISTS `Pergunta`;
DROP TABLE IF EXISTS `Tema`;
DROP TABLE IF EXISTS `Jogo`;
DROP TABLE IF EXISTS `usuarios`;
DROP TABLE IF EXISTS `usuario`;

-- ==============================================================================
-- SEÇÃO 1: ESTRUTURA DAS TABELAS
-- ==============================================================================

-- ------------------------------------------------------------------------------
-- TABELA 1: usuario (Autenticação, Estatísticas e Pontuação Geral)
-- Permite que alunos tenham o mesmo nome (apenas e-mail é único)
-- ------------------------------------------------------------------------------
CREATE TABLE `usuario` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `nome` VARCHAR(255) NOT NULL,
  `senha` VARCHAR(255) NOT NULL,
  `email` VARCHAR(255) NOT NULL,
  `pontuacao` INT NOT NULL DEFAULT 0,
  `questoesRespondidas` INT NOT NULL DEFAULT 0,
  `acertos` INT NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_usuario_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------------------------
-- TABELA 2: Jogo (Catálogo de Jogos do Sistema)
-- ------------------------------------------------------------------------------
CREATE TABLE `Jogo` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `nome` VARCHAR(255) NOT NULL,
  `descricao` VARCHAR(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------------------------
-- TABELA 3: Tema (Temas Oficiais Compartilhados)
-- ------------------------------------------------------------------------------
CREATE TABLE `Tema` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `id_jogo` INT DEFAULT NULL,
  `nome` VARCHAR(255) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_Tema_Jogo` (`id_jogo`),
  CONSTRAINT `fk_Tema_Jogo` FOREIGN KEY (`id_jogo`) REFERENCES `Jogo` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------------------------
-- TABELA 4: Pergunta (Jogo 1: Complete the Sentence - Gap Fill Quiz)
-- ------------------------------------------------------------------------------
CREATE TABLE `Pergunta` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `primeira parte` VARCHAR(255) DEFAULT NULL,
  `resposta` VARCHAR(100) NOT NULL,
  `segunda parte` VARCHAR(255) DEFAULT NULL,
  `id_tema` INT NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_Pergunta_Tema` (`id_tema`),
  CONSTRAINT `fk_Pergunta_Tema` FOREIGN KEY (`id_tema`) REFERENCES `Tema` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------------------------
-- TABELA 5: Conjunto de respostas erradas (Distratores do Jogo 1)
-- ------------------------------------------------------------------------------
CREATE TABLE `Conjunto de respostas erradas` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `nome do conjunto` VARCHAR(255) NOT NULL,
  `conjunto` JSON NOT NULL,
  `Pergunta_id` INT NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_Conjunto_Pergunta` (`Pergunta_id`),
  CONSTRAINT `fk_Conjunto_Pergunta` FOREIGN KEY (`Pergunta_id`) REFERENCES `Pergunta` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------------------------
-- TABELA 6: Frases (Jogo 2: Estoure os Balões - Sentence Builder)
-- ------------------------------------------------------------------------------
CREATE TABLE `Frases` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `frase` VARCHAR(500) NOT NULL,
  `id_tema` INT NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_Frases_Tema` (`id_tema`),
  CONSTRAINT `fk_Frases_Tema` FOREIGN KEY (`id_tema`) REFERENCES `Tema` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------------------------
-- TABELA 7: palavras (Jogo 3: Montagem de Palavras & Jogo 4: Ouça e Escolha)
-- ------------------------------------------------------------------------------
CREATE TABLE `palavras` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `palavra` VARCHAR(255) NOT NULL,
  `traducao` VARCHAR(255) NOT NULL,
  `tema_id` INT NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_palavras_tema` (`tema_id`),
  CONSTRAINT `fk_palavras_tema` FOREIGN KEY (`tema_id`) REFERENCES `Tema` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------------------------
-- TABELA 8: gemgame_quiz (Módulo GemGame - Quizzes Personalizados)
-- ------------------------------------------------------------------------------
CREATE TABLE `gemgame_quiz` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `titulo` VARCHAR(255) NOT NULL,
  `descricao` VARCHAR(500) DEFAULT NULL,
  `criado_em` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------------------------
-- TABELA 9: gemgame_pergunta (Perguntas de cada Quiz)
-- ------------------------------------------------------------------------------
CREATE TABLE `gemgame_pergunta` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `quiz_id` INT NOT NULL,
  `enunciado` TEXT NOT NULL,
  `imagem` VARCHAR(500) DEFAULT NULL,
  `ordem` INT NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `fk_pergunta_quiz` (`quiz_id`),
  CONSTRAINT `fk_pergunta_quiz` FOREIGN KEY (`quiz_id`) REFERENCES `gemgame_quiz` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------------------------
-- TABELA 10: gemgame_resposta (Alternativas de cada Pergunta)
-- ------------------------------------------------------------------------------
CREATE TABLE `gemgame_resposta` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `pergunta_id` INT NOT NULL,
  `texto` VARCHAR(500) NOT NULL,
  `correta` TINYINT(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `fk_resposta_pergunta` (`pergunta_id`),
  CONSTRAINT `fk_resposta_pergunta` FOREIGN KEY (`pergunta_id`) REFERENCES `gemgame_pergunta` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------------------------
-- TABELA 11: gemgame_partida (Histórico de Partidas Individuais)
-- ------------------------------------------------------------------------------
CREATE TABLE `gemgame_partida` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `quiz_id` INT NOT NULL,
  `jogador_id` INT NOT NULL,
  `acertos` INT NOT NULL DEFAULT 0,
  `total_perguntas` INT NOT NULL DEFAULT 0,
  `pontuacao_partida` INT NOT NULL DEFAULT 0,
  `jogado_em` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_partida_quiz` (`quiz_id`),
  KEY `fk_partida_jogador` (`jogador_id`),
  CONSTRAINT `fk_partida_quiz` FOREIGN KEY (`quiz_id`) REFERENCES `gemgame_quiz` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_partida_jogador` FOREIGN KEY (`jogador_id`) REFERENCES `usuario` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------------------------
-- TABELA 12: gemgame_sala (Salas Multiplayer Síncronas - Modo Kahoot)
-- ------------------------------------------------------------------------------
CREATE TABLE `gemgame_sala` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `pin` VARCHAR(6) NOT NULL,
  `quiz_id` INT NOT NULL,
  `host_id` INT NOT NULL,
  `estado` ENUM('aguardando', 'pergunta', 'resultado_pergunta', 'ranking', 'finalizado') NOT NULL DEFAULT 'aguardando',
  `pergunta_atual_indice` INT NOT NULL DEFAULT 0,
  `pergunta_atual_id` INT DEFAULT NULL,
  `tempo_inicio_pergunta` BIGINT DEFAULT NULL,
  `duracao_pergunta` INT NOT NULL DEFAULT 25,
  `criado_em` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `atualizado_em` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_sala_pin` (`pin`),
  KEY `fk_sala_quiz` (`quiz_id`),
  KEY `fk_sala_host` (`host_id`),
  CONSTRAINT `fk_sala_quiz` FOREIGN KEY (`quiz_id`) REFERENCES `gemgame_quiz` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_sala_host` FOREIGN KEY (`host_id`) REFERENCES `usuario` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------------------------
-- TABELA 13: gemgame_sala_jogador (Alunos conectados na sala do Host)
-- ------------------------------------------------------------------------------
CREATE TABLE `gemgame_sala_jogador` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `sala_id` INT NOT NULL,
  `usuario_id` INT DEFAULT NULL,
  `nickname` VARCHAR(100) NOT NULL,
  `pontuacao` INT NOT NULL DEFAULT 0,
  `acertos` INT NOT NULL DEFAULT 0,
  `ultimo_ping` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `criado_em` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_jogador_sala` (`sala_id`),
  KEY `fk_jogador_usuario` (`usuario_id`),
  CONSTRAINT `fk_jogador_sala` FOREIGN KEY (`sala_id`) REFERENCES `gemgame_sala` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_jogador_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuario` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------------------------
-- TABELA 14: gemgame_sala_resposta (Respostas dos alunos por pergunta da sala)
-- ------------------------------------------------------------------------------
CREATE TABLE `gemgame_sala_resposta` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `sala_id` INT NOT NULL,
  `pergunta_id` INT NOT NULL,
  `jogador_id` INT NOT NULL,
  `resposta_id` INT NOT NULL,
  `correta` TINYINT(1) NOT NULL DEFAULT 0,
  `tempo_resposta_ms` INT NOT NULL DEFAULT 0,
  `pontos_ganhos` INT NOT NULL DEFAULT 0,
  `respondido_em` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_jogador_pergunta` (`sala_id`, `pergunta_id`, `jogador_id`),
  KEY `fk_resp_sala` (`sala_id`),
  KEY `fk_resp_pergunta` (`pergunta_id`),
  KEY `fk_resp_jogador` (`jogador_id`),
  KEY `fk_resp_resposta` (`resposta_id`),
  CONSTRAINT `fk_resp_sala` FOREIGN KEY (`sala_id`) REFERENCES `gemgame_sala` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_resp_pergunta` FOREIGN KEY (`pergunta_id`) REFERENCES `gemgame_pergunta` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_resp_jogador` FOREIGN KEY (`jogador_id`) REFERENCES `gemgame_sala_jogador` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_resp_resposta` FOREIGN KEY (`resposta_id`) REFERENCES `gemgame_resposta` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==============================================================================
-- SEÇÃO 2: CARGA DE DADOS INICIAIS (SEEDING)
-- ==============================================================================

-- ------------------------------------------------------------------------------
-- DADOS: Jogo (Catálogo dos 4 Jogos)
-- ------------------------------------------------------------------------------
INSERT INTO `Jogo` (`id`, `nome`, `descricao`) VALUES
(1, 'complete sentence', 'Complete the Sentence - English for Kids'),
(2, 'ballons', 'Estoure os Balões - Build the Sentence'),
(3, 'word spelling', 'Montagem de Palavras - English for Kids'),
(4, 'listen and choose', 'Ouça e Escolha - English for Kids')
ON DUPLICATE KEY UPDATE `nome` = VALUES(`nome`), `descricao` = VALUES(`descricao`);

-- ------------------------------------------------------------------------------
-- DADOS: Tema (4 Temas Oficiais Unificados: animals, sports, school, family)
-- ------------------------------------------------------------------------------
INSERT INTO `Tema` (`id`, `id_jogo`, `nome`) VALUES
(7, NULL, 'animals'),
(8, NULL, 'sports'),
(9, NULL, 'school'),
(10, NULL, 'family')
ON DUPLICATE KEY UPDATE `nome` = VALUES(`nome`), `id_jogo` = VALUES(`id_jogo`);

-- ------------------------------------------------------------------------------
-- DADOS: Frases (Jogo 2: Estoure os Balões)
-- ------------------------------------------------------------------------------
-- Tema: Animals (ID 7)
INSERT INTO `Frases` (`frase`, `id_tema`) VALUES  
('The tiger is a very strong animal', 7),
('The panda is eating green bamboo', 7),
('The turtle walks very slowly', 7),
('The spider has eight long legs', 7),
('The penguin lives in the cold ice', 7),
('The bear is sleeping in the dark cave', 7),
('The horse is running on the big farm', 7),
('The snake is green and long', 7),
('The dolphin is swimming in the blue ocean', 7),
('The butterfly has beautiful colorful wings', 7),
('The capybara is chilling like a boss', 7),
('The little dog is doing a funny TikTok dance', 7),
('The cat is hiding inside the cardboard box', 7),
('The pink pig loves to play in the mud', 7),
('The zebra has black and white stripes', 7),
('The kangaroo is jumping very high', 7),
('The wise owl is looking at the night sky', 7),
('The little mouse is eating yellow cheese', 7),
('The bee is making sweet honey', 7),
('The shark is the big monster of the sea', 7);

-- Tema: Sports (ID 8)
INSERT INTO `Frases` (`frase`, `id_tema`) VALUES  
('The boy is playing soccer in the green field', 8),
('The tall player is playing basketball like an NBA star', 8),
('The girl is swimming very fast in the blue pool', 8),
('The runner is using cool sneakers to win the race', 8),
('The volleyball match is very famous on Instagram', 8),
('The yellow tennis ball is flying over the big net', 8),
('The cool boy is doing a skateboard trick for TikTok', 8),
('The gamer is playing an e-sports tournament on his computer', 8),
('The champion is happy with the shiny gold medal', 8),
('The surfer is riding a giant wave at the beautiful beach', 8),
('The famous soccer player is scoring a beautiful goal', 8),
('The kids are playing ping pong on the big table', 8),
('The karate master is breaking the wood with a fast kick', 8),
('The strong man is lifting heavy weights in the gym', 8),
('The gymnastics girl is jumping very high in the air', 8),
('The bicycle is super fast in the amazing street race', 8),
('The baseball player is hitting the ball with a big bat', 8),
('The snowboarder is sliding down the cold snow mountain', 8),
('The happy fans are making a viral video in the big stadium', 8),
('The boxing champion is training hard for the great fight', 8);

-- Tema: School (ID 9)
INSERT INTO `Frases` (`frase`, `id_tema`) VALUES  
('The yellow pencil is inside the case', 9),
('The open book is on the student desk', 9),
('The teacher is writing on the blackboard', 9),
('The heavy backpack is full of notebooks', 9),
('The kids are playing soccer during the break', 9),
('The yellow school bus is waiting outside', 9),
('The blue pen is next to the white eraser', 9),
('The students are using the computer in the lab', 9),
('The physical education class is very fun', 9),
('The girl is drawing a beautiful picture in art class', 9),
('The friends are making a TikTok dance at school', 9),
('The math test is easy like a video game', 9),
('The school bell is ringing for the recess', 9),
('The smart boy is doing his English homework', 9),
('The children are eating pizza in the cafeteria', 9),
('The scissors are for cutting the colorful paper', 9),
('The principal is talking in the big microphone', 9),
('The science project is a cool volcano', 9),
('The dictionary is helping me learn new words', 9),
('The music class is my favorite part of the day', 9);

-- Tema: Family (ID 10)
INSERT INTO `Frases` (`frase`, `id_tema`) VALUES  
('The loving mother is cooking a delicious pizza', 10),
('The cool father is playing a racing video game', 10),
('My big brother is watching a funny video on TikTok', 10),
('The little sister is drawing a colorful rainbow', 10),
('The sweet grandmother is making a big chocolate cake', 10),
('The old grandfather is sleeping on the comfortable sofa', 10),
('The happy family is taking a beautiful photo for Instagram', 10),
('My funny uncle is dancing at the birthday party', 10),
('The young aunt is buying a new toy at the mall', 10),
('My baby cousin is playing with the cute dog', 10),
('The father is driving the big blue car', 10),
('The mother is helping me with my English homework', 10),
('My sister is listening to pop music on her phone', 10),
('The brother is playing soccer in the green park', 10),
('The grandfather is telling an amazing old story', 10),
('The grandmother loves her beautiful flower garden', 10),
('My cousin is wearing a cool superhero t-shirt', 10),
('The whole family is watching a fun movie together', 10),
('My parents are buying sweet ice cream for us', 10),
('The twin brothers are doing a crazy viral dance', 10);

-- ------------------------------------------------------------------------------
-- DADOS: Pergunta (Jogo 1: Complete the Sentence)
-- ------------------------------------------------------------------------------
INSERT INTO `Pergunta` (`id`, `primeira parte`, `resposta`, `segunda parte`, `id_tema`) VALUES
-- Tema: Animals (id_tema: 7)
(1, 'The friendly pet ', 'dog', ' barks at the mailman.', 7),
(2, 'The small ', 'cat', ' likes to drink milk and purr.', 7),
(3, 'The large gray ', 'elephant', ' uses its trunk to drink water.', 7),
(4, 'The little golden ', 'fish', ' swims in the bowl.', 7),
(5, 'The colorful ', 'bird', ' flies high in the blue sky.', 7),
(6, 'The tall ', 'giraffe', ' eats leaves from the high trees.', 7),
(7, 'The king of the jungle is the fierce ', 'lion', '.', 7),
(8, 'The funny ', 'monkey', ' loves to eat yellow bananas.', 7),
(9, 'The farm ', 'cow', ' gives us white milk to drink.', 7),
(10, 'You can ride a fast ', 'horse', ' on the farm.', 7),
(11, 'The pink ', 'pig', ' likes to play in the mud.', 7),
(12, 'The fluffy white ', 'rabbit', ' eats orange carrots.', 7),
(13, 'The tiny squeaking ', 'mouse', ' loves to eat cheese.', 7),
(14, 'The long green ', 'snake', ' slithers on the ground.', 7),
(15, 'The slow moving ', 'turtle', ' carries its house on its back.', 7),
(16, 'The big brown ', 'bear', ' sleeps all winter in a cave.', 7),
(17, 'The beautiful ', 'tiger', ' has black and orange stripes.', 7),
(18, 'The black and white ', 'zebra', ' looks like a horse.', 7),
(19, 'The yellow ', 'duck', ' swims in the lake and says quack.', 7),
(20, 'The green ', 'frog', ' jumps very high near the pond.', 7),

-- Tema: Sports (id_tema: 8)
(21, 'In soccer, you kick the ', 'ball', ' to score a goal.', 8),
(22, 'To go swimming, you need a ', 'pool', ' full of water.', 8),
(23, 'In tennis, we use a ', 'racket', ' to hit the ball.', 8),
(24, 'You wear a helmet to ride your ', 'bike', ' safely.', 8),
(25, 'In basketball, you throw the ball into the ', 'hoop', '.', 8),
(26, 'In volleyball, you use your ', 'hands', ' to hit the ball over the net.', 8),
(27, 'When playing baseball, you use a ', 'bat', ' to hit the small ball.', 8),
(28, 'People ', 'run', ' very fast in a marathon race.', 8),
(29, 'You wear big red gloves in a ', 'boxing', ' match.', 8),
(30, 'In golf, you try to put the ball in a small ', 'hole', '.', 8),
(31, 'In table tennis, the ball bounces on a green ', 'table', '.', 8),
(32, 'You use a board to surf on the big ocean ', 'waves', '.', 8),
(33, 'A soccer team has eleven ', 'players', ' on the green field.', 8),
(34, 'Gymnasts love to run, stretch, and ', 'jump', ' very high.', 8),
(35, 'You need a lot of cold snow to go ', 'skiing', '.', 8),
(36, 'The person who catches the soccer ball is the ', 'goalkeeper', '.', 8),
(37, 'In judo, you tie a colored ', 'belt', ' around your waist.', 8),
(38, 'A large grassy area to play sports is called a ', 'field', '.', 8),
(39, 'To play ice hockey, you need to slide on the ', 'ice', '.', 8),
(40, 'When you win first place, you get a gold ', 'medal', '!', 8),

-- Tema: School (id_tema: 9)
(41, 'We read stories from a big ', 'book', ' during reading time.', 9),
(42, 'The ', 'teacher', ' writes lessons on the whiteboard.', 9),
(43, 'I use a yellow ', 'pencil', ' to write in my notebook.', 9),
(44, 'We sit on a wooden ', 'chair', ' at our desk.', 9),
(45, 'I carry all my school supplies in a ', 'backpack', '.', 9),
(46, 'You use an ', 'eraser', ' to clean your mistakes.', 9),
(47, 'I need a blue ', 'pen', ' to write my name in ink.', 9),
(48, 'We put our books and papers on top of the ', 'desk', '.', 9),
(49, 'To draw straight lines, I use a plastic ', 'ruler', '.', 9),
(50, 'I write all my homework inside my spiral ', 'notebook', '.', 9),
(51, 'We cut colored paper with a pair of ', 'scissors', '.', 9),
(52, 'The teacher uses white ', 'chalk', ' on the old blackboard.', 9),
(53, 'We look at the smart ', 'board', ' to see the pictures.', 9),
(54, 'Our desks and chairs are inside the ', 'classroom', '.', 9),
(55, 'A child who goes to school to learn is a ', 'student', '.', 9),
(56, 'After school, we have to do our ', 'homework', ' at home.', 9),
(57, 'We learn how to count numbers in ', 'math', ' class.', 9),
(58, 'We paint colorful pictures in ', 'art', ' class.', 9),
(59, 'We sing happy songs in ', 'music', ' class.', 9),
(60, 'We learn about plants and animals in ', 'science', ' class.', 9),

-- Tema: Family (id_tema: 10)
(61, 'My mother and father are my loving ', 'parents', '.', 10),
(62, 'The father of my dad is my ', 'grandfather', '.', 10),
(63, 'The sister of my mom is my ', 'aunt', '.', 10),
(64, 'My parents'' son is my ', 'brother', '.', 10),
(65, 'The baby girl of my parents is my ', 'sister', '.', 10),
(66, 'The woman who gave birth to me is my ', 'mother', '.', 10),
(67, 'The man who is married to my mom is my ', 'father', '.', 10),
(68, 'The brother of my dad is my ', 'uncle', '.', 10),
(69, 'The mother of my dad is my ', 'grandmother', '.', 10),
(70, 'The child of my aunt is my ', 'cousin', '.', 10),
(71, 'A very young crying child is a ', 'baby', '.', 10),
(72, 'My parents, siblings, and I make a happy ', 'family', '.', 10),
(73, 'A boy is the ', 'son', ' of his mom and dad.', 10),
(74, 'A girl is the ', 'daughter', ' of her mom and dad.', 10),
(75, 'My brother and I are the ', 'children', ' of our parents.', 10),
(76, 'My grandma and grandpa together are my ', 'grandparents', '.', 10),
(77, 'Two brothers born on the exact same day are ', 'twins', '.', 10),
(78, 'The woman married to my uncle is his ', 'wife', '.', 10),
(79, 'The man married to my aunt is her ', 'husband', '.', 10),
(80, 'My dad''s mom and dad are my ', 'grandparents', '.', 10);

-- ------------------------------------------------------------------------------
-- DADOS: Conjunto de respostas erradas (Distratores do Jogo 1)
-- ------------------------------------------------------------------------------
INSERT INTO `Conjunto de respostas erradas` (`id`, `nome do conjunto`, `conjunto`, `Pergunta_id`) VALUES
-- Tema: Animals
(1, 'animals', '["cat","horse","cow","pig"]', 1),
(2, 'animals', '["dog","bird","horse","cow"]', 2),
(3, 'animals', '["mouse","cat","dog","rabbit"]', 3),
(4, 'animals', '["bird","cat","dog","horse"]', 4),
(5, 'animals', '["dog","cat","fish","cow"]', 5),
(6, 'animals', '["dog","cat","pig","mouse"]', 6),
(7, 'animals', '["rabbit","turtle","fish","bird"]', 7),
(8, 'animals', '["cat","dog","horse","cow"]', 8),
(9, 'animals', '["lion","tiger","monkey","bird"]', 9),
(10, 'animals', '["fish","turtle","snake","frog"]', 10),
(11, 'animals', '["lion","tiger","bear","wolf"]', 11),
(12, 'animals', '["lion","tiger","bear","wolf"]', 12),
(13, 'animals', '["horse","cow","pig","sheep"]', 13),
(14, 'animals', '["dog","cat","bird","fish"]', 14),
(15, 'animals', '["lion","tiger","horse","cow"]', 15),
(16, 'animals', '["rabbit","mouse","frog","duck"]', 16),
(17, 'animals', '["dog","cat","pig","cow"]', 17),
(18, 'animals', '["lion","tiger","monkey","bear"]', 18),
(19, 'animals', '["lion","tiger","horse","cow"]', 19),
(20, 'animals', '["lion","tiger","bear","wolf"]', 20),

-- Tema: Sports
(21, 'sports', '["shoe","car","book","apple"]', 21),
(22, 'sports', '["bed","car","tree","house"]', 22),
(23, 'sports', '["shoe","book","pencil","hat"]', 23),
(24, 'sports', '["car","boat","train","airplane"]', 24),
(25, 'sports', '["tree","window","car","door"]', 25),
(26, 'sports', '["feet","knees","eyes","teeth"]', 26),
(27, 'sports', '["book","shoe","racket","pillow"]', 27),
(28, 'sports', '["sleep","fly","sing","eat"]', 28),
(29, 'sports', '["swimming","sleeping","reading","cooking"]', 29),
(30, 'sports', '["tree","window","car","cloud"]', 30),
(31, 'sports', '["bed","chair","wall","door"]', 31),
(32, 'sports', '["street","house","desert","forest"]', 32),
(33, 'sports', '["dogs","cars","trees","apples"]', 33),
(34, 'sports', '["sleep","cry","cook","read"]', 34),
(35, 'sports', '["swimming","surfing","sleeping","shopping"]', 35),
(36, 'sports', '["teacher","doctor","singer","driver"]', 36),
(37, 'sports', '["hat","shoe","sock","glove"]', 37),
(38, 'sports', '["kitchen","bathroom","bedroom","store"]', 38),
(39, 'sports', '["fire","sand","grass","mud"]', 39),
(40, 'sports', '["apple","chair","sock","potato"]', 40),

-- Tema: School
(41, 'school', '["shoe","apple","car","dog"]', 41),
(42, 'school', '["doctor","farmer","singer","pilot"]', 42),
(43, 'school', '["shoe","tree","apple","plate"]', 43),
(44, 'school', '["cloud","flower","water","bird"]', 44),
(45, 'school', '["refrigerator","window","television","bed"]', 45),
(46, 'school', '["spoon","fork","apple","cat"]', 46),
(47, 'school', '["car","dog","tree","apple"]', 47),
(48, 'school', '["cloud","flower","water","bird"]', 48),
(49, 'school', '["banana","fish","hat","sock"]', 49),
(50, 'school', '["shoe","apple","car","dog"]', 50),
(51, 'school', '["cats","shoes","apples","books"]', 51),
(52, 'school', '["milk","water","juice","bread"]', 52),
(53, 'school', '["shoe","apple","car","dog"]', 53),
(54, 'school', '["bathroom","moon","hospital","supermarket"]', 54),
(55, 'school', '["doctor","farmer","singer","pilot"]', 55),
(56, 'school', '["sleeping","running","eating","swimming"]', 56),
(57, 'school', '["sleeping","running","eating","swimming"]', 57),
(58, 'school', '["gym","lunch","sleeping","running"]', 58),
(59, 'school', '["math","science","eating","sleeping"]', 59),
(60, 'school', '["food","animal","toy","color"]', 60),

-- Tema: Family
(61, 'family', '["dogs","cats","birds","fishes"]', 61),
(62, 'family', '["hat","shoe","spoon","plate"]', 62),
(63, 'family', '["horse","door","window","table"]', 63),
(64, 'family', '["chair","fish","tree","cloud"]', 64),
(65, 'family', '["car","boat","flower","moon"]', 65),
(66, 'family', '["apple","banana","orange","grape"]', 66),
(67, 'family', '["car","dog","book","shoe"]', 67),
(68, 'family', '["cat","bird","apple","pencil"]', 68),
(69, 'family', '["dog","cat","house","car"]', 69),
(70, 'family', '["book","pen","desk","ruler"]', 70),
(71, 'family', '["car","house","tree","star"]', 71),
(72, 'family', '["book","shoe","hat","sock"]', 72),
(73, 'family', '["cat","dog","bird","fish"]', 73),
(74, 'family', '["car","bus","train","plane"]', 74),
(75, 'family', '["apples","chairs","shoes","cars"]', 75),
(76, 'family', '["cats","dogs","cars","trees"]', 76),
(77, 'family', '["apples","chairs","shoes","cars"]', 77),
(78, 'family', '["cat","dog","bird","fish"]', 78),
(79, 'family', '["apple","banana","car","dog"]', 79),
(80, 'family', '["cats","dogs","cars","trees"]', 80);

-- ------------------------------------------------------------------------------
-- DADOS: palavras (Jogo 3: Montagem de Palavras & Jogo 4: Ouça e Escolha)
-- ------------------------------------------------------------------------------
INSERT INTO `palavras` (`palavra`, `tema_id`, `traducao`) VALUES
-- Tema 7: Animais
('dog', 7, 'cachorro'),
('cat', 7, 'gato'),
('bird', 7, 'pássaro'),
('fish', 7, 'peixe'),
('lion', 7, 'leão'),
('tiger', 7, 'tigre'),
('elephant', 7, 'elefante'),
('monkey', 7, 'macaco'),
('bear', 7, 'urso'),
('horse', 7, 'cavalo'),
('pig', 7, 'porco'),
('cow', 7, 'vaca'),
('sheep', 7, 'ovelha'),
('chicken', 7, 'galinha'),
('duck', 7, 'pato'),
('rabbit', 7, 'coelho'),
('turtle', 7, 'tartaruga'),
('snake', 7, 'cobra'),
('frog', 7, 'sapo'),
('spider', 7, 'aranha'),
('butterfly', 7, 'borboleta'),
('bee', 7, 'abelha'),
('ant', 7, 'formiga'),
('dolphin', 7, 'golfinho'),
('whale', 7, 'baleia'),
('shark', 7, 'tubarão'),
('octopus', 7, 'polvo'),
('penguin', 7, 'pinguim'),
('kangaroo', 7, 'canguru'),
('zebra', 7, 'zebra'),
('giraffe', 7, 'girafa'),
('rhino', 7, 'rinoceronte'),
('hippo', 7, 'hipopótamo'),
('crocodile', 7, 'crocodilo'),
('owl', 7, 'coruja'),

-- Tema 8: Esportes
('soccer', 8, 'futebol'),
('basketball', 8, 'basquete'),
('volleyball', 8, 'vôlei'),
('tennis', 8, 'tênis'),
('swimming', 8, 'natação'),
('baseball', 8, 'beisebol'),
('running', 8, 'corrida'),
('cycling', 8, 'ciclismo'),
('boxing', 8, 'boxe'),
('gymnastics', 8, 'ginástica'),
('surfing', 8, 'surfe'),
('rugby', 8, 'rugby'),
('golf', 8, 'golfe'),
('skiing', 8, 'esqui'),
('snowboarding', 8, 'snowboard'),
('hockey', 8, 'hóquei'),
('judo', 8, 'judô'),
('karate', 8, 'karatê'),
('athletics', 8, 'atletismo'),
('wrestling', 8, 'luta livre'),

-- Tema 9: Escola
('pen', 9, 'caneta'),
('pencil', 9, 'lápis'),
('eraser', 9, 'borracha'),
('notebook', 9, 'caderno'),
('book', 9, 'livro'),
('ruler', 9, 'régua'),
('backpack', 9, 'mochila'),
('teacher', 9, 'professor(a)'),
('student', 9, 'aluno(a)'),
('classroom', 9, 'sala de aula'),
('desk', 9, 'carteira'),
('chair', 9, 'cadeira'),
('board', 9, 'lousa'),
('chalk', 9, 'giz'),
('scissors', 9, 'tesoura'),
('glue', 9, 'cola'),
('sharpener', 9, 'apontador'),
('homework', 9, 'lição de casa'),
('library', 9, 'biblioteca'),
('exam', 9, 'prova'),

-- Tema 10: Família
('family', 10, 'família'),
('mother', 10, 'mãe'),
('father', 10, 'pai'),
('son', 10, 'filho'),
('daughter', 10, 'filha'),
('brother', 10, 'irmão'),
('sister', 10, 'irmã'),
('grandfather', 10, 'avô'),
('grandmother', 10, 'avó'),
('uncle', 10, 'tio'),
('aunt', 10, 'tia'),
('cousin', 10, 'primo(a)'),
('nephew', 10, 'sobrinho'),
('niece', 10, 'sobrinha'),
('parents', 10, 'pais'),
('grandparents', 10, 'avós'),
('husband', 10, 'marido'),
('wife', 10, 'esposa'),
('grandson', 10, 'neto'),
('granddaughter', 10, 'neta');

-- ------------------------------------------------------------------------------
-- DADOS: GemGame Starter Quiz (Quiz Padrão Inicial com Perguntas e Respostas)
-- ------------------------------------------------------------------------------
INSERT INTO `gemgame_quiz` (`id`, `titulo`, `descricao`) VALUES
(1, 'English Fun Challenge', 'Quiz inicial interativo para testar seus conhecimentos em inglês!')
ON DUPLICATE KEY UPDATE `titulo` = VALUES(`titulo`), `descricao` = VALUES(`descricao`);

INSERT INTO `gemgame_pergunta` (`id`, `quiz_id`, `enunciado`, `imagem`, `ordem`) VALUES
(1, 1, 'Como se diz "Cachorro" em inglês?', NULL, 1),
(2, 1, 'Qual a cor do "Sun" (Sol)?', NULL, 2),
(3, 1, 'Qual desses é um material escolar?', NULL, 3),
(4, 1, 'Quem é o "Father" na família?', NULL, 4)
ON DUPLICATE KEY UPDATE `enunciado` = VALUES(`enunciado`);

INSERT INTO `gemgame_resposta` (`id`, `pergunta_id`, `texto`, `correta`) VALUES
-- Pergunta 1: Dog
(1, 1, 'Dog', 1),
(2, 1, 'Cat', 0),
(3, 1, 'Fish', 0),
(4, 1, 'Bird', 0),
-- Pergunta 2: Yellow
(5, 2, 'Yellow', 1),
(6, 2, 'Blue', 0),
(7, 2, 'Red', 0),
(8, 2, 'Green', 0),
-- Pergunta 3: Pencil
(9, 3, 'Pencil', 1),
(10, 3, 'Apple', 0),
(11, 3, 'Car', 0),
(12, 3, 'Table', 0),
-- Pergunta 4: Pai
(13, 4, 'Pai', 1),
(14, 4, 'Mãe', 0),
(15, 4, 'Irmão', 0),
(16, 4, 'Tio', 0)
ON DUPLICATE KEY UPDATE `texto` = VALUES(`texto`), `correta` = VALUES(`correta`);

-- ------------------------------------------------------------------------------
-- FINALIZAÇÃO E REATIVAÇÃO DE CHECAGEM DE CHAVES ESTRANGEIRAS
-- ------------------------------------------------------------------------------
SET FOREIGN_KEY_CHECKS = 1;

-- ==============================================================================
-- FIM DO SCRIPT ÚNICO
-- ==============================================================================
