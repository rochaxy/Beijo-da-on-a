-- Importação inicial: não execute sobre uma instalação existente sem backup.
CREATE DATABASE IF NOT EXISTS beijo_da_onca_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE beijo_da_onca_db;
SET NAMES utf8mb4;
CREATE TABLE usuarios (
 id INT AUTO_INCREMENT PRIMARY KEY,
 nome VARCHAR(100) NOT NULL,
 email VARCHAR(254) NOT NULL UNIQUE,
 senha VARCHAR(255) NOT NULL,
 perfil ENUM('cliente','admin') NOT NULL DEFAULT 'cliente',
 criado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;
CREATE TABLE categorias (
 id INT AUTO_INCREMENT PRIMARY KEY,
 nome VARCHAR(100) NOT NULL UNIQUE
) ENGINE=InnoDB;
CREATE TABLE produtos (
 id INT AUTO_INCREMENT PRIMARY KEY,
 categoria_id INT NOT NULL,
 nome VARCHAR(150) NOT NULL,
 resumo VARCHAR(255) NOT NULL,
 descricao TEXT NOT NULL,
 preco DECIMAL(10,2) NOT NULL,
 estoque INT NOT NULL DEFAULT 0,
 imagem VARCHAR(500) NOT NULL DEFAULT 'assets/garrafa.svg',
 ativo TINYINT NOT NULL DEFAULT 1,
 CONSTRAINT fk_produto_categoria FOREIGN KEY (categoria_id) REFERENCES categorias(id),
 CONSTRAINT ck_produto_preco CHECK (preco > 0),
 CONSTRAINT ck_produto_estoque CHECK (estoque >= 0),
 CONSTRAINT ck_produto_ativo CHECK (ativo IN (0,1)),
 INDEX idx_produto_categoria (categoria_id),
 INDEX idx_produto_status_nome (ativo,nome)
) ENGINE=InnoDB;
CREATE TABLE pedidos (
 id INT AUTO_INCREMENT PRIMARY KEY,
 usuario_id INT NOT NULL,
 data TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
 metodo_pagamento ENUM('PIX','Boleto','Cartão de Crédito') NOT NULL,
 total DECIMAL(10,2) NOT NULL,
 status ENUM('confirmado_simulado') NOT NULL DEFAULT 'confirmado_simulado',
 checkout_token CHAR(64) NOT NULL UNIQUE,
 CONSTRAINT fk_pedido_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id),
 CONSTRAINT ck_pedido_total CHECK (total >= 0),
 INDEX idx_pedido_cliente_data (usuario_id,data),
 INDEX idx_pedido_data (data)
) ENGINE=InnoDB;
CREATE TABLE itens_pedido (
 id INT AUTO_INCREMENT PRIMARY KEY,
 pedido_id INT NOT NULL,
 produto_id INT NOT NULL,
 -- Snapshot preserva o histórico mesmo quando o produto é renomeado.
 nome_produto VARCHAR(150) NOT NULL,
 quantidade INT NOT NULL,
 preco_unitario DECIMAL(10,2) NOT NULL,
 CONSTRAINT fk_item_pedido FOREIGN KEY (pedido_id) REFERENCES pedidos(id),
 CONSTRAINT fk_item_produto FOREIGN KEY (produto_id) REFERENCES produtos(id),
 CONSTRAINT ck_item_quantidade CHECK (quantidade > 0),
 CONSTRAINT ck_item_preco CHECK (preco_unitario > 0),
 UNIQUE KEY uq_item_pedido_produto (pedido_id,produto_id),
 INDEX idx_item_produto (produto_id)
) ENGINE=InnoDB;
INSERT INTO usuarios (nome,email,senha,perfil) VALUES
('Administrador','admin@beijodaonca.com','$2y$12$UxLG/Dg.sHsoxmfN6RuLz.PsZdINg.6tG/sb63F/lHUzQbNwH4UBW','admin'),
('Cliente de Teste','cliente@example.com','$2y$12$XPKlFhMEv69RN9K1fRB0q.BysSvVVBBnIs/8Xka6U2NFuK35Yno0K','cliente');
INSERT INTO categorias (nome) VALUES
('Cachaças Envelhecidas'),('Cachaças Clássicas / Prata'),('Licores & Especiarias'),('Kits & Presentes');
-- Linha demonstrativa inspirada no nicho da marca; preços e descrições fictícios.
INSERT INTO produtos (categoria_id,nome,resumo,descricao,preco,estoque) VALUES
(1,'Reserva Carvalho 700 ml','Notas de baunilha, madeira e final persistente.','Cachaça artesanal de perfil amadeirado. O carvalho inspira aromas de baunilha e especiarias, corpo macio e final prolongado. Sugestão de degustação: pequenas doses, em temperatura ambiente. Conteúdo: 700 ml. Catálogo demonstrativo.',89.90,18),
(1,'Seleção Amburana 700 ml','Aromas adocicados e delicado toque de especiarias.','Perfil sensorial inspirado na amburana brasileira: notas aromáticas doces e especiarias suaves, com textura aveludada. Sugestão: apreciar lentamente. Conteúdo: 700 ml. Produto demonstrativo.',79.90,12),
(1,'Blend de Madeiras 700 ml','Equilíbrio entre intensidade e suavidade.','Uma proposta de blend com notas de madeira, especiarias e final equilibrado. Ideal para conhecer diferentes expressões da cachaça artesanal. Conteúdo: 700 ml. Produto demonstrativo.',109.90,6),
(2,'Prata da Casa 700 ml','Frescor da cana e versatilidade para coquetéis.','Cachaça de aparência cristalina, com notas frescas de cana e perfil direto. Uma proposta para caipirinhas e coquetéis brasileiros. Conteúdo: 700 ml. Produto demonstrativo.',49.90,30),
(2,'Prata Origem 1 L','Expressão clássica da tradição brasileira.','Perfil leve e fresco, pensado para encontros e preparo de coquetéis. Notas vegetais e de cana, com final limpo. Conteúdo: 1 litro. Produto demonstrativo.',64.90,0),
(3,'Licor de Jabuticaba 500 ml','Fruta brasileira com dulçor equilibrado.','Licor de perfil frutado, inspirado na jabuticaba, com aroma intenso e textura envolvente. Sugestão: servir gelado em pequenas doses ou em coquetéis. Conteúdo: 500 ml. Produto demonstrativo.',59.90,15),
(3,'Licor de Mel e Especiarias 500 ml','Aconchego aromático e final delicado.','Proposta de licor com notas de mel e especiarias, aroma acolhedor e dulçor persistente. Sugestão: servir gelado. Conteúdo: 500 ml. Produto demonstrativo.',62.90,9),
(4,'Kit Presente Raízes','Uma seleção para brindar e presentear.','Kit demonstrativo com uma cachaça artesanal de 700 ml e dois copos de degustação em embalagem para presente. A proposta reúne identidade brasileira e apresentação rústica. Imagem ilustrativa.',149.90,5);
