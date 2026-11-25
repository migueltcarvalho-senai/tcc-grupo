<?php
/**
 * jogo_troco.php — Jogo do Troco
 * Sketchbook Educacional — Ensino Fundamental 1
 *
 * Baseado no layout de referência MathPlay (exemplos-layout/MathPlay.png)
 * - Fundo branco e barra superior com botão "Sair do Jogo" (#ff914d)
 * - Retângulo laranja (#ff914d) com:
 *     "QUAL O VALOR DO TROCO SE EU PAGAR R$(valor pago)"
 *     Caixa branca: "VALOR TOTAL: R$(valor total)"
 * - Tabela cinza (#efefef) com 3 caixas: REAIS, CENTAVOS e TOTAL (soma automática)
 * - Botões laterais:
 *     Botão #c1cf35 (Confirmar / Enviar)
 *     Botão #f22373 (X / Apagar última ação)
 * - Indicador de tempo: "TEMPO: x SEGUNDOS", barra de progresso e 3 estrelas (icones/estrela.svg)
 * - Informações no canto da tela: Acertos e Estrelas conquistadas
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
    <title>Jogo do Troco — Sketchbook Educacional</title>
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><text y=%22.9em%22 font-size=%2290%22>💵</text></svg>">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fredoka:wght@500;600;700&family=Nunito:wght@700;800;900&display=swap" rel="stylesheet">

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'troco-laranja': '#ff914d',
                        'troco-rosa':    '#f22373',
                        'troco-verde':   '#c1cf35',
                        'troco-cinza':   '#efefef',
                        'tinta-escura':  '#0F172A',
                    },
                    fontFamily: {
                        'gooddog': ['Gooddog', 'cursive', 'sans-serif'],
                        'bingo':   ['Bingo', 'cursive', 'sans-serif'],
                        'fredoka': ['Fredoka', 'sans-serif'],
                        'nunito':  ['Nunito', 'sans-serif'],
                    }
                }
            }
        }
    </script>
    <link rel="stylesheet" href="css/index_php.css">
    <link rel="stylesheet" href="css/acessibilidade.css">

    <style>
        body {
            background-color: #FFFFFF !important;
        }

        /* Campo de digitação estilizado */
        .campo-dinheiro {
            transition: all 0.15s ease;
            outline: none;
        }
        .campo-dinheiro:focus, .campo-dinheiro.focado {
            box-shadow: 0 0 0 4px #ff914d, 0 4px 12px rgba(255, 145, 77, 0.25);
            border-color: #ff914d;
        }

        /* Botões de ação */
        .btn-acao {
            transition: transform 0.1s ease, box-shadow 0.1s ease;
            user-select: none;
        }
        .btn-acao:hover {
            transform: translateY(-2px);
            filter: brightness(1.05);
        }
        .btn-acao:active {
            transform: translateY(2px);
            filter: brightness(0.95);
        }

        /* Barra de tempo */
        #barra-tempo-preenchimento {
            transition: width 1s linear;
        }

        @keyframes pulsar-estrela {
            0%, 100% { opacity: 1; transform: scale(1); }
            50%       { opacity: 0.3; transform: scale(0.8); }
        }
        .estrela-pulsando {
            animation: pulsar-estrela 0.6s ease-in-out 3;
        }

        @keyframes tremor-erro {
            0%, 100% { transform: translateX(0); }
            20%, 60% { transform: translateX(-8px); }
            40%, 80% { transform: translateX(8px); }
        }
        .anim-erro {
            animation: tremor-erro 0.4s ease;
        }

        @keyframes salto-acerto {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.03); }
        }
        .anim-acerto {
            animation: salto-acerto 0.4s ease;
        }

        #overlay-fim {
            backdrop-filter: blur(4px);
        }
    </style>
