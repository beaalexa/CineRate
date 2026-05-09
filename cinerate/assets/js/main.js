/* ============================================================
   CINERATE — JavaScript Principal
   Responsabilidades:
     - Notificações em tempo real (polling AJAX)
     - Feed de comentários dinâmico (polling AJAX)
     - Validação de formulários no lado do cliente
     - Alternância de tema claro/escuro
   ============================================================ */


/* ============================================================
   1. NOTIFICAÇÕES — Popup dinâmico
   ============================================================ */

/**
 * Cria e mostra um popup de notificação no canto inferior direito.
 * O popup desaparece automaticamente após 4 segundos.
 *
 * @param {string} message - Texto a apresentar na notificação
 */
function showNotification(message) {
    /* Cria o elemento do popup */
    const popup = document.createElement("div");
    popup.className = "notification-popup";
    popup.textContent = message;

    /* Adiciona ao body (o CSS trata do posicionamento fixo) */
    document.body.appendChild(popup);

    /* Remove o popup após 4 segundos */
    setTimeout(() => {
        /* Animação de saída suave antes de remover do DOM */
        popup.style.transition = "opacity 0.3s ease, transform 0.3s ease";
        popup.style.opacity = "0";
        popup.style.transform = "translateY(10px)";

        setTimeout(() => popup.remove(), 310);
    }, 4000);
}

/**
 * Vai buscar novas notificações ao servidor via fetch.
 * Chama showNotification() para cada notificação recebida.
 * É chamada periodicamente por setInterval.
 */
function checkNotifications() {
    fetch("/cinerate/actions/notifications.php")
        .then(response => {
            /* Verifica se a resposta HTTP foi bem-sucedida */
            if (!response.ok) {
                throw new Error("Erro HTTP: " + response.status);
            }
            return response.json();
        })
        .then(data => {
            /* Mostra cada notificação recebida */
            data.forEach(notification => {
                showNotification(notification.message);
            });
        })
        .catch(error => {
            /* Regista o erro na consola sem interromper a app */
            console.error("Erro ao carregar notificações:", error);
        });
}

/* Verifica notificações a cada 5 segundos (5000 ms) */
setInterval(checkNotifications, 5000);


/* ============================================================
   2. VALIDAÇÃO DE FORMULÁRIOS (lado do cliente)
   ============================================================ */

/**
 * Aplica validação a todos os formulários da página.
 * Corre quando o DOM está completamente carregado.
 *
 * Valida:
 *   - Campos marcados como [required] — não podem estar vazios
 *   - Campo de rating — tem de ser um número entre 1 e 10
 */
document.addEventListener("DOMContentLoaded", function () {

    /* Selecciona todos os formulários da página */
    const forms = document.querySelectorAll("form");

    forms.forEach(form => {

        form.addEventListener("submit", function (event) {
            /* Flag que controla se o formulário é válido */
            let valid = true;

            /* --- Validação de campos obrigatórios --- */
            const requiredFields = form.querySelectorAll("[required]");

            requiredFields.forEach(field => {
                if (field.value.trim() === "") {
                    /* Campo vazio — marca com borda vermelha e classe de erro */
                    valid = false;
                    field.classList.add("error");
                    field.setAttribute("aria-invalid", "true");
                } else {
                    /* Campo preenchido — remove indicadores de erro */
                    field.classList.remove("error");
                    field.removeAttribute("aria-invalid");
                }
            });

            /* --- Validação específica do campo de nota (rating) --- */
            const ratingInput = form.querySelector("input[name='rating']");

            if (ratingInput) {
                const value = parseInt(ratingInput.value, 10);

                /* A nota tem de ser um número inteiro entre 1 e 10 */
                if (isNaN(value) || value < 1 || value > 10) {
                    valid = false;
                    ratingInput.classList.add("error");
                    alert("A nota deve ser um número entre 1 e 10.");
                } else {
                    ratingInput.classList.remove("error");
                }
            }

            /* Se alguma validação falhou, impede o envio do formulário */
            if (!valid) {
                event.preventDefault();
                /* Faz scroll suave até ao primeiro campo com erro */
                const firstError = form.querySelector(".error");
                if (firstError) {
                    firstError.scrollIntoView({ behavior: "smooth", block: "center" });
                    firstError.focus();
                }
            }
        });

        /* Remove a marcação de erro quando o utilizador começa a escrever */
        form.querySelectorAll("input, textarea, select").forEach(field => {
            field.addEventListener("input", function () {
                this.classList.remove("error");
                this.removeAttribute("aria-invalid");
            });
        });
    });
});


