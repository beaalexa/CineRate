# 🎬 CineRate

> Avalia. Comenta. Descobre.

CineRate é uma plataforma web de críticas e comentários sobre filmes, desenvolvida no âmbito da unidade curricular de **Programação de Sistemas Web** da Universidade Atlântica.

---

## 👥 Equipa

| Nome | 
|------|
| Beatriz Cansado | 
| David Cardoso | 
| Gustavo Vília | 
| Renato Almeida | 

**Docente:** Prof. Dr. Paulo Pombinho

---

## 📋 Descrição

O CineRate permite aos utilizadores:
- Criar conta, fazer login e gerir o perfil pessoal
- Avaliar filmes com nota de 1 a 10
- Escrever e ler críticas da comunidade
- Comentar filmes e responder a outros utilizadores
- Receber notificações em tempo real (AJAX)
- Escolher entre tema claro e escuro

Os administradores podem adicionar, editar e gerir o catálogo de filmes.

---

## 🛠️ Tecnologias Utilizadas

| Tecnologia | Utilização |
|------------|------------|
| PHP | Lógica do servidor e sessões |
| MySQL | Base de dados relacional |
| PDO | Ligação segura à BD com prepared statements |
| JavaScript | AJAX, validação e interatividade |
| HTML5 | Estrutura das páginas |
| CSS3 | Estilos, tema claro/escuro e responsividade |
| XAMPP | Servidor local (Apache + MySQL) |

---

## 📁 Estrutura do Projeto

```
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
│   │   └── style.css     ← Estilos globais (19 secções comentadas)
│   ├── js/
│   │   └── main.js       ← JavaScript global (AJAX, validação, tema)
│   ├──  uploads/
│   │   └── profiles/     ← Fotos de perfil dos utilizadores
│   └── screenshots/      ← Screenshots das páginas do site
│   
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
├── cinerate.sql          ← Script de criação e povoamento da base de dados
├── index.php             ← Página inicial
└── README.md
```

---

## ⚙️ Instalação e Configuração

### Pré-requisitos
- XAMPP (Apache + MySQL + PHP)
- PHP 7.4 ou superior

### Passos

**1. Clonar o repositório**
```bash
git clone https://github.com/beaalexa/cinerate.git
```

**2. Mover para a pasta do XAMPP**
```
C:\xampp\htdocs\cinerate\
```

**3. Importar a base de dados**

No phpMyAdmin:
1. Clica em **Import**
2. Selecciona o ficheiro `cinerate.sql`
3. Clica **Go**

Ou pelo terminal:
```bash
mysql -u root -p < cinerate.sql
```

**4. Iniciar o XAMPP**

Inicia o **Apache** e o **MySQL** no XAMPP Control Panel.

**5. Abrir no browser**
```
http://localhost/cinerate
```

---

## 🔐 Credenciais de Teste

| Papel | Email | Password |
|-------|-------|----------|
| Administrador | admin@cinerate.pt | admin123 |
| Utilizador | ines@cinerate.pt | user123 |
| Utilizador | rafael@cinerate.pt | user123 |

---

## ✨ Funcionalidades

- ✅ Registo e login com hash bcrypt
- ✅ Recuperação de password com token de uso único (expiração de 1h)
- ✅ Gestão de perfil com upload de foto
- ✅ Tema claro / escuro persistente na base de dados
- ✅ Catálogo de filmes com pesquisa por título e género
- ✅ Sistema de críticas (1 por utilizador por filme)
- ✅ Comentários aninhados com respostas
- ✅ Notificações em tempo real via AJAX (polling a cada 5s)
- ✅ Feed dinâmico de comentários na página inicial (polling a cada 7s)
- ✅ Validação de formulários no cliente (JavaScript) e no servidor (PHP)
- ✅ Área de administração para gestão do catálogo
- ✅ Design responsivo (mobile e desktop)
- ✅ Perfis públicos de utilizadores

---

## 🔒 Segurança

| Medida | Descrição |
|--------|-----------|
| `password_hash()` / `password_verify()` | Passwords nunca guardadas em texto simples — bcrypt com salt automático |
| PDO Prepared Statements | Prevenção de SQL Injection em todas as queries |
| `session_regenerate_id(true)` | Prevenção de Session Fixation após login |
| `htmlspecialchars()` | Prevenção de XSS em todos os dados de saída HTML |
| `escapeHtml()` (JavaScript) | Prevenção de XSS no conteúdo dinâmico via AJAX |
| `mime_content_type()` | Validação do tipo real de ficheiros no upload (magic bytes) |
| Logout em 3 passos | `session_unset()` + cookie expirado + `session_destroy()` |
| Controlo de acesso por `role` | Verificação de sessão e papel em todos os ficheiros de ação |

---

## 📸 Interface

O design é inspirado em plataformas de streaming de cinema:
- Tema escuro por defeito com accent roxo (`#7c3aed`)
- Navbar com efeito glassmorphism (`backdrop-filter: blur`)
- Cards com animações suaves ao hover
- Botões pill com 3 variantes (primário, secundário, ghost)
- Layout totalmente responsivo com media queries para mobile

---

## 📸 Screenshots

### Página Inicial
"C:\xampp\htdocs\cinerate\assets\screenshots\home.png"

### Catálogo de Filmes
![Movies](./assets/screenshots/movies.png)

### Página de Filme
![Movie](./assets/screenshots/movie.png)

### Página Login
![Login](./assets/screenshots/login.png)

### Página Registo
![Register](./assets/screenshots/registo.png)

---

## 📚 Funcionalidades Dinâmicas

O projeto inclui funcionalidades dinâmicas desenvolvidas com JavaScript e AJAX:

Notificações automáticas;
Feed de comentários atualizado em tempo real;
Validação dinâmica de formulários;
Atualização automática de conteúdo sem recarregar páginas.

---

## 📄 Licença

Projeto académico desenvolvido exclusivamente para fins educativos.
