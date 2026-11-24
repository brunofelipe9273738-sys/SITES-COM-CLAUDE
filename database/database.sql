-- Joalheria: schema MySQL 8 / MariaDB 10.4+ (XAMPP)
CREATE DATABASE IF NOT EXISTS joalheria CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE joalheria;

-- ============ USUÁRIOS E PERMISSÕES ============
CREATE TABLE usuarios (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nome VARCHAR(120) NOT NULL,
  email VARCHAR(160) NOT NULL UNIQUE,
  senha_hash VARCHAR(255) NOT NULL,            -- password_hash() (bcrypt/argon2)
  cpf VARCHAR(14) NULL,
  telefone VARCHAR(20) NULL,
  papel ENUM('cliente','atendente','admin') NOT NULL DEFAULT 'cliente',
  foto VARCHAR(255) NULL,
  ativo TINYINT(1) NOT NULL DEFAULT 1,
  aceitou_lgpd_em DATETIME NULL,               -- consentimento LGPD
  criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE recuperacao_senha (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  usuario_id INT UNSIGNED NOT NULL,
  token_hash CHAR(64) NOT NULL,                -- guarda só o hash do token
  expira_em DATETIME NOT NULL,
  usado TINYINT(1) DEFAULT 0,
  FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE enderecos (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  usuario_id INT UNSIGNED NOT NULL,
  cep CHAR(8) NOT NULL,
  logradouro VARCHAR(160) NOT NULL,
  numero VARCHAR(20) NOT NULL,
  complemento VARCHAR(80) NULL,
  bairro VARCHAR(80) NOT NULL,
  cidade VARCHAR(80) NOT NULL,
  uf CHAR(2) NOT NULL,
  principal TINYINT(1) DEFAULT 0,
  FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============ CATÁLOGO ============
CREATE TABLE categorias (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nome VARCHAR(80) NOT NULL,
  slug VARCHAR(80) NOT NULL UNIQUE,
  ativa TINYINT(1) DEFAULT 1
) ENGINE=InnoDB;

CREATE TABLE colecoes (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nome VARCHAR(100) NOT NULL,
  slug VARCHAR(100) NOT NULL UNIQUE,
  imagem VARCHAR(255) NULL
) ENGINE=InnoDB;

CREATE TABLE produtos (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  categoria_id INT UNSIGNED NOT NULL,
  colecao_id INT UNSIGNED NULL,
  nome VARCHAR(160) NOT NULL,
  slug VARCHAR(180) NOT NULL UNIQUE,
  descricao TEXT,
  material VARCHAR(120),
  peso_gramas DECIMAL(8,2),
  publico ENUM('feminino','masculino','unissex') DEFAULT 'unissex',
  preco DECIMAL(10,2) NOT NULL,
  preco_promocional DECIMAL(10,2) NULL,
  parcelas_max TINYINT DEFAULT 10,
  destaque TINYINT(1) DEFAULT 0,
  lancamento TINYINT(1) DEFAULT 0,
  ativo TINYINT(1) DEFAULT 1,
  criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (categoria_id) REFERENCES categorias(id),
  FOREIGN KEY (colecao_id) REFERENCES colecoes(id) ON DELETE SET NULL,
  INDEX (categoria_id), FULLTEXT (nome, descricao)
) ENGINE=InnoDB;

CREATE TABLE produto_imagens (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  produto_id INT UNSIGNED NOT NULL,
  caminho VARCHAR(255) NOT NULL,
  ordem TINYINT DEFAULT 0,
  FOREIGN KEY (produto_id) REFERENCES produtos(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Variações (tamanho do aro, comprimento etc.) com estoque próprio
CREATE TABLE produto_variacoes (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  produto_id INT UNSIGNED NOT NULL,
  rotulo VARCHAR(60) NOT NULL,                 -- ex.: "Aro 16", "45 cm"
  sku VARCHAR(60) UNIQUE,
  estoque INT NOT NULL DEFAULT 0,
  estoque_minimo INT NOT NULL DEFAULT 2,
  FOREIGN KEY (produto_id) REFERENCES produtos(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE favoritos (
  usuario_id INT UNSIGNED NOT NULL,
  produto_id INT UNSIGNED NOT NULL,
  PRIMARY KEY (usuario_id, produto_id),
  FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
  FOREIGN KEY (produto_id) REFERENCES produtos(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE avaliacoes (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  produto_id INT UNSIGNED NOT NULL,
  usuario_id INT UNSIGNED NOT NULL,
  nota TINYINT NOT NULL CHECK (nota BETWEEN 1 AND 5),
  comentario TEXT,
  aprovada TINYINT(1) DEFAULT 0,               -- moderação pelo admin
  criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (produto_id) REFERENCES produtos(id) ON DELETE CASCADE,
  FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============ CARRINHO, CUPONS, PEDIDOS ============
CREATE TABLE carrinho_itens (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  usuario_id INT UNSIGNED NULL,
  sessao_id VARCHAR(64) NULL,                  -- carrinho de visitante
  variacao_id INT UNSIGNED NOT NULL,
  quantidade INT NOT NULL DEFAULT 1,
  FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
  FOREIGN KEY (variacao_id) REFERENCES produto_variacoes(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE cupons (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  codigo VARCHAR(40) NOT NULL UNIQUE,
  tipo ENUM('percentual','valor') NOT NULL,
  valor DECIMAL(10,2) NOT NULL,
  pedido_minimo DECIMAL(10,2) DEFAULT 0,
  usos_max INT NULL,
  usos INT DEFAULT 0,
  validade DATE NULL,
  ativo TINYINT(1) DEFAULT 1
) ENGINE=InnoDB;

CREATE TABLE pedidos (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  usuario_id INT UNSIGNED NOT NULL,
  endereco_json JSON NOT NULL,                 -- cópia do endereço no momento da compra
  cupom_id INT UNSIGNED NULL,
  subtotal DECIMAL(10,2) NOT NULL,
  desconto DECIMAL(10,2) DEFAULT 0,
  frete DECIMAL(10,2) DEFAULT 0,
  total DECIMAL(10,2) NOT NULL,
  metodo_envio VARCHAR(40),
  prazo_dias INT,
  codigo_rastreio VARCHAR(40) NULL,
  status ENUM('aguardando_pagamento','pago','em_separacao','enviado','entregue','cancelado') DEFAULT 'aguardando_pagamento',
  criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (usuario_id) REFERENCES usuarios(id),
  FOREIGN KEY (cupom_id) REFERENCES cupons(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE pedido_itens (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  pedido_id INT UNSIGNED NOT NULL,
  variacao_id INT UNSIGNED NOT NULL,
  nome_produto VARCHAR(160) NOT NULL,          -- snapshot
  rotulo_variacao VARCHAR(60),
  preco_unitario DECIMAL(10,2) NOT NULL,
  quantidade INT NOT NULL,
  FOREIGN KEY (pedido_id) REFERENCES pedidos(id) ON DELETE CASCADE,
  FOREIGN KEY (variacao_id) REFERENCES produto_variacoes(id)
) ENGINE=InnoDB;

CREATE TABLE pedido_historico (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  pedido_id INT UNSIGNED NOT NULL,
  status VARCHAR(40) NOT NULL,
  observacao VARCHAR(255),
  criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (pedido_id) REFERENCES pedidos(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Pagamento: NUNCA guardar número de cartão nem CVV. Só referência do gateway.
CREATE TABLE pagamentos (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  pedido_id INT UNSIGNED NOT NULL,
  gateway VARCHAR(30) NOT NULL DEFAULT 'mercadopago',
  gateway_ref VARCHAR(80) NULL,                -- id da transação no gateway
  metodo ENUM('pix','cartao','boleto') NOT NULL,
  parcelas TINYINT DEFAULT 1,
  status ENUM('pendente','aprovado','recusado','estornado') DEFAULT 'pendente',
  valor DECIMAL(10,2) NOT NULL,
  criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (pedido_id) REFERENCES pedidos(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============ ATENDIMENTO AO VIVO ============
CREATE TABLE atendentes (
  usuario_id INT UNSIGNED PRIMARY KEY,
  nome_exibicao VARCHAR(80) NOT NULL,
  online TINYINT(1) DEFAULT 0,                 -- atualizado pelo servidor WebSocket
  ultimo_ping DATETIME NULL,                   -- heartbeat: sem ping = offline
  FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE atendimento_horarios (
  dia_semana TINYINT PRIMARY KEY,              -- 0=dom ... 6=sáb
  abre TIME NULL, fecha TIME NULL, ativo TINYINT(1) DEFAULT 1
) ENGINE=InnoDB;

CREATE TABLE conversas (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  cliente_id INT UNSIGNED NOT NULL,
  atendente_id INT UNSIGNED NULL,
  status ENUM('aguardando','em_atendimento','encerrada') DEFAULT 'aguardando',
  nota TINYINT NULL,
  comentario_avaliacao VARCHAR(255) NULL,
  iniciada_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  encerrada_em DATETIME NULL,
  FOREIGN KEY (cliente_id) REFERENCES usuarios(id),
  FOREIGN KEY (atendente_id) REFERENCES usuarios(id)
) ENGINE=InnoDB;

CREATE TABLE mensagens (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  conversa_id INT UNSIGNED NOT NULL,
  remetente_id INT UNSIGNED NOT NULL,
  texto TEXT NULL,
  imagem VARCHAR(255) NULL,
  lida TINYINT(1) DEFAULT 0,
  criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (conversa_id) REFERENCES conversas(id) ON DELETE CASCADE,
  FOREIGN KEY (remetente_id) REFERENCES usuarios(id)
) ENGINE=InnoDB;

-- ============ SEGURANÇA / AUDITORIA ============
CREATE TABLE tentativas_login (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  email VARCHAR(160), ip VARCHAR(45),
  criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX (email, ip)
) ENGINE=InnoDB;

-- ============ DADOS INICIAIS ============
INSERT INTO categorias (nome, slug) VALUES
('Anéis','aneis'),('Alianças','aliancas'),('Colares','colares'),('Pulseiras','pulseiras'),
('Brincos','brincos'),('Relógios','relogios'),('Pingentes','pingentes'),('Conjuntos','conjuntos'),
('Joias masculinas','joias-masculinas'),('Joias femininas','joias-femininas');

INSERT INTO atendimento_horarios (dia_semana, abre, fecha, ativo) VALUES
(0,NULL,NULL,0),(1,'09:00','18:00',1),(2,'09:00','18:00',1),(3,'09:00','18:00',1),
(4,'09:00','18:00',1),(5,'09:00','18:00',1),(6,'09:00','13:00',1);
