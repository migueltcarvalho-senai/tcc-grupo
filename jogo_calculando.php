<?php
/**
 * jogo_calculando.php — Jogo de Operações Matemáticas
 * Sketchbook Educacional — Ensino Fundamental 1
 *
 * 3 níveis de dificuldade progressiva:
 * - Básico:  7 acertos → avança para Médio  (reset ao errar ou tempo zerar)
 * - Médio:   7 acertos → avança para Difícil
 * - Difícil: infinito até errar ou tempo zerar
 *
 * Cronômetro 30s com 3 estrelas (perde 1 a cada 10s).
 * Teclado virtual 3×4 + teclado físico.
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/proteger.php';

$nomeUsuario   = $_SESSION['user'] ?? '';
$pontosUsuario = (int) ($_SESSION['pontuacao'] ?? 0);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Calculando — Sketchbook Educacional</title>
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><text y=%22.9em%22 font-size=%2290%22>🧮</text></svg>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fredoka:wght@500;600;700&family=Nunito:wght@700;800;900&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'papel-branco':       '#FFFFFF',
                        'papel-claro':        '#F8FAF6',
                        'tinta-escura':       '#0F172A',
                        'verde-vibrante':     '#00C853',
                        'verde-escuro':       '#054D20',
                        'laranja-vibrante':   '#FF6D00',
                        'laranja-escuro':     '#803700',
                        'azul-vibrante':      '#00B0FF',
                        'azul-escuro':        '#004B87',
                        'amarelo-vibrante':   '#FFD600',
                        'amarelo-escuro':     '#806B00',
                        'rosa-vibrante':      '#FF4081',
                        'rosa-escuro':        '#c0185b',
                        'vermelho-vivo':      '#EF4444',
                        'vermelho-escuro':    '#991B1B',
                    },
                    fontFamily: {
                        'gooddog': ['Gooddog', 'cursive', 'sans-serif'],
                        'bingo':   ['Bingo', 'cursive', 'sans-serif'],
                        'fredoka': ['Fredoka', 'sans-serif'],
                        'nunito':  ['Nunito', 'sans-serif'],
                    },
                    boxShadow: {
                        'sombra-cartum-laranja': '0px 6px 0px #803700',
                        'sombra-cartum-verde':   '0px 6px 0px #054D20',
                        'sombra-cartum-branca':  '0px 5px 0px #0F172A',
                        'sombra-cartum-escura':  '0px 4px 0px #0F172A',
                    }
                }
            }
        }
    </script>
    <link rel="stylesheet" href="css/index_php.css">
    <link rel="stylesheet" href="css/acessibilidade.css">

    <style>
        /* Teclado virtual */
        .tecla {
            transition: transform 0.08s ease, box-shadow 0.08s ease;
            user-select: none;
        }
        .tecla:active {
            transform: translateY(4px);
            box-shadow: 0px 1px 0px #803700 !important;
        }
        /* Entrada de resposta */
        #display-resposta {
            min-height: 56px;
            letter-spacing: 0.08em;
        }
        /* Feedback flash */
        @keyframes flash-certo {
            0%, 100% { background-color: #FF6D00; }
            50%       { background-color: #00C853; }
        }
        @keyframes flash-errado {
            0%, 100% { background-color: #FF6D00; }
            25%, 75% { background-color: #EF4444; }
        }
        .flash-certo  { animation: flash-certo  0.5s ease; }
        .flash-errado { animation: flash-errado 0.5s ease; }

        /* Estrelas pulsando quando vai sumir */
        @keyframes pulsar-estrela {
            0%, 100% { opacity: 1; transform: scale(1); }
            50%       { opacity: 0.5; transform: scale(0.85); }
        }
        .estrela-pulsando { animation: pulsar-estrela 0.6s ease-in-out 3; }

        /* Overlay fim de jogo */
        #overlay-fim {
            backdrop-filter: blur(4px);
        }
    </style>
</head>
<body class="font-bingo min-h-screen text-tinta-escura flex flex-col p-2 sm:p-4 md:p-6 relative">

    <div id="elementos-fundo-oscilantes" aria-hidden="true">
        <div class="elemento-fundo-oscilante elemento-fundo-1"></div>
        <div class="elemento-fundo-oscilante elemento-fundo-2"></div>
        <div class="elemento-fundo-oscilante elemento-fundo-3"></div>
        <div class="elemento-fundo-oscilante elemento-fundo-4"></div>
    </div>

    <div id="livro-conteiner-principal" class="w-full flex-1 flex flex-col caderno-pagina-branca rounded-3xl shadow-2xl p-4 sm:p-6 md:p-8 relative">

        <!-- ESPIRAIS -->
        <div id="espiral-topo-caderno" class="w-full flex justify-between items-center -mt-8 sm:-mt-10 mb-4 z-20 px-2 sm:px-6">
            <div class="espiral-caderno"></div>
            <div class="espiral-caderno"></div>
            <div class="espiral-caderno"></div>
            <div class="espiral-caderno"></div>
            <div class="espiral-caderno"></div>
            <div class="espiral-caderno"></div>
            <div class="espiral-caderno"></div>
            <div class="espiral-caderno hidden sm:block"></div>
            <div class="espiral-caderno hidden sm:block"></div>
            <div class="espiral-caderno hidden md:block"></div>
            <div class="espiral-caderno hidden md:block"></div>
            <div class="espiral-caderno hidden md:block"></div>
        </div>

        <!-- BARRA: apenas botão Sair do Jogo à direita -->
        <header class="w-full flex items-center justify-end gap-3 pb-3 mb-4">
            <a href="hub_matematica.php"
               class="cartao-dinamico flex items-center gap-2 bg-laranja-vibrante text-white borda-tinta-escura rounded-xl px-5 py-2 font-bingo font-bold text-sm shadow-sombra-cartum-laranja transition-all no-underline">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
                    <path d="M19 12H5M12 5l-7 7 7 7"/>
                </svg>
                SAIR DO JOGO
            </a>
        </header>

        <!-- CONTEÚDO PRINCIPAL -->
        <main class="w-full flex-1 flex flex-col lg:flex-row gap-6 items-stretch justify-center">

            <!-- PAINEL ESQUERDO: INFO -->
            <div class="flex flex-col items-center gap-4 lg:w-56 shrink-0">

                <!-- Nível -->
                <div class="w-full bg-laranja-vibrante borda-tinta-escura rounded-2xl px-4 py-3 text-center shadow-sombra-cartum-laranja">
                    <p class="font-bingo font-bold text-xs text-orange-100 uppercase tracking-widest">Nível</p>
                    <p id="label-nivel" class="font-gooddog text-3xl text-white font-bold tracking-wide">BÁSICO</p>
                </div>

                <!-- Pontuação desta rodada -->
                <div class="w-full bg-amarelo-vibrante borda-tinta-escura rounded-2xl px-4 py-3 text-center shadow-sombra-cartum-branca">
                    <p class="font-bingo font-bold text-xs text-yellow-900 uppercase tracking-widest">Acertos</p>
                    <p id="label-acertos" class="font-gooddog text-4xl text-tinta-escura font-bold">0</p>
                </div>

                <!-- Estrelas -->
                <div class="w-full bg-papel-claro borda-tinta-escura rounded-2xl px-4 py-3 text-center shadow-sombra-cartum-branca">
                    <p class="font-bingo font-bold text-xs text-gray-500 uppercase tracking-widest mb-1">Estrelas</p>
                    <div id="estrelas-container" class="flex justify-center items-center gap-2 py-1">
                        <img id="estrela-1" src="icones/estrela.svg" alt="Estrela" class="w-8 h-8 transition-all duration-300 drop-shadow-sm">
                        <img id="estrela-2" src="icones/estrela.svg" alt="Estrela" class="w-8 h-8 transition-all duration-300 drop-shadow-sm">
                        <img id="estrela-3" src="icones/estrela.svg" alt="Estrela" class="w-8 h-8 transition-all duration-300 drop-shadow-sm">
                    </div>
                </div>

                <!-- Cronômetro -->
                <div class="w-full borda-tinta-escura rounded-2xl px-4 py-3 text-center shadow-sombra-cartum-branca" id="box-cronometro" style="background-color:#00C853;">
                    <p class="font-bingo font-bold text-xs text-green-100 uppercase tracking-widest">Tempo</p>
                    <p id="label-tempo" class="font-gooddog text-5xl text-white font-bold">30</p>
                </div>

            </div>

            <!-- PAINEL DIREITO: JOGO -->
            <div class="flex-1 flex flex-col items-center justify-center gap-6">

                <!-- Painel da conta -->
                <div id="painel-conta" class="w-full max-w-md bg-laranja-vibrante borda-tinta-escura rounded-3xl p-6 shadow-sombra-cartum-laranja flex flex-col items-center gap-4">

                    <!-- Conta -->
                    <p id="display-conta" class="font-gooddog text-4xl sm:text-5xl text-white font-bold tracking-widest text-center">
                        3 × 7 =
                    </p>

                    <!-- Display da resposta digitada -->
                    <div id="display-resposta"
                         class="w-full bg-papel-branco borda-tinta-escura rounded-2xl px-6 py-3 font-gooddog text-4xl text-tinta-escura font-bold text-center shadow-sombra-cartum-branca min-h-[64px] flex items-center justify-center">
                        &nbsp;
                    </div>

                </div>

                <!-- Teclado virtual 3×4 -->
                <div id="teclado-virtual" class="grid grid-cols-3 gap-3 w-full max-w-xs">
                    <!-- Linha 1: 1 2 3 -->
                    <button class="tecla bg-papel-branco borda-tinta-escura rounded-2xl py-4 font-gooddog text-2xl font-bold shadow-sombra-cartum-branca hover:bg-amarelo-vibrante transition-colors" data-valor="1">1</button>
                    <button class="tecla bg-papel-branco borda-tinta-escura rounded-2xl py-4 font-gooddog text-2xl font-bold shadow-sombra-cartum-branca hover:bg-amarelo-vibrante transition-colors" data-valor="2">2</button>
                    <button class="tecla bg-papel-branco borda-tinta-escura rounded-2xl py-4 font-gooddog text-2xl font-bold shadow-sombra-cartum-branca hover:bg-amarelo-vibrante transition-colors" data-valor="3">3</button>
                    <!-- Linha 2: 4 5 6 -->
                    <button class="tecla bg-papel-branco borda-tinta-escura rounded-2xl py-4 font-gooddog text-2xl font-bold shadow-sombra-cartum-branca hover:bg-amarelo-vibrante transition-colors" data-valor="4">4</button>
                    <button class="tecla bg-papel-branco borda-tinta-escura rounded-2xl py-4 font-gooddog text-2xl font-bold shadow-sombra-cartum-branca hover:bg-amarelo-vibrante transition-colors" data-valor="5">5</button>
                    <button class="tecla bg-papel-branco borda-tinta-escura rounded-2xl py-4 font-gooddog text-2xl font-bold shadow-sombra-cartum-branca hover:bg-amarelo-vibrante transition-colors" data-valor="6">6</button>
                    <!-- Linha 3: 7 8 9 -->
                    <button class="tecla bg-papel-branco borda-tinta-escura rounded-2xl py-4 font-gooddog text-2xl font-bold shadow-sombra-cartum-branca hover:bg-amarelo-vibrante transition-colors" data-valor="7">7</button>
                    <button class="tecla bg-papel-branco borda-tinta-escura rounded-2xl py-4 font-gooddog text-2xl font-bold shadow-sombra-cartum-branca hover:bg-amarelo-vibrante transition-colors" data-valor="8">8</button>
                    <button class="tecla bg-papel-branco borda-tinta-escura rounded-2xl py-4 font-gooddog text-2xl font-bold shadow-sombra-cartum-branca hover:bg-amarelo-vibrante transition-colors" data-valor="9">9</button>
                    <!-- Linha 4: APAGAR 0 = -->
                    <button class="tecla bg-vermelho-vivo text-white borda-tinta-escura rounded-2xl py-4 font-gooddog text-xl font-bold shadow-[0px_5px_0px_#991B1B] hover:bg-red-700 transition-colors" data-valor="del">⌫</button>
                    <button class="tecla bg-papel-branco borda-tinta-escura rounded-2xl py-4 font-gooddog text-2xl font-bold shadow-sombra-cartum-branca hover:bg-amarelo-vibrante transition-colors" data-valor="0">0</button>
                    <button class="tecla bg-verde-vibrante text-white borda-tinta-escura rounded-2xl py-4 font-gooddog text-xl font-bold shadow-sombra-cartum-verde hover:bg-green-500 transition-colors" data-valor="ok">=</button>
                </div>

            </div>
        </main>
    </div>

    <!-- OVERLAY FIM DE JOGO -->
    <div id="overlay-fim" class="fixed inset-0 bg-black/60 z-50 hidden flex items-center justify-center p-4">
        <div class="bg-papel-branco borda-tinta-escura rounded-3xl p-8 max-w-sm w-full text-center shadow-[0px_8px_0px_#0F172A] flex flex-col gap-5">
            <div class="text-6xl">🎲</div>
            <h2 class="font-gooddog text-4xl font-bold text-tinta-escura tracking-wide">FIM DE JOGO!</h2>
            <p class="font-bingo text-lg font-extrabold text-gray-700">
                Você acertou <span id="fim-acertos" class="text-laranja-vibrante font-gooddog text-2xl font-bold">0</span> conta(s)!
            </p>
            <div class="flex flex-col sm:flex-row gap-3">
                <button id="btn-reiniciar"
                        class="flex-1 bg-verde-vibrante text-white borda-tinta-escura rounded-2xl px-6 py-3 font-gooddog text-xl font-bold shadow-sombra-cartum-verde hover:bg-green-500 transition-colors cursor-pointer">
                    REINICIAR
                </button>
                <a href="hub_matematica.php"
                   class="flex-1 bg-laranja-vibrante text-white borda-tinta-escura rounded-2xl px-6 py-3 font-gooddog text-xl font-bold shadow-sombra-cartum-laranja hover:bg-orange-600 transition-colors no-underline text-center">
                    INÍCIO
                </a>
            </div>
        </div>
    </div>

    <script>
    // ============================================================
    // BANCO DE QUESTÕES
    // ============================================================
    const QUESTOES = {
        basico: [
            {conta: '3 × 7', resp: 21},
            {conta: '20 − 15', resp: 5},
            {conta: '30 + 9', resp: 39},
            {conta: '8 × 4', resp: 32},
            {conta: '45 − 18', resp: 27},
            {conta: '16 + 23', resp: 39},
            {conta: '24 ÷ 6', resp: 4},
            {conta: '5 × 9', resp: 45},
            {conta: '50 − 27', resp: 23},
            {conta: '36 + 15', resp: 51},
            {conta: '48 ÷ 8', resp: 6},
            {conta: '7 × 6', resp: 42},
        ],
        medio: [
            {conta: '8 × 20', resp: 160},
            {conta: '86 − 37', resp: 49},
            {conta: '53 + 41', resp: 94},
            {conta: '72 ÷ 9', resp: 8},
            {conta: '15 × 7', resp: 105},
            {conta: '123 − 56', resp: 67},
            {conta: '78 + 64', resp: 142},
            {conta: '96 ÷ 12', resp: 8},
            {conta: '13 × 8', resp: 104},
            {conta: '200 − 87', resp: 113},
        ],
        dificil: [
            {conta: '11 × 30', resp: 330},
            {conta: '200 − 139', resp: 61},
            {conta: '145 + 71', resp: 216},
            {conta: '144 ÷ 12', resp: 12},
            {conta: '25 × 14', resp: 350},
            {conta: '500 − 273', resp: 227},
            {conta: '389 + 246', resp: 635},
            {conta: '256 ÷ 16', resp: 16},
            {conta: '17 × 19', resp: 323},
            {conta: '1000 − 487', resp: 513},
            {conta: '267 + 384', resp: 651},
            {conta: '18 × 22', resp: 396},
        ],
    };

    // ============================================================
    // ESTADO DO JOGO
    // ============================================================
    const NIVEL_ORDEM = ['basico', 'medio', 'dificil'];
    const ALVO_NIVEL  = {basico: 7, medio: 7, dificil: Infinity};

    let estado = {
        nivel:         'basico',
        acertos:       0,
        acertosNivel:  0,
        digitado:      '',
        questaoAtual:  null,
        usadasNivel:   [],   // índices já usados no nível atual
        tempoBloq:     false, // bloqueia input durante feedback
    };

    let cronometroId  = null;
    let tempoRestante = 30;
    let estrelasAtivas = 3;

    // ============================================================
    // ELEMENTOS DOM
    // ============================================================
    const elConta    = document.getElementById('display-conta');
    const elResp     = document.getElementById('display-resposta');
    const elNivel    = document.getElementById('label-nivel');
    const elAcertos  = document.getElementById('label-acertos');
    const elTempo    = document.getElementById('label-tempo');
    const elBoxCron  = document.getElementById('box-cronometro');
    const overlayFim = document.getElementById('overlay-fim');
    const elFimAcert = document.getElementById('fim-acertos');
    const btnReini   = document.getElementById('btn-reiniciar');

    const elEstrelas = [
        document.getElementById('estrela-1'),
        document.getElementById('estrela-2'),
        document.getElementById('estrela-3'),
    ];

    // ============================================================
    // LÓGICA DE QUESTÕES
    // ============================================================
    function sortearQuestao() {
        const pool = QUESTOES[estado.nivel];
        // Resetar usadas se esgotadas
        if (estado.usadasNivel.length >= pool.length) {
            estado.usadasNivel = [];
        }
        const disponiveis = pool.map((_, i) => i).filter(i => !estado.usadasNivel.includes(i));
        const idx = disponiveis[Math.floor(Math.random() * disponiveis.length)];
        estado.usadasNivel.push(idx);
        estado.questaoAtual = pool[idx];
        elConta.textContent = estado.questaoAtual.conta + ' =';
        limparDisplay();
    }

    function limparDisplay() {
        estado.digitado = '';
        elResp.innerHTML = '&nbsp;';
    }

    // ============================================================
    // VERIFICAR RESPOSTA
    // ============================================================
    function verificarResposta() {
        if (estado.tempoBloq || estado.digitado === '') return;
        const valor = parseInt(estado.digitado, 10);
        if (valor === estado.questaoAtual.resp) {
            acertou();
        } else {
            errou();
        }
    }

    function acertou() {
        estado.tempoBloq = true;
        estado.acertos++;
        estado.acertosNivel++;
        elAcertos.textContent = estado.acertos;
        // Flash verde
        const painel = document.getElementById('painel-conta');
        painel.classList.add('flash-certo');
        setTimeout(() => painel.classList.remove('flash-certo'), 500);

        // Reiniciar cronômetro a cada acerto
        reiniciarCronometro();

        // Verificar avanço de nível
        const idx = NIVEL_ORDEM.indexOf(estado.nivel);
        if (estado.acertosNivel >= ALVO_NIVEL[estado.nivel] && idx < NIVEL_ORDEM.length - 1) {
            estado.nivel = NIVEL_ORDEM[idx + 1];
            estado.acertosNivel = 0;
            estado.usadasNivel  = [];
            atualizarLabelNivel();
        }

        setTimeout(() => {
            estado.tempoBloq = false;
            sortearQuestao();
        }, 400);
    }

    function errou() {
        estado.tempoBloq = true;
        const painel = document.getElementById('painel-conta');
        painel.classList.add('flash-errado');
        setTimeout(() => painel.classList.remove('flash-errado'), 500);

        // Resetar para básico
        setTimeout(() => {
            estado.tempoBloq   = false;
            estado.nivel       = 'basico';
            estado.acertosNivel = 0;
            estado.usadasNivel  = [];
            atualizarLabelNivel();
            reiniciarCronometro();
            sortearQuestao();
        }, 600);
    }

    function atualizarLabelNivel() {
        const nomes = {basico: 'BÁSICO', medio: 'MÉDIO', dificil: 'DIFÍCIL'};
        elNivel.textContent = nomes[estado.nivel];
    }

    // ============================================================
    // CRONÔMETRO
    // ============================================================
    function iniciarCronometro() {
        clearInterval(cronometroId);
        tempoRestante  = 30;
        estrelasAtivas = 3;
        atualizarEstrelas();
        atualizarDisplayTempo();

        cronometroId = setInterval(() => {
            tempoRestante--;
            atualizarDisplayTempo();

            // Perde estrela a cada 10s decorridos (aos 20s, 10s, 0s)
            const estrPerdida = 3 - Math.floor((30 - tempoRestante) / 10);
            if (estrPerdida < estrelasAtivas) {
                estrelasAtivas = Math.max(0, estrPerdida);
                atualizarEstrelas(true);
            }

            if (tempoRestante <= 0) {
                clearInterval(cronometroId);
                fimDeJogo();
            }
        }, 1000);
    }

    function reiniciarCronometro() {
        clearInterval(cronometroId);
        tempoRestante  = 30;
        estrelasAtivas = 3;
        atualizarEstrelas();
        atualizarDisplayTempo();
        iniciarCronometro();
    }

    function atualizarDisplayTempo() {
        elTempo.textContent = tempoRestante;
        // Cor do box de acordo com urgência
        if (tempoRestante > 20)       elBoxCron.style.backgroundColor = '#00C853';
        else if (tempoRestante > 10)  elBoxCron.style.backgroundColor = '#FFD600';
        else                          elBoxCron.style.backgroundColor = '#EF4444';
    }

    function atualizarEstrelas(animar = false) {
        elEstrelas.forEach((el, i) => {
            if (i < estrelasAtivas) {
                el.style.opacity = '1';
                el.style.filter = 'none';
                if (animar && i === estrelasAtivas) {
                    el.classList.add('estrela-pulsando');
                    el.addEventListener('animationend', () => el.classList.remove('estrela-pulsando'), {once: true});
                }
            } else {
                el.style.opacity = '0.2';
                el.style.filter = 'grayscale(100%)';
            }
        });
    }

    // ============================================================
    // FIM DE JOGO
    // ============================================================
    function fimDeJogo() {
        clearInterval(cronometroId);
        elFimAcert.textContent = estado.acertos;
        overlayFim.classList.remove('hidden');
        overlayFim.classList.add('flex');
    }

    function reiniciarJogo() {
        overlayFim.classList.add('hidden');
        overlayFim.classList.remove('flex');
        estado.nivel        = 'basico';
        estado.acertos      = 0;
        estado.acertosNivel = 0;
        estado.usadasNivel  = [];
        estado.tempoBloq    = false;
        elAcertos.textContent = 0;
        atualizarLabelNivel();
        sortearQuestao();
        iniciarCronometro();
    }

    btnReini.addEventListener('click', reiniciarJogo);

    // ============================================================
    // INPUT — TECLADO VIRTUAL
    // ============================================================
    document.querySelectorAll('.tecla').forEach(btn => {
        btn.addEventListener('click', () => {
            if (estado.tempoBloq) return;
            const val = btn.dataset.valor;
            if (val === 'del') {
                estado.digitado = estado.digitado.slice(0, -1);
            } else if (val === 'ok') {
                verificarResposta();
                return;
            } else {
                if (estado.digitado.length < 6) estado.digitado += val;
            }
            elResp.textContent = estado.digitado || '\u00a0';
        });
    });

    // ============================================================
    // INPUT — TECLADO FÍSICO
    // ============================================================
    document.addEventListener('keydown', e => {
        if (estado.tempoBloq) return;
        if (e.key >= '0' && e.key <= '9') {
            if (estado.digitado.length < 6) {
                estado.digitado += e.key;
                elResp.textContent = estado.digitado;
            }
        } else if (e.key === 'Backspace') {
            estado.digitado = estado.digitado.slice(0, -1);
            elResp.textContent = estado.digitado || '\u00a0';
        } else if (e.key === 'Enter' || e.key === '=') {
            verificarResposta();
        }
    });

    // ============================================================
    // INICIAR
    // ============================================================
    sortearQuestao();
    iniciarCronometro();
    </script>

    <script src="js/acessibilidade.js"></script>
</body>
</html>
