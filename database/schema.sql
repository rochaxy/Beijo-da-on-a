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
('Cachaças de Madeira'),('Cachaças Clássicas'),('Kits & Presentes'),('Acessórios');
-- Preços das capturas fornecidas. Estoques de teste para a avaliação do CRUD e do checkout.
INSERT INTO produtos (categoria_id,nome,resumo,descricao,preco,estoque,imagem) VALUES
(3,'Kit Carvalho e Amburana','Garrafa de 750 ml com taça ISO e caixa.','Acompanha uma garrafa Carvalho & Amburana de 750 ml, uma taça ISO personalizada e uma caixa para presente.',269.00,5,'assets/catalogo/kit-carvalho.webp'),
(1,'Carvalho & Amburana','Cachaça de alambique, 750 ml.','A cachaça Carvalho & Amburana tem perfil intenso. A garrafa de 750 ml traz graduação alcoólica de 40% vol. no rótulo.',249.00,12,'assets/catalogo/carvalho-amburana.png'),
(3,'Kit Clássica','Garrafa de 750 ml com taça ISO e caixa.','O kit reúne a garrafa Beijo da Onça Clássica de 750 ml, uma taça ISO personalizada e a caixa para presente.',169.00,6,'assets/catalogo/kit-classica.webp'),
(2,'Beijo da Onça Clássica','Cachaça leve, sem filtro, 750 ml.','A marca descreve a Clássica como direta, leve e sem filtro. A garrafa de 750 ml traz graduação alcoólica de 38% vol. no rótulo.',149.00,30,'assets/catalogo/classica.png'),
(4,'Tábua','Tábua da linha Premium Beijo da Onça.','Tábua de madeira da linha Premium com a marca Beijo da Onça gravada na superfície.',119.00,0,'assets/catalogo/tabua.png'),
(4,'Taça','Taça personalizada Beijo da Onça.','Taça transparente com haste e o logotipo da marca em dourado.',59.90,15,'assets/catalogo/taca.png'),
(4,'Copo duplo shot','Copo alto da linha Beijo da Onça.','Copo de vidro transparente, de formato alongado, com o símbolo da onça em verde e amarelo.',24.90,9,'assets/catalogo/copo-duplo-shot.png'),
(4,'Copo de shot','Copo de dose da linha Beijo da Onça.','Copo baixo de vidro transparente, com base espessa e símbolo da onça em amarelo.',14.90,18,'assets/catalogo/copo-de-shot.png');
