-- ============================================================
-- CINERATE — Script de criação e povoamento da base de dados
--
-- Instruções de uso:
--   1. Abre o phpMyAdmin ou o terminal MySQL
--   2. Corre este script completo
--   3. A base de dados fica pronta a usar com dados de teste
--
-- Credenciais de teste criadas:
--   Admin  → email: admin@cinerate.pt   | password: admin123
--   User 1 → email: ines@cinerate.pt    | password: user123
--   User 2 → email: rafael@cinerate.pt  | password: user123
-- ============================================================


-- ------------------------------------------------------------
-- BASE DE DADOS
-- ------------------------------------------------------------

-- Cria a BD se ainda não existir e selecciona-a
CREATE DATABASE IF NOT EXISTS cinerate
    CHARACTER SET utf8mb4        -- Suporte completo a Unicode (emojis, etc.)
    COLLATE utf8mb4_unicode_ci;  -- Ordenação e comparação correcta de acentos

USE cinerate;


-- ------------------------------------------------------------
-- TABELA: users
-- Armazena os dados de todos os utilizadores registados.
--
-- Colunas adicionadas em relação à versão original:
--   photo         — foto de perfil (usada em profile.php)
--   role          — papel do utilizador (controlo de acesso admin)
--   reset_token   — token de recuperação de password
--   reset_expires — data de expiração do token (1 hora)
-- Alteração: theme DEFAULT alterado de 'light' para 'dark'
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS users (
                                     id            INT           AUTO_INCREMENT PRIMARY KEY,
                                     name          VARCHAR(100)  NOT NULL,                        -- Nome completo do utilizador
    email         VARCHAR(150)  NOT NULL UNIQUE,                 -- Email único — usado para login
    password      VARCHAR(255)  NOT NULL,                        -- Hash bcrypt da password (password_hash)
    bio           TEXT,                                          -- Biografia opcional do utilizador
    photo         VARCHAR(255)  DEFAULT NULL,                    -- Nome do ficheiro da foto de perfil (em uploads/profiles/)
    role          ENUM('user','admin') DEFAULT 'user',           -- Papel: 'user' (normal) ou 'admin' (gestor)
    theme         ENUM('light','dark') DEFAULT 'dark',           -- Tema visual preferido (dark por defeito)
    reset_token   VARCHAR(64)   DEFAULT NULL,                    -- Token de recuperação de password (64 chars hex)
    reset_expires DATETIME      DEFAULT NULL,                    -- Data/hora de expiração do token (1 hora)
    created_at    TIMESTAMP     DEFAULT CURRENT_TIMESTAMP        -- Data de registo na plataforma
    );


-- ------------------------------------------------------------
-- TABELA: movies
-- Catálogo de filmes disponíveis para avaliação.
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS movies (
                                      id          INT           AUTO_INCREMENT PRIMARY KEY,
                                      title       VARCHAR(150)  NOT NULL,                          -- Título do filme
    year        INT           DEFAULT NULL,                      -- Ano de lançamento (NULL se desconhecido)
    genre       VARCHAR(100)  DEFAULT NULL,                      -- Género(s) do filme (ex: "Drama, Acção")
    description TEXT,                                            -- Sinopse / descrição do filme
    image       VARCHAR(255)  DEFAULT NULL,                      -- Nome do ficheiro do poster (em uploads/)
    created_at  TIMESTAMP     DEFAULT CURRENT_TIMESTAMP          -- Data de adição ao catálogo
    );