</head>
<body class="font-bingo min-h-screen text-tinta-escura flex flex-col justify-between bg-white select-none">

    <!-- BARRA SUPERIOR: APENAS BOTÃO "SAIR DO JOGO" NO LADO DIREITO -->
    <header class="w-full bg-[#f22373] px-4 sm:px-8 py-3 flex items-center justify-between shadow-md">
        <div class="flex items-center gap-3">
            <span class="font-gooddog text-2xl sm:text-3xl font-bold text-white tracking-wider">
                MathPlay
            </span>
        </div>
        <a href="hub_matematica.php"
           class="bg-[#ff914d] hover:bg-[#ff7b2b] text-white font-bingo font-bold text-sm sm:text-base px-6 py-2 rounded-full shadow-md transition-all no-underline flex items-center gap-2">
            <span>Sair do jogo</span>
        </a>
    </header>

    <!-- ÁREA PRINCIPAL -->
    <main class="w-full flex-1 max-w-5xl mx-auto px-4 py-4 flex flex-col justify-between items-center relative">

        <!-- INFORMAÇÕES NO CANTO SUPERIOR (Acertos e Estrelas acumuladas) -->
        <div class="w-full flex items-center justify-between gap-3 mb-2 px-2">
            <div class="flex items-center gap-2">
                <div class="bg-gray-100 border-2 border-gray-200 rounded-2xl px-4 py-1.5 flex items-center gap-2 shadow-sm">
                    <span class="text-xs uppercase font-extrabold text-gray-500">Acertos:</span>
                    <span id="placar-acertos" class="font-gooddog text-2xl font-bold text-[#ff914d]">0</span>
                </div>
                <div class="bg-gray-100 border-2 border-gray-200 rounded-2xl px-4 py-1.5 flex items-center gap-2 shadow-sm">
                    <span class="text-xs uppercase font-extrabold text-gray-500">Estrelas:</span>
                    <span id="placar-estrelas" class="font-gooddog text-2xl font-bold text-amber-500">0</span>
                    <img src="icones/estrela.svg" alt="Estrela" class="w-5 h-5">
                </div>
            </div>

            <div class="hidden sm:block text-xs font-bold text-gray-400">
                Aluno: <?= htmlspecialchars($nomeUsuario) ?>
            </div>
        </div>

        <!-- CONTAINER CENTRAL DO JOGO -->
        <div id="container-cartao-jogo" class="w-full max-w-3xl flex flex-col items-center gap-5 my-auto">

            <!-- RETÂNGULO LARANJA (#ff914d) COM BORDAS ARREDONDADAS -->
            <div class="w-full bg-[#ff914d] rounded-[2.5rem] p-5 sm:p-7 shadow-lg flex flex-col items-center gap-4">
                
                <!-- PARTE DE CIMA: "QUAL O VALOR DO TROCO SE EU PAGAR R$(valor pago)" -->
                <h1 id="texto-pergunta-pago" class="font-bingo text-xl sm:text-2xl md:text-3xl font-extrabold text-white text-center tracking-wide leading-snug uppercase">
                    QUAL O VALOR DO TROCO SE EU PAGAR R$50,00
                </h1>

                <!-- RETÂNGULO BRANCO CENTRALIZADO: "VALOR TOTAL: R$(valor total)" -->
                <div class="w-full bg-white rounded-2xl sm:rounded-3xl py-4 sm:py-5 px-6 shadow-sm flex items-center justify-center">
                    <p id="texto-valor-total" class="font-bingo text-3xl sm:text-4xl md:text-5xl font-black text-black tracking-tight text-center">
                        VALOR TOTAL: 42,75
                    </p>
                </div>

            </div>

            <!-- TABELA CINZA (#efefef) + OS 2 BOTÕES LATERAIS (CHECK & X) -->
            <div class="w-full flex items-center gap-3 sm:gap-4 justify-center">

                <!-- TABELA CINZA COM AS 3 DIVISÓRIAS -->
                <div class="flex-1 bg-[#efefef] rounded-3xl p-4 sm:p-6 grid grid-cols-3 gap-3 sm:gap-5 shadow-sm">

                    <!-- 1ª DIVISÓRIA: REAIS -->
                    <div class="flex flex-col items-center">
                        <label for="input-reais" class="font-bingo text-sm sm:text-lg md:text-xl font-black text-black tracking-wider uppercase mb-1.5 sm:mb-2 cursor-pointer">
                            REAIS:
                        </label>
                        <div class="w-full aspect-[4/3] bg-white rounded-2xl sm:rounded-3xl border-2 border-transparent shadow-inner flex items-center justify-center p-2 campo-container cursor-text"
                             onclick="focarCampo('reais')">
                            <input type="text"
                                   id="input-reais"
                                   inputmode="numeric"
                                   pattern="[0-9]*"
                                   maxlength="5"
                                   placeholder="0"
                                   class="campo-dinheiro w-full h-full text-center font-gooddog text-3xl sm:text-4xl md:text-5xl font-bold text-black bg-transparent border-0 outline-none"
                                   onfocus="definirFoco('reais')"
                                   oninput="aoDigitarReais(this)" />
                        </div>
                    </div>

                    <!-- 2ª DIVISÓRIA: CENTAVOS -->
                    <div class="flex flex-col items-center">
                        <label for="input-centavos" class="font-bingo text-sm sm:text-lg md:text-xl font-black text-black tracking-wider uppercase mb-1.5 sm:mb-2 cursor-pointer">
                            CENTAVOS:
                        </label>
                        <div class="w-full aspect-[4/3] bg-white rounded-2xl sm:rounded-3xl border-2 border-transparent shadow-inner flex items-center justify-center p-2 campo-container cursor-text"
                             onclick="focarCampo('centavos')">
                            <input type="text"
                                   id="input-centavos"
                                   inputmode="numeric"
                                   pattern="[0-9]*"
                                   maxlength="2"
                                   placeholder="00"
                                   class="campo-dinheiro w-full h-full text-center font-gooddog text-3xl sm:text-4xl md:text-5xl font-bold text-black bg-transparent border-0 outline-none"
                                   onfocus="definirFoco('centavos')"
                                   oninput="aoDigitarCentavos(this)" />
                        </div>
                    </div>

                    <!-- 3ª DIVISÓRIA: TOTAL (SOMA DOS DOIS) -->
                    <div class="flex flex-col items-center">
                        <span class="font-bingo text-sm sm:text-lg md:text-xl font-black text-black tracking-wider uppercase mb-1.5 sm:mb-2">
                            TOTAL:
                        </span>
                        <div class="w-full aspect-[4/3] bg-white rounded-2xl sm:rounded-3xl shadow-inner flex items-center justify-center p-2">
                            <span id="display-total-troco" class="font-gooddog text-2xl sm:text-3xl md:text-4xl font-bold text-black text-center truncate px-1">
                                R$ 0,00
                            </span>
                        </div>
                    </div>

                </div>

                <!-- 2 BOTÕES EMPILHADOS: CERTO (#c1cf35) E X (#f22373) -->
                <div class="flex flex-col gap-3 sm:gap-4 shrink-0">

                    <!-- BOTÃO CERTO (#c1cf35) - CONFIRMAR/ENVIAR -->
                    <button type="button"
                            id="btn-confirmar"
                            onclick="conferirResposta()"
                            title="Confirmar Resposta"
                            class="btn-acao w-16 h-16 sm:w-20 sm:h-20 md:w-24 md:h-24 bg-[#c1cf35] rounded-3xl flex items-center justify-center shadow-md cursor-pointer border-0">
                        <div class="w-10 h-10 sm:w-12 sm:h-12 md:w-14 md:h-14 rounded-full border-[3.5px] border-white flex items-center justify-center">
                            <svg class="w-6 h-6 sm:w-7 sm:h-7 md:w-8 md:h-8 text-white stroke-current" fill="none" stroke-width="4.5" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                                <path d="M5 13l4 4L19 7"/>
                            </svg>
                        </div>
                    </button>

                    <!-- BOTÃO X (#f22373) - APAGAR ÚLTIMA AÇÃO -->
                    <button type="button"
                            id="btn-apagar"
                            onclick="apagarUltimaAcao()"
                            title="Apagar Último Dígito"
                            class="btn-acao w-16 h-16 sm:w-20 sm:h-20 md:w-24 md:h-24 bg-[#f22373] rounded-3xl flex items-center justify-center shadow-md cursor-pointer border-0">
                        <svg class="w-10 h-10 sm:w-12 sm:h-12 md:w-14 md:h-14 text-white fill-current" viewBox="0 0 24 24">
                            <path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/>
                        </svg>
                    </button>

                </div>

            </div>

            <!-- TECLADO NUMÉRICO VIRTUAL (ACESSÍVEL PARA TOQUE/MOUSE) -->
            <div id="teclado-virtual" class="w-full max-w-sm flex flex-wrap justify-center gap-2 pt-1">
                <button type="button" onclick="digitarVirtual('1')" class="btn-acao w-10 h-10 sm:w-12 sm:h-12 bg-gray-100 hover:bg-gray-200 border border-gray-300 rounded-xl font-gooddog text-xl font-bold text-gray-800 shadow-sm">1</button>
                <button type="button" onclick="digitarVirtual('2')" class="btn-acao w-10 h-10 sm:w-12 sm:h-12 bg-gray-100 hover:bg-gray-200 border border-gray-300 rounded-xl font-gooddog text-xl font-bold text-gray-800 shadow-sm">2</button>
                <button type="button" onclick="digitarVirtual('3')" class="btn-acao w-10 h-10 sm:w-12 sm:h-12 bg-gray-100 hover:bg-gray-200 border border-gray-300 rounded-xl font-gooddog text-xl font-bold text-gray-800 shadow-sm">3</button>
                <button type="button" onclick="digitarVirtual('4')" class="btn-acao w-10 h-10 sm:w-12 sm:h-12 bg-gray-100 hover:bg-gray-200 border border-gray-300 rounded-xl font-gooddog text-xl font-bold text-gray-800 shadow-sm">4</button>
                <button type="button" onclick="digitarVirtual('5')" class="btn-acao w-10 h-10 sm:w-12 sm:h-12 bg-gray-100 hover:bg-gray-200 border border-gray-300 rounded-xl font-gooddog text-xl font-bold text-gray-800 shadow-sm">5</button>
                <button type="button" onclick="digitarVirtual('6')" class="btn-acao w-10 h-10 sm:w-12 sm:h-12 bg-gray-100 hover:bg-gray-200 border border-gray-300 rounded-xl font-gooddog text-xl font-bold text-gray-800 shadow-sm">6</button>
                <button type="button" onclick="digitarVirtual('7')" class="btn-acao w-10 h-10 sm:w-12 sm:h-12 bg-gray-100 hover:bg-gray-200 border border-gray-300 rounded-xl font-gooddog text-xl font-bold text-gray-800 shadow-sm">7</button>
                <button type="button" onclick="digitarVirtual('8')" class="btn-acao w-10 h-10 sm:w-12 sm:h-12 bg-gray-100 hover:bg-gray-200 border border-gray-300 rounded-xl font-gooddog text-xl font-bold text-gray-800 shadow-sm">8</button>
                <button type="button" onclick="digitarVirtual('9')" class="btn-acao w-10 h-10 sm:w-12 sm:h-12 bg-gray-100 hover:bg-gray-200 border border-gray-300 rounded-xl font-gooddog text-xl font-bold text-gray-800 shadow-sm">9</button>
                <button type="button" onclick="digitarVirtual('0')" class="btn-acao w-10 h-10 sm:w-12 sm:h-12 bg-gray-100 hover:bg-gray-200 border border-gray-300 rounded-xl font-gooddog text-xl font-bold text-gray-800 shadow-sm">0</button>
                <button type="button" onclick="alternarCampo()" class="btn-acao px-3 h-10 sm:h-12 bg-gray-200 hover:bg-gray-300 border border-gray-300 rounded-xl font-bingo text-xs font-bold text-gray-700 shadow-sm flex items-center justify-center">
                    MUDAR CAMPO ⇄
                </button>
            </div>

            <!-- ÁREA DO TEMPO E ESTRELAS -->
            <div class="w-full max-w-md flex flex-col items-center gap-2 pt-2">
                
                <!-- TEMPO RESTANTE -->
                <p id="label-tempo-texto" class="font-bingo text-sm sm:text-base font-black text-black uppercase tracking-wider text-center">
                    TEMPO: 30 SEGUNDOS
                </p>

                <!-- BARRA DE TEMPO (TRILHA MAGENTA COM BARRA AMARELA) -->
                <div class="w-full h-5 sm:h-6 bg-[#d81b60] rounded-full p-1 shadow-inner overflow-hidden flex items-center">
                    <div id="barra-tempo-preenchimento" class="h-full bg-[#ffcf4a] rounded-full w-full"></div>
                </div>

                <!-- 3 ESTRELINHAS (icones/estrela.svg) -->
                <div id="estrelas-container" class="flex justify-center items-center gap-2 pt-1">
                    <img id="estrela-1" src="icones/estrela.svg" alt="Estrela 1" class="w-7 h-7 sm:w-8 sm:h-8 transition-all duration-300 drop-shadow-sm">
                    <img id="estrela-2" src="icones/estrela.svg" alt="Estrela 2" class="w-7 h-7 sm:w-8 sm:h-8 transition-all duration-300 drop-shadow-sm">
                    <img id="estrela-3" src="icones/estrela.svg" alt="Estrela 3" class="w-7 h-7 sm:w-8 sm:h-8 transition-all duration-300 drop-shadow-sm">
                </div>

            </div>

        </div>

    </main>

    <!-- OVERLAY FIM DE JOGO -->
    <div id="overlay-fim" class="fixed inset-0 bg-black/60 z-50 hidden flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl p-6 sm:p-8 max-w-sm w-full text-center shadow-2xl flex flex-col gap-5 border-4 border-gray-100">
            <div class="text-6xl">🪙</div>
            <h2 class="font-bingo text-3xl sm:text-4xl font-black text-black tracking-wide">FIM DE JOGO!</h2>
            <div class="bg-gray-50 rounded-2xl p-4 flex flex-col gap-2">
                <p class="font-bingo text-base font-extrabold text-gray-700">
                    Acertos: <span id="fim-acertos" class="text-[#ff914d] font-gooddog text-3xl font-bold">0</span>
                </p>
                <p class="font-bingo text-base font-extrabold text-gray-700 flex items-center justify-center gap-1.5">
                    Estrelas ganhas: <span id="fim-estrelas" class="text-amber-500 font-gooddog text-3xl font-bold">0</span>
                    <img src="icones/estrela.svg" alt="Estrela" class="w-6 h-6">
                </p>
            </div>
            <div class="flex flex-col sm:flex-row gap-3 pt-2">
                <button type="button"
                        id="btn-reiniciar"
                        onclick="reiniciarJogo()"
                        class="flex-1 bg-[#c1cf35] hover:bg-[#b2c028] text-white rounded-2xl px-5 py-3 font-bingo text-lg font-bold shadow-md transition-colors cursor-pointer border-0">
                    REINICIAR
                </button>
                <a href="hub_matematica.php"
                   class="flex-1 bg-[#ff914d] hover:bg-[#ff7b2b] text-white rounded-2xl px-5 py-3 font-bingo text-lg font-bold shadow-md transition-colors no-underline text-center">
                    INÍCIO
                </a>
            </div>
        </div>
    </div>

    <script>
    // ============================================================
    // POOL DE CASOS DE TROCO
    // ============================================================
    const CASOS_TROCO = [
        { pago: 50.00, total: 42.75 },
        { pago: 20.00, total: 13.50 },
        { pago: 10.00, total: 4.80  },
        { pago: 50.00, total: 27.45 },
        { pago: 100.00, total: 68.30 },
        { pago: 20.00, total: 11.20 },
        { pago: 5.00,  total: 2.65  },
        { pago: 50.00, total: 34.90 },
        { pago: 10.00, total: 7.15  },
        { pago: 100.00, total: 84.50 },
        { pago: 20.00, total: 8.75  },
        { pago: 50.00, total: 19.60 },
        { pago: 10.00, total: 3.25  },
        { pago: 5.00,  total: 1.40  },
        { pago: 20.00, total: 16.85 }
    ];

    // ============================================================
    // ESTADO DO JOGO
    // ============================================================
    let rodadaAtual     = null;
    let acertosTotal    = 0;
    let estrelasTotal   = 0;
    let estrelasAtivas  = 3;
    let tempoRestante   = 30;
    let cronometroId    = null;
    let campoAtivo      = 'reais'; // 'reais' ou 'centavos'
    let bloqueado       = false;

    // Elementos DOM
    const elTextoPago      = document.getElementById('texto-pergunta-pago');
    const elTextoTotal     = document.getElementById('texto-valor-total');
    const inputReais       = document.getElementById('input-reais');
    const inputCentavos    = document.getElementById('input-centavos');
    const displayTotal     = document.getElementById('display-total-troco');
    const elPlacarAcertos  = document.getElementById('placar-acertos');
    const elPlacarEstrelas = document.getElementById('placar-estrelas');
    const elTempoTexto     = document.getElementById('label-tempo-texto');
    const barraPreenchimento = document.getElementById('barra-tempo-preenchimento');
    const overlayFim       = document.getElementById('overlay-fim');
    const elFimAcertos     = document.getElementById('fim-acertos');
    const elFimEstrelas    = document.getElementById('fim-estrelas');
    const containerJogo    = document.getElementById('container-cartao-jogo');

    const elEstrelas = [
        document.getElementById('estrela-1'),
        document.getElementById('estrela-2'),
        document.getElementById('estrela-3')
    ];

    // Formatar moeda brasileira
    function formatarMoeda(val) {
        return val.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    // ============================================================
    // NOVA RODADA
    // ============================================================
    function novaRodada() {
        // Sorteia caso de troco diferente
        let caso;
        do {
            caso = CASOS_TROCO[Math.floor(Math.random() * CASOS_TROCO.length)];
        } while (rodadaAtual && caso.pago === rodadaAtual.pago && caso.total === rodadaAtual.total);

        rodadaAtual = caso;

        // Troco esperado em centavos inteiros para evitar imprecisão de float
        const pagoCentavos  = Math.round(rodadaAtual.pago * 100);
        const totalCentavos = Math.round(rodadaAtual.total * 100);
        const trocoCentavos = pagoCentavos - totalCentavos;

        rodadaAtual.trocoReaisEsperado    = Math.floor(trocoCentavos / 100);
        rodadaAtual.trocoCentavosEsperado = trocoCentavos % 100;

        // Atualiza textos na tela
        elTextoPago.textContent = `QUAL O VALOR DO TROCO SE EU PAGAR R$${formatarMoeda(rodadaAtual.pago)}`;
        elTextoTotal.textContent = `VALOR TOTAL: ${formatarMoeda(rodadaAtual.total)}`;

        // Limpa campos
        inputReais.value = '';
        inputCentavos.value = '';
        atualizarDisplayTotal();

        campoAtivo = 'reais';
        focarCampo('reais');
        bloqueado = false;
    }

    // ============================================================
    // INTERAÇÃO E FOCO
    // ============================================================
    function focarCampo(campo) {
        campoAtivo = campo;
        if (campo === 'reais') {
            inputReais.focus();
            inputReais.parentElement.classList.add('focado');
            inputCentavos.parentElement.classList.remove('focado');
        } else {
            inputCentavos.focus();
            inputCentavos.parentElement.classList.add('focado');
            inputReais.parentElement.classList.remove('focado');
        }
    }

    function definirFoco(campo) {
        campoAtivo = campo;
        if (campo === 'reais') {
            inputReais.parentElement.classList.add('focado');
            inputCentavos.parentElement.classList.remove('focado');
        } else {
            inputCentavos.parentElement.classList.add('focado');
            inputReais.parentElement.classList.remove('focado');
        }
    }

    function alternarCampo() {
        if (campoAtivo === 'reais') {
            focarCampo('centavos');
        } else {
            focarCampo('reais');
        }
    }

    function aoDigitarReais(input) {
        input.value = input.value.replace(/\D/g, '');
        atualizarDisplayTotal();
    }

    function aoDigitarCentavos(input) {
        input.value = input.value.replace(/\D/g, '');
        if (input.value.length > 2) input.value = input.value.slice(0, 2);
        atualizarDisplayTotal();
    }

    function atualizarDisplayTotal() {
        const r = parseInt(inputReais.value, 10) || 0;
        let cStr = inputCentavos.value.trim();
        let c = 0;
        if (cStr.length === 1) {
            c = parseInt(cStr, 10) * 10;
        } else if (cStr.length >= 2) {
            c = parseInt(cStr.slice(0, 2), 10);
        }

        const totalDigitado = r + (c / 100);
        displayTotal.textContent = `R$ ${formatarMoeda(totalDigitado)}`;
    }

    function digitarVirtual(digito) {
        if (bloqueado) return;
        const input = (campoAtivo === 'reais') ? inputReais : inputCentavos;
        const maxLen = (campoAtivo === 'reais') ? 4 : 2;

        if (input.value.length < maxLen) {
            input.value += digito;
            atualizarDisplayTotal();
        }

        // Se preencheu 2 dígitos em centavos, pode manter o foco
    }

    // Botão X: apaga a última edição feita
    function apagarUltimaAcao() {
        if (bloqueado) return;
        const input = (campoAtivo === 'reais') ? inputReais : inputCentavos;

        if (input.value.length > 0) {
            input.value = input.value.slice(0, -1);
        } else if (campoAtivo === 'centavos' && inputCentavos.value.length === 0) {
            // Se centavos está vazio, volta para reais e apaga lá
            campoAtivo = 'reais';
            focarCampo('reais');
            inputReais.value = inputReais.value.slice(0, -1);
        }
        atualizarDisplayTotal();
    }

    // Suporte ao teclado físico
    document.addEventListener('keydown', (e) => {
        if (bloqueado) return;
        if (e.key === 'Enter') {
            e.preventDefault();
            conferirResposta();
        } else if (e.key === 'Tab' || e.key === ',' || e.key === '.') {
            e.preventDefault();
            alternarCampo();
        } else if (e.key === 'Backspace' && document.activeElement !== inputReais && document.activeElement !== inputCentavos) {
            apagarUltimaAcao();
        }
    });

    // ============================================================
    // CONFERÊNCIA DA RESPOSTA
    // ============================================================
    function conferirResposta() {
        if (bloqueado || !rodadaAtual) return;
        bloqueado = true;

        const reaisDigitados = parseInt(inputReais.value, 10) || 0;
        let cStr = inputCentavos.value.trim();
        let centavosDigitados = 0;
        if (cStr.length === 1) {
            centavosDigitados = parseInt(cStr, 10) * 10;
        } else if (cStr.length >= 2) {
            centavosDigitados = parseInt(cStr.slice(0, 2), 10);
        }

        const acertou = (reaisDigitados === rodadaAtual.trocoReaisEsperado && centavosDigitados === rodadaAtual.trocoCentavosEsperado);

        if (acertou) {
            acertosTotal++;
            estrelasTotal += estrelasAtivas;
            elPlacarAcertos.textContent = acertosTotal;
            elPlacarEstrelas.textContent = estrelasTotal;

            containerJogo.classList.add('anim-acerto');
            setTimeout(() => containerJogo.classList.remove('anim-acerto'), 450);

            reiniciarCronometro();

            setTimeout(() => {
                novaRodada();
            }, 500);
        } else {
            containerJogo.classList.add('anim-erro');
            setTimeout(() => containerJogo.classList.remove('anim-erro'), 500);

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
        atualizarBarraTempo();

        cronometroId = setInterval(() => {
            tempoRestante--;
            atualizarBarraTempo();

            // A cada 10s perde 1 estrela (aos 20s perde 1, aos 10s perde outra, aos 0s perde a última)
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
        atualizarBarraTempo();
        iniciarCronometro();
    }

    function atualizarBarraTempo() {
        elTempoTexto.textContent = `TEMPO: ${tempoRestante} SEGUNDOS`;
        const pct = Math.max(0, Math.min(100, (tempoRestante / 30) * 100));
        barraPreenchimento.style.width = pct + '%';

        // Muda cor de urgência se tempo for menor que 10s
        if (tempoRestante <= 10) {
            barraPreenchimento.style.backgroundColor = '#f22373';
        } else if (tempoRestante <= 20) {
            barraPreenchimento.style.backgroundColor = '#ff914d';
        } else {
            barraPreenchimento.style.backgroundColor = '#ffcf4a';
        }
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
        elFimAcertos.textContent = acertosTotal;
        elFimEstrelas.textContent = estrelasTotal;
        overlayFim.classList.remove('hidden');
        overlayFim.classList.add('flex');
    }

    function reiniciarJogo() {
        overlayFim.classList.add('hidden');
        overlayFim.classList.remove('flex');
        acertosTotal = 0;
        estrelasTotal = 0;
        elPlacarAcertos.textContent = 0;
        elPlacarEstrelas.textContent = 0;
        novaRodada();
        iniciarCronometro();
    }

    // Inicialização
    novaRodada();
    iniciarCronometro();
    </script>

    <script src="js/acessibilidade.js"></script>
</body>
</html>
