````md
# 🎬 CineRate

> Descobre filmes. Partilha opiniões. Vive o cinema.

CineRate é uma plataforma web de críticas e comentários sobre filmes, desenvolvida no âmbito da unidade curricular de **Programação de Sistemas Web** da Universidade Atlântica.

---

## 📋 Descrição

O CineRate permite aos utilizadores:

- Criar conta, fazer login e gerir o perfil pessoal;
- Avaliar filmes com nota de 1 a 10;
- Escrever e ler críticas da comunidade;
- Comentar filmes e responder a outros utilizadores;
- Receber notificações em tempo real;
- Escolher entre tema claro e escuro.

Os administradores podem adicionar, editar e gerir o catálogo de filmes.

---

## 🛠️ Tecnologias Utilizadas

| Tecnologia | Utilização |
|------------|------------|
| PHP | Lógica do servidor e sessões |
| MySQL | Base de dados relacional |
| PDO | Ligação segura à base de dados |
| JavaScript | AJAX, validação e interatividade |
| HTML5 | Estrutura das páginas |
| CSS3 | Estilos, responsividade e temas |
| XAMPP | Servidor local (Apache + MySQL) |
| PhpStorm | Ambiente de desenvolvimento |

---

## 📁 Estrutura do Projeto

```text
cinerate/
│
├── actions/              ← Processamento de formulários (PHP)
│   ├── login_action.php
│   ├── register_action.php
│   ├── logout.php
│   ├── comment_action.php
│   ├── review_action.php
│   ├── add_movie_action.php
│   ├── update_movie_action.php
│   ├── update_profile.php
│   ├── notifications.php
│   └── latest_comments.php
│
├── assets/
│   ├── css/
│   │   └── style.css     ← Estilos globais da aplicação
│   ├── js/
│   │   └── main.js       ← JavaScript global (AJAX, validação, tema)
│   └── uploads/
│       └── profiles/     ← Fotos de perfil dos utilizadores
│
├── config/
│   └── db.php            ← Ligação à base de dados
│
├── includes/
│   ├── header.php        ← Cabeçalho e navbar globais
│   └── footer.php        ← Rodapé e inclusão de scripts
│
├── pages/
│   ├── login.php
│   ├── register.php
│   ├── movies.php
│   ├── movie.php
│   ├── profile.php
│   ├── user_profile.php
│   ├── add_movie.php
│   ├── edit_movie.php
│   ├── forgot_password.php
│   └── reset_password.php
│
├── cinerate.sql          ← Script da base de dados
├── index.php             ← Página inicial
└── README.md
````

---

## ⚙️ Instalação e Configuração

### Pré-requisitos

* XAMPP (Apache + MySQL + PHP)
* PHP 7.4 ou superior

---

### 1. Clonar o repositório

```bash
git clone https://github.com/beaalexa/cinerate.git
```

---

### 2. Mover para a pasta do XAMPP

```text
C:\xampp\htdocs\cinerate\
```

---

### 3. Importar a base de dados

No phpMyAdmin:

1. Criar uma base de dados chamada:

```text
cinerate
```

2. Clicar em **Import**
3. Selecionar o ficheiro:

```text
cinerate.sql
```

4. Clicar em **Go**

---

### 4. Iniciar o XAMPP

Iniciar:

* Apache
* MySQL

---

### 5. Abrir o projeto

```text
http://localhost/cinerate
```

---

## 🔐 Credenciais de Teste

| Papel         | Email                                           | Password |
| ------------- | ----------------------------------------------- | -------- |
| Administrador | [admin@cinerate.pt](mailto:admin@cinerate.pt)   | admin123 |
| Utilizador    | [ines@cinerate.pt](mailto:ines@cinerate.pt)     | user123  |
| Utilizador    | [rafael@cinerate.pt](mailto:rafael@cinerate.pt) | user123  |

---

## ✨ Funcionalidades

### 👤 Utilizadores

* Registo e login
* Recuperação de password
* Gestão de perfil
* Upload de foto de perfil
* Perfil público
* Tema claro e escuro

### 🎬 Filmes

* Catálogo de filmes
* Pesquisa por título e género
* Página individual de cada filme
* Upload de posters
* Edição de filmes (admin)

### 💬 Interações

* Publicação de críticas
* Sistema de avaliações
* Comentários públicos
* Respostas a comentários
* Feed dinâmico
* Notificações em tempo real

### ⚡ Interatividade

* AJAX
* Atualização automática sem recarregar páginas
* Validação de formulários no cliente
* Popups de notificações

---

## 🔒 Segurança

| Medida                                  | Descrição                       |
| --------------------------------------- | ------------------------------- |
| `password_hash()` / `password_verify()` | Passwords protegidas com bcrypt |
| PDO Prepared Statements                 | Prevenção de SQL Injection      |
| `htmlspecialchars()`                    | Prevenção de XSS                |
| Sessões PHP                             | Gestão segura de autenticação   |
| Validação de uploads                    | Controlo de ficheiros enviados  |
| Controlo de permissões                  | Gestão de administradores       |

---

## 📸 Interface

O design do CineRate foi inspirado em plataformas modernas de streaming.

Características principais:

* Tema escuro moderno;
* Accent roxo (`#7c3aed`);
* Glassmorphism na navbar;
* Cards interativos;
* Animações suaves;
* Design responsivo para mobile e desktop.

---

## 📚 Funcionalidades Dinâmicas

O projeto inclui funcionalidades dinâmicas desenvolvidas com JavaScript e AJAX:

* Notificações automáticas;
* Feed de comentários atualizado em tempo real;
* Validação dinâmica de formulários;
* Atualização automática de conteúdo sem recarregar páginas.

---

## 👨‍💻 Autores

* Beatriz Cansado 
* David Cardoso 
* Gustavo Vília 
* Renato Almeida 

---

## 🎓 Contexto Académico

Universidade Atlântica – Instituto Universitário

Licenciatura em Gestão de Sistemas e Computação – 3.º Ano

Unidade Curricular: Programação de Sistemas Web

Docente: Prof. Dr. Paulo Pombinho

Ano Letivo: 2025/2026

---

## 📄 Licença

Projeto académico desenvolvido exclusivamente para fins educativos.

```
```