-- ------------------------------------------------------------
-- TABELA: reviews
-- Críticas escritas pelos utilizadores sobre os filmes.
--
-- Alteração em relação à versão original:
--   UNIQUE KEY (user_id, movie_id) — garante 1 crítica por
--   utilizador por filme, também ao nível da base de dados
--   (o PHP faz a verificação, mas a BD é a última barreira)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS reviews (
                                       id          INT   AUTO_INCREMENT PRIMARY KEY,
                                       user_id     INT   NOT NULL,                                  -- Utilizador que escreveu a crítica
                                       movie_id    INT   NOT NULL,                                  -- Filme avaliado
                                       rating      INT   NOT NULL CHECK (rating BETWEEN 1 AND 10), -- Nota de 1 a 10 (validada na BD)
    review_text TEXT  NOT NULL,                                  -- Texto da crítica
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,             -- Data de publicação

-- ON DELETE CASCADE: apaga as críticas se o utilizador ou filme for apagado
    FOREIGN KEY (user_id)  REFERENCES users(id)  ON DELETE CASCADE,
    FOREIGN KEY (movie_id) REFERENCES movies(id) ON DELETE CASCADE,

    -- Impede críticas duplicadas do mesmo utilizador no mesmo filme
    UNIQUE KEY unique_user_movie (user_id, movie_id)
    );


-- ------------------------------------------------------------
-- TABELA: comments
-- Comentários e respostas (replies) nos filmes.
-- Estrutura aninhada: parent_comment_id NULL = comentário raiz
--                     parent_comment_id = ID = resposta a esse comentário
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS comments (
                                        id                INT  AUTO_INCREMENT PRIMARY KEY,
                                        user_id           INT  NOT NULL,                             -- Autor do comentário
                                        movie_id          INT  NOT NULL,                             -- Filme comentado
                                        parent_comment_id INT  NULL DEFAULT NULL,                    -- NULL = raiz | ID = reply
                                        comment_text      TEXT NOT NULL,                             -- Texto do comentário
                                        created_at        TIMESTAMP DEFAULT CURRENT_TIMESTAMP,       -- Data de publicação

                                        FOREIGN KEY (user_id)           REFERENCES users(id)    ON DELETE CASCADE,
    FOREIGN KEY (movie_id)          REFERENCES movies(id)   ON DELETE CASCADE,
    -- Ao apagar um comentário raiz, as suas respostas são também apagadas
    FOREIGN KEY (parent_comment_id) REFERENCES comments(id) ON DELETE CASCADE
    );


-- ------------------------------------------------------------
-- TABELA: notifications
-- Notificações geradas automaticamente (ex: alguém respondeu
-- ao teu comentário). Lidas via AJAX a cada 5 segundos.
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS notifications (
                                             id         INT           AUTO_INCREMENT PRIMARY KEY,
                                             user_id    INT           NOT NULL,                           -- Utilizador que recebe a notificação
                                             message    VARCHAR(255)  NOT NULL,                           -- Texto da notificação
    is_read    TINYINT(1)    DEFAULT 0,                          -- 0 = não lida | 1 = lida
    created_at TIMESTAMP     DEFAULT CURRENT_TIMESTAMP,          -- Data de criação

    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    );


-- ============================================================
-- DADOS DE TESTE
-- Permitem demonstrar e testar a aplicação imediatamente.
-- ============================================================


-- ------------------------------------------------------------
-- UTILIZADORES DE TESTE
-- As passwords foram geradas com password_hash() em PHP.
-- Admin:  password = admin123
-- Users:  password = user123
-- Para gerar novos hashes: php -r "echo password_hash('password', PASSWORD_DEFAULT);"
-- ------------------------------------------------------------
INSERT INTO users (name, email, password, bio, role, theme) VALUES
                                                                (
                                                                    'Admin CineRate',
                                                                    'admin@cinerate.pt',
                                                                    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
                                                                    'Administrador da plataforma CineRate.',
                                                                    'admin',
                                                                    'dark'
                                                                ),
                                                                (
                                                                    'Inês Sousa',
                                                                    'ines@cinerate.pt',
                                                                    '$2y$10$TKh8H1.PFgs/3gHGQqjhGeM7m.5T8X5wYnjEeKNOM/VqbJgFVkVm6',
                                                                    'Apaixonada por cinema independente e ficção científica.',
                                                                    'user',
                                                                    'dark'
                                                                ),
                                                                (
                                                                    'Rafael Almeida',
                                                                    'rafael@cinerate.pt',
                                                                    '$2y$10$TKh8H1.PFgs/3gHGQqjhGeM7m.5T8X5wYnjEeKNOM/VqbJgFVkVm6',
                                                                    'Fã de cinema clássico e thrillers psicológicos.',
                                                                    'user',
                                                                    'dark'
                                                                );