/* ============================================================
   3. FEED DE COMENTÁRIOS — Actualização dinâmica (AJAX)
   ============================================================ */

/**
 * Vai buscar os comentários mais recentes ao servidor
 * e actualiza o elemento #latest-comments sem recarregar a página.
 *
 * Cada item mostra: nome do utilizador, filme, texto e data.
 */
function loadLatestComments() {
    const container = document.getElementById("latest-comments");

    /* Se o elemento não existir na página, não faz nada */
    if (!container) return;

    fetch("/cinerate/actions/latest_comments.php")
        .then(response => {
            if (!response.ok) {
                throw new Error("Erro HTTP: " + response.status);
            }
            return response.json();
        })
        .then(comments => {
            /* Se não houver comentários, mostra mensagem vazia */
            if (comments.length === 0) {
                container.innerHTML = `
                    <p class="text-muted text-center mt-16">
                        Ainda não existem comentários.
                    </p>`;
                return;
            }

            /* Limpa o conteúdo anterior antes de adicionar o novo */
            container.innerHTML = "";

            /* Cria um item no feed para cada comentário */
            comments.forEach(comment => {
                const item = document.createElement("div");
                item.className = "feed-item";

                /*
                 * Estrutura do item:
                 *   Nome do utilizador → comentou em → Título do Filme
                 *   Texto do comentário (truncado por CSS)
                 *   Data/hora do comentário
                 */
                item.innerHTML = `
                    <strong>${escapeHtml(comment.name)}</strong>
                    <span class="text-muted">comentou em
                        <a href="/cinerate/pages/movie.php?id=${parseInt(comment.movie_id)}">
                            ${escapeHtml(comment.title)}
                        </a>
                    </span>
                    <p>${escapeHtml(comment.comment_text)}</p>
                    <small>${escapeHtml(comment.created_at)}</small>
                `;

                container.appendChild(item);
            });
        })
        .catch(error => {
            /* Mostra mensagem de erro no contentor em vez de crashar */
            console.error("Erro ao carregar comentários:", error);
            if (container) {
                container.innerHTML = `
                    <p class="text-muted text-center mt-16">
                        Não foi possível carregar os comentários.
                    </p>`;
            }
        });
}

/**
 * Função auxiliar de segurança:
 * Escapa caracteres HTML especiais para prevenir ataques XSS
 * quando inserimos dados vindos do servidor no DOM.
 *
 * @param {string} str - String a sanitizar
 * @returns {string} - String com caracteres perigosos escapados
 */
function escapeHtml(str) {
    if (typeof str !== "string") return "";
    return str
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
}

/* Carrega os comentários imediatamente ao abrir a página */
loadLatestComments();

/* Actualiza o feed a cada 7 segundos para mostrar novos comentários */
setInterval(loadLatestComments, 7000);


/* ============================================================
   4. TEMA CLARO / ESCURO — Toggle
   ============================================================ */

/**
 * Alterna entre tema escuro (padrão) e tema claro.
 * Guarda a preferência no localStorage para a manter
 * entre sessões do utilizador.
 *
 * Uso no HTML: <button onclick="toggleTheme()">🌙</button>
 */
function toggleTheme() {
    const body = document.body;

    /* Alterna a classe "light" no body */
    body.classList.toggle("light");

    /* Guarda a preferência (true = claro, false = escuro) */
    const isLight = body.classList.contains("light");
    localStorage.setItem("cinerate_theme", isLight ? "light" : "dark");

    /* Actualiza o ícone do botão, se existir */
    const themeBtn = document.getElementById("theme-toggle");
    if (themeBtn) {
        themeBtn.textContent = isLight ? "☀️" : "🌙";
        themeBtn.setAttribute("aria-label", isLight ? "Mudar para tema escuro" : "Mudar para tema claro");
    }
}

/**
 * Aplica o tema guardado quando a página carrega.
 * Corre imediatamente para evitar "flash" de tema errado.
 */
(function applyStoredTheme() {
    const savedTheme = localStorage.getItem("cinerate_theme");

    if (savedTheme === "light") {
        document.body.classList.add("light");

        /* Actualiza o ícone do botão, se já estiver no DOM */
        const themeBtn = document.getElementById("theme-toggle");
        if (themeBtn) {
            themeBtn.textContent = "☀️";
            themeBtn.setAttribute("aria-label", "Mudar para tema escuro");
        }
    }
})();