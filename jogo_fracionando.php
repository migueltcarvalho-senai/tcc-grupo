<?php
/**
 * jogo_fracionando.php — Jogo de Frações com Gráfico de Pizza
 * Sketchbook Educacional — Ensino Fundamental 1
 *
 * Gráfico de pizza SVG:
 * - Fatias preenchidas com a cor do retângulo temático
 * - Fatias vazias na cor de fundo do retângulo interno
 * - 4 opções de resposta (1 correta e 3 erradas)
 * - Ao errar ou zerar o tempo: Fim de Jogo (alerta com Reiniciar / Início)
 * - Cronômetro de 30s com 3 estrelas que somem a cada 10s
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
    <title>Fracionando — Sketchbook Educacional</title>
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><text y=%22.9em%22 font-size=%2290%22>🍕</text></svg>">
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
                        'sombra-cartum-azul':    '0px 6px 0px #004B87',
                        'sombra-cartum-laranja': '0px 6px 0px #803700',
                        'sombra-cartum-verde':   '0px 6px 0px #054D20',
                        'sombra-cartum-branca':  '0px 5px 0px #0F172A',
                    }
                }
            }
        }
    </script>
    <link rel="stylesheet" href="css/index_php.css">
    <link rel="stylesheet" href="css/acessibilidade.css">

    <style>
        .cartao-opcao {
            transition: transform 0.1s ease, box-shadow 0.1s ease, background-color 0.15s ease;
            user-select: none;
        }
        .cartao-opcao:hover {
            transform: translateY(-2px);
            box-shadow: 0px 6px 0px #0F172A;
        }
        .cartao-opcao:active {
            transform: translateY(2px);
            box-shadow: 0px 2px 0px #0F172A;
        }

        @keyframes pulsar-estrela {
            0%, 100% { opacity: 1; transform: scale(1); }
            50%       { opacity: 0.4; transform: scale(0.85); }
        }
        .estrela-pulsando { animation: pulsar-estrela 0.6s ease-in-out 3; }

        @keyframes feedback-correto {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }
        .anim-correto { animation: feedback-correto 0.4s ease; }

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

        <!-- BARRA: Apenas botão Sair do Jogo no lado direito -->
        <header class="w-full flex items-center justify-end gap-3 pb-3 mb-4">
            <a href="hub_matematica.php"
               class="cartao-dinamico flex items-center gap-2 bg-laranja-vibrante text-white borda-tinta-escura rounded-xl px-5 py-2 font-bingo font-bold text-sm shadow-sombra-cartum-laranja transition-all no-underline">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
                    <path d="M19 12H5M12 5l-7 7 7 7"/>
                </svg>
                SAIR DO JOGO
            </a>
        </header>

        <!-- CONTEÚDO PRINCIPAL (LAYOUT EM DUAS COLUNAS RESPONSIVAS) -->
        <main class="w-full flex-1 flex flex-col lg:flex-row gap-6 items-center justify-center">

            <!-- LADO ESQUERDO: 3 CAIXAS (Pontuação, Cronômetro, Estrelas) -->
            <div class="flex flex-col items-center gap-4 w-full sm:w-64 shrink-0">

                <!-- 1. Pontuação -->
                <div class="w-full bg-amarelo-vibrante borda-tinta-escura rounded-2xl px-4 py-3 text-center shadow-sombra-cartum-branca">
                    <p class="font-bingo font-bold text-xs text-yellow-900 uppercase tracking-widest">Pontuação</p>
                    <p id="label-pontos" class="font-gooddog text-5xl text-tinta-escura font-bold">0</p>
                    <span class="text-xs font-bold text-yellow-950">frações acertadas</span>
                </div>

                <!-- 2. Cronômetro -->
                <div id="box-cronometro" class="w-full borda-tinta-escura rounded-2xl px-4 py-3 text-center shadow-sombra-cartum-branca transition-colors" style="background-color: #00C853;">
                    <p class="font-bingo font-bold text-xs text-green-100 uppercase tracking-widest">Cronômetro</p>
                    <p id="label-tempo" class="font-gooddog text-5xl text-white font-bold">30</p>
                    <span class="text-xs font-bold text-white/90">segundos</span>
                </div>

                <!-- 3. Estrelas -->
                <div class="w-full bg-papel-claro borda-tinta-escura rounded-2xl px-4 py-3 text-center shadow-sombra-cartum-branca">
                    <p class="font-bingo font-bold text-xs text-gray-600 uppercase tracking-widest mb-1">Estrelas</p>
                    <div id="estrelas-container" class="flex justify-center items-center gap-2 py-1">
                        <img id="estrela-1" src="icones/estrela.svg" alt="Estrela" class="w-8 h-8 transition-all duration-300 drop-shadow-sm">
                        <img id="estrela-2" src="icones/estrela.svg" alt="Estrela" class="w-8 h-8 transition-all duration-300 drop-shadow-sm">
                        <img id="estrela-3" src="icones/estrela.svg" alt="Estrela" class="w-8 h-8 transition-all duration-300 drop-shadow-sm">
                    </div>
                </div>

            </div>

            <!-- LADO DIREITO: RETÂNGULO DO JOGO FRACIONANDO -->
            <div class="flex-1 w-full max-w-lg flex flex-col items-center">

                <div id="painel-fracao" class="w-full bg-azul-vibrante borda-tinta-escura rounded-3xl p-6 sm:p-7 shadow-sombra-cartum-azul flex flex-col items-center gap-5">

                    <!-- Título dentro do retângulo -->
                    <h2 class="font-gooddog text-4xl sm:text-5xl font-bold uppercase text-white tracking-widest text-center leading-none">
                        FRACIONANDO
                    </h2>

                    <!-- Retângulo branco interno centralizado com o Gráfico de Pizza -->
                    <div class="w-full bg-papel-branco borda-tinta-escura rounded-2xl p-4 flex flex-col items-center justify-center shadow-sombra-cartum-branca min-h-[250px]">
                        <p class="font-bingo text-xs sm:text-sm font-extrabold text-gray-500 mb-2 uppercase tracking-wider">
                            Qual fração está representada abaixo?
                        </p>
                        <!-- Container SVG da Pizza -->
                        <div id="container-pizza" class="w-48 h-48 sm:w-56 sm:h-56 flex items-center justify-center">
                            <!-- SVG inserido dinamicamente via JS -->
                        </div>
                    </div>

                    <!-- Tabela / Grid com 4 Opções de Fração -->
                    <div class="w-full">
                        <div id="grade-opcoes" class="grid grid-cols-2 gap-3 w-full">
                            <!-- 4 botões de opção gerados via JS -->
                        </div>
                    </div>

                </div>

            </div>

        </main>

    </div>

    <!-- OVERLAY FIM DE JOGO -->
    <div id="overlay-fim" class="fixed inset-0 bg-black/60 z-50 hidden flex items-center justify-center p-4">
        <div class="bg-papel-branco borda-tinta-escura rounded-3xl p-8 max-w-sm w-full text-center shadow-[0px_8px_0px_#0F172A] flex flex-col gap-5">
            <div class="text-6xl">🥧</div>
            <h2 class="font-gooddog text-4xl font-bold text-tinta-escura tracking-wide">FIM DE JOGO!</h2>
            <p class="font-bingo text-lg font-extrabold text-gray-700">
                Você acertou <span id="fim-pontos" class="text-azul-vibrante font-gooddog text-3xl font-bold">0</span> fração(ões)!
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
    // POOL DE FRAÇÕES PARA O ENSINO FUNDAMENTAL
    // ============================================================
    const FRACOES_BASE = [
        { num: 1, den: 2 },
        { num: 1, den: 3 },
        { num: 2, den: 3 },
        { num: 1, den: 4 },
        { num: 2, den: 4 },
        { num: 3, den: 4 },
        { num: 1, den: 5 },
        { num: 2, den: 5 },
        { num: 3, den: 5 },
        { num: 4, den: 5 },
        { num: 1, den: 6 },
        { num: 2, den: 6 },
        { num: 3, den: 6 },
        { num: 4, den: 6 },
        { num: 5, den: 6 },
        { num: 1, den: 8 },
        { num: 2, den: 8 },
        { num: 3, den: 8 },
        { num: 4, den: 8 },
        { num: 5, den: 8 },
        { num: 6, den: 8 },
        { num: 7, den: 8 },
        { num: 3, den: 10 },
        { num: 7, den: 10 }
    ];

    // Cor do retângulo do jogo que preenche as fatias
    const COR_PREENCHIMENTO = '#00B0FF';
    const COR_VAZIO          = '#FFFFFF';
    const COR_BORDA          = '#0F172A';

    let pontuacao = 0;
    let tempoRestante = 30;
    let estrelasAtivas = 3;
    let cronometroId = null;
    let fracAtual = null;
    let bloqueado = false;

    const elPontos   = document.getElementById('label-pontos');
    const elTempo    = document.getElementById('label-tempo');
    const elBoxCron  = document.getElementById('box-cronometro');
    const containerPizza = document.getElementById('container-pizza');
    const gradeOpcoes = document.getElementById('grade-opcoes');
    const overlayFim = document.getElementById('overlay-fim');
    const elFimPontos = document.getElementById('fim-pontos');
    const btnReiniciar = document.getElementById('btn-reiniciar');

    const elEstrelas = [
        document.getElementById('estrela-1'),
        document.getElementById('estrela-2'),
        document.getElementById('estrela-3')
    ];

    // ============================================================
    // GERADOR DE GRÁFICO DE PIZZA EM SVG
    // ============================================================
    function criarSvgPizza(num, den) {
        const cx = 100;
        const cy = 100;
        const r  = 82;
        const anguloPorFatia = (2 * Math.PI) / den;

        let paths = '';

        for (let i = 0; i < den; i++) {
            const aInicio = -Math.PI / 2 + i * anguloPorFatia;
            const aFim    = aInicio + anguloPorFatia;

            const x1 = cx + r * Math.cos(aInicio);
            const y1 = cy + r * Math.sin(aInicio);
            const x2 = cx + r * Math.cos(aFim);
            const y2 = cy + r * Math.sin(aFim);

            const largeArc = (anguloPorFatia > Math.PI) ? 1 : 0;
            const corFatia = (i < num) ? COR_PREENCHIMENTO : COR_VAZIO;

            paths += `<path d="M ${cx} ${cy} L ${x1.toFixed(2)} ${y1.toFixed(2)} A ${r} ${r} 0 ${largeArc} 1 ${x2.toFixed(2)} ${y2.toFixed(2)} Z" fill="${corFatia}" stroke="${COR_BORDA}" stroke-width="3.5" stroke-linejoin="round" />`;
        }

        // Círculo de contorno externo para garantir acabamento cartoon perfeito
        const contorno = `<circle cx="${cx}" cy="${cy}" r="${r}" fill="none" stroke="${COR_BORDA}" stroke-width="4.5" />`;

        return `
            <svg viewBox="0 0 200 200" class="w-full h-full drop-shadow-md">
                ${paths}
                ${contorno}
            </svg>
        `;
    }

    // ============================================================
    // GERAR DISTRATORES PLAUSÍVEIS
    // ============================================================
    function gerarOpcoes(correta) {
        const opcoes = [correta];
        const usados = new Set([`${correta.num}/${correta.den}`]);

        // Possíveis distratores interessantes:
        const candidatos = [
            { num: correta.den - correta.num, den: correta.den },               // complemento
            { num: correta.num, den: correta.den + 1 },                         // denominador + 1
            { num: correta.num + 1, den: correta.den },                         // numerador + 1
            { num: Math.max(1, correta.num - 1), den: correta.den },            // numerador - 1
            { num: correta.num, den: Math.max(correta.num + 1, correta.den - 1) },
            { num: 1, den: correta.den }
        ];

        // Tentar candidatos lógicos
        for (const cand of candidatos) {
            if (cand.num > 0 && cand.num < cand.den) {
                const chave = `${cand.num}/${cand.den}`;
                if (!usados.has(chave)) {
                    usados.add(chave);
                    opcoes.push(cand);
                    if (opcoes.length === 4) break;
                }
            }
        }

        // Se ainda faltar para 4, pegar do banco geral aleatoriamente
        while (opcoes.length < 4) {
            const random = FRACOES_BASE[Math.floor(Math.random() * FRACOES_BASE.length)];
            const chave = `${random.num}/${random.den}`;
            if (!usados.has(chave)) {
                usados.add(chave);
                opcoes.push(random);
            }
        }

        // Embaralhar Fisher-Yates
        for (let i = opcoes.length - 1; i > 0; i--) {
            const j = Math.floor(Math.random() * (i + 1));
            [opcoes[i], opcoes[j]] = [opcoes[j], opcoes[i]];
        }

        return opcoes;
    }

    // ============================================================
    // CARREGAR NOVA PERGUNTA
    // ============================================================
    function novaPergunta() {
        // Sortear uma fração diferente da atual se possível
        let sorteada = FRACOES_BASE[Math.floor(Math.random() * FRACOES_BASE.length)];
        if (fracAtual && sorteada.num === fracAtual.num && sorteada.den === fracAtual.den) {
            sorteada = FRACOES_BASE[(FRACOES_BASE.indexOf(sorteada) + 1) % FRACOES_BASE.length];
        }
        fracAtual = sorteada;

        // Renderizar Pizza
        containerPizza.innerHTML = criarSvgPizza(fracAtual.num, fracAtual.den);
        containerPizza.classList.remove('anim-correto');
        void containerPizza.offsetWidth; // trigger reflow
        containerPizza.classList.add('anim-correto');

        // Renderizar 4 Opções
        const opcoes = gerarOpcoes(fracAtual);
        gradeOpcoes.innerHTML = '';

        opcoes.forEach(op => {
            const ehCorreta = (op.num === fracAtual.num && op.den === fracAtual.den);
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'cartao-opcao bg-papel-branco text-tinta-escura borda-tinta-escura rounded-2xl py-3 px-4 font-gooddog text-3xl font-bold shadow-sombra-cartum-branca flex items-center justify-center cursor-pointer';
            btn.innerHTML = `${op.num} / ${op.den}`;

            btn.onclick = () => selecionarOpcao(btn, ehCorreta);
            gradeOpcoes.appendChild(btn);
        });

        bloqueado = false;
    }

    // ============================================================
    // PROCESSAR ESCOLHA
    // ============================================================
    function selecionarOpcao(btn, acertou) {
        if (bloqueado) return;
        bloqueado = true;

        if (acertou) {
            pontuacao++;
            elPontos.textContent = pontuacao;
            btn.classList.remove('bg-papel-branco');
            btn.classList.add('bg-verde-vibrante', 'text-white');

            // Reinicia o tempo para a próxima questão
            reiniciarCronometro();

            setTimeout(() => {
                novaPergunta();
            }, 500);
        } else {
            // Errou: pontuação volta pro 0 e fim de jogo
            btn.classList.remove('bg-papel-branco');
            btn.classList.add('bg-vermelho-vivo', 'text-white');

            setTimeout(() => {
                fimDeJogo();
            }, 600);
        }
    }

    // ============================================================
    // CRONÔMETRO DE 30 SEGUNDOS
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

            // A cada 10s decorridos perde 1 estrela (aos 20s, 10s, 0s)
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
        if (tempoRestante > 20)      elBoxCron.style.backgroundColor = '#00C853';
        else if (tempoRestante > 10) elBoxCron.style.backgroundColor = '#FFD600';
        else                         elBoxCron.style.backgroundColor = '#EF4444';
    }

    function atualizarEstrelas(animar = false) {
        elEstrelas.forEach((el, i) => {
            if (i < estrelasAtivas) {
                el.style.opacity = '1';
                el.style.filter = 'none';
                if (animar && i === estrelasAtivas) {
                    el.classList.add('estrela-pulsando');
                    el.addEventListener('animationend', () => el.classList.remove('estrela-pulsando'), { once: true });
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
        elFimPontos.textContent = pontuacao;
        overlayFim.classList.remove('hidden');
        overlayFim.classList.add('flex');
    }

    function reiniciarJogo() {
        overlayFim.classList.add('hidden');
        overlayFim.classList.remove('flex');
        pontuacao = 0;
        elPontos.textContent = 0;
        novaPergunta();
        iniciarCronometro();
    }

    btnReiniciar.addEventListener('click', reiniciarJogo);

    // Iniciar jogo ao carregar
    novaPergunta();
    iniciarCronometro();
    </script>

    <script src="js/acessibilidade.js"></script>
</body>
</html>