-- ------------------------------------------------------------
-- FILMES DE TESTE
-- ------------------------------------------------------------
INSERT INTO movies (title, year, genre, description) VALUES
                                                         (
                                                             'Interstellar',
                                                             2014,
                                                             'Ficção Científica, Drama',
                                                             'Quando a Terra se torna inabitável, um grupo de exploradores viaja através de um buraco de minhoca à procura de um novo lar para a humanidade.'
                                                         ),
                                                         (
                                                             'The Dark Knight',
                                                             2008,
                                                             'Acção, Crime',
                                                             'O Batman enfrenta o Joker, um criminoso caótico que quer mergulhar Gotham City no caos e na anarquia.'
                                                         ),
                                                         (
                                                             'Inception',
                                                             2010,
                                                             'Ficção Científica, Acção',
                                                             'Um ladrão especializado em roubar segredos do subconsciente recebe uma missão inversa: plantar uma ideia na mente de um alvo.'
                                                         ),
                                                         (
                                                             'Pulp Fiction',
                                                             1994,
                                                             'Crime, Drama',
                                                             'Várias histórias de crime em Los Angeles interligam-se de formas inesperadas, envolvendo assassinos, boxers e gangsters.'
                                                         ),
                                                         (
                                                             'Forrest Gump',
                                                             1994,
                                                             'Drama, Romance',
                                                             'A vida extraordinária de um homem simples do Alabama que, sem o saber, participa nos grandes eventos da história americana.'
                                                         );


-- ------------------------------------------------------------
-- CRÍTICAS DE TESTE
-- ------------------------------------------------------------
INSERT INTO reviews (user_id, movie_id, rating, review_text) VALUES
                                                                 (2, 1, 9,  'Uma obra-prima visual e emocional. A forma como o Nolan explora o tempo e o amor é simplesmente incrível.'),
                                                                 (3, 1, 8,  'Filme incrível, mas o final deixa algumas perguntas no ar. Mesmo assim, obrigatório!'),
                                                                 (2, 2, 10, 'O melhor filme de super-heróis de sempre. O Heath Ledger como Joker é absolutamente genial.'),
                                                                 (3, 3, 9,  'Mindfuck do melhor! Cada vez que vejo descubro algo novo. Obra-prima de Nolan.');


-- ------------------------------------------------------------
-- COMENTÁRIOS DE TESTE (com replies aninhados)
-- ------------------------------------------------------------
INSERT INTO comments (user_id, movie_id, parent_comment_id, comment_text) VALUES
-- Comentário raiz (id=1): Inês sobre Interstellar
(2, 1, NULL, 'Que sequência final incrível! A cena no tesseract deu-me arrepios.'),
-- Reply (id=2): Rafael responde ao comentário 1 de Inês
(3, 1, 1,    'Concordo! E a banda sonora do Hans Zimmer nessa cena é arrepiante.'),
-- Comentário raiz (id=3): Rafael sobre The Dark Knight
(3, 2, NULL, 'O melhor filme de super-heróis de sempre. Sem discussão.'),
-- Reply (id=4): Inês responde ao comentário 3 de Rafael
(2, 2, 3,    'Totalmente de acordo! O Ledger merecia todos os Óscares do mundo.');


-- ------------------------------------------------------------
-- NOTIFICAÇÃO DE TESTE
-- Rafael recebe notificação pela resposta de Inês ao comentário 3
-- ------------------------------------------------------------
INSERT INTO notifications (user_id, message) VALUES
    (3, 'Inês Sousa respondeu ao teu comentário.');