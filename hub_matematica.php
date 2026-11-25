<?php
/**
 * hub_matematica.php — Hub de Matemática
 * Sketchbook Educacional — Ensino Fundamental 1
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/proteger.php';

$nomeUsuario   = $_SESSION['user'] ?? '';
$userId        = (int) ($_SESSION['id'] ?? 0);
$pontosUsuario = (int) ($_SESSION['pontuacao'] ?? 0);

if ($userId > 0) {
    $tbl_usuarios = db_table($conn, 'usuario');
    $stmt = $conn->prepare("SELECT pontuacao FROM {$tbl_usuarios} WHERE id = ?");
    if ($stmt) {
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($row = $res->fetch_assoc()) {
            $pontosUsuario = (int) $row['pontuacao'];
            $_SESSION['pontuacao'] = $pontosUsuario;
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Matemática — Sketchbook Educacional</title>
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><text y=%22.9em%22 font-size=%2290%22>➕</text></svg>">
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
                        'papel-linhas':       '#E2E8F0',
                        'papelao-fundo':      '#c6a175',
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
                        'roxo-vibrante':      '#7C3AED',
                        'roxo-escuro':        '#3B0764',
                        'ciano-vibrante':     '#06B6D4',
                        'ciano-escuro':       '#164E63',
                    },
                    fontFamily: {
                        'gooddog': ['Gooddog', 'cursive', 'sans-serif'],
                        'bingo':   ['Bingo', 'cursive', 'sans-serif'],
                        'fredoka': ['Fredoka', 'sans-serif'],
                        'nunito':  ['Nunito', 'sans-serif'],
                    },
                    boxShadow: {
                        'sombra-cartum-verde':   '0px 6px 0px #054D20',
                        'sombra-cartum-laranja': '0px 6px 0px #803700',
                        'sombra-cartum-azul':    '0px 6px 0px #004B87',
                        'sombra-cartum-amarela': '0px 6px 0px #806B00',
                        'sombra-cartum-rosa':    '0px 6px 0px #c0185b',
                        'sombra-cartum-roxo':    '0px 6px 0px #3B0764',
                        'sombra-cartum-ciano':   '0px 6px 0px #164E63',
                        'sombra-cartum-branca':  '0px 5px 0px #0F172A',
                    }
                }
            }
        }
    </script>
    <link rel="stylesheet" href="css/index_php.css">
    <link rel="stylesheet" href="css/acessibilidade.css">
</head>
<body class="font-bingo min-h-screen text-tinta-escura flex flex-col justify-between p-2 sm:p-4 md:p-6 relative">

    <div id="elementos-fundo-oscilantes" aria-hidden="true">
        <div class="elemento-fundo-oscilante elemento-fundo-1"></div>
        <div class="elemento-fundo-oscilante elemento-fundo-2"></div>
        <div class="elemento-fundo-oscilante elemento-fundo-3"></div>
        <div class="elemento-fundo-oscilante elemento-fundo-4"></div>
    </div>

    <div id="livro-conteiner-principal" class="w-full flex-1 flex flex-col justify-between caderno-pagina-branca rounded-3xl shadow-2xl p-4 sm:p-6 md:p-8 relative">

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

        <header id="cabecalho-principal" class="w-full flex flex-wrap items-center justify-between gap-3 pb-3 mb-4">
            <div class="flex items-center gap-2.5 flex-wrap">
                <a href="hub.php" title="Voltar ao Hub de Disciplinas"
                   class="cartao-dinamico flex items-center gap-2 bg-papel-claro borda-tinta-escura rounded-xl px-3.5 py-1.5 shadow-sombra-cartum-branca font-bingo font-bold text-xs sm:text-sm text-tinta-escura hover:bg-yellow-100 transition-colors no-underline">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
                        <path d="M19 12H5M12 5l-7 7 7 7"/>
                    </svg>
                    <span>HUB</span>
                </a>
                <span class="bg-papel-claro borda-tinta-escura rounded-xl px-3.5 py-1.5 font-bingo font-bold text-xs sm:text-sm text-tinta-escura shadow-sombra-cartum-branca">
                    ALUNO: <?= htmlspecialchars($nomeUsuario) ?>
                </span>
                <span class="bg-amarelo-vibrante borda-tinta-escura rounded-xl px-3.5 py-1.5 font-bingo font-bold text-xs sm:text-sm text-tinta-escura shadow-sombra-cartum-branca flex items-center gap-1.5">
                    <span class="font-gooddog text-xl text-tinta-escura font-bold leading-none"><?= $pontosUsuario ?></span> PTS
                </span>
            </div>
            <a href="logout.php" id="btn-logout" title="Encerrar Sessão"
               class="cartao-dinamico flex items-center gap-2 bg-red-500 hover:bg-red-600 text-white borda-tinta-escura rounded-xl px-4 py-2 font-bingo font-bold text-sm shadow-sombra-cartum-branca transition-all no-underline">
                <svg class="w-4 h-4 fill-none stroke-current" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15M12 9l-3 3m0 0l3 3m-3-3h12.75" />
                </svg>
                <span>LOGOUT</span>
            </a>
        </header>

        <main id="conteudo-principal" class="w-full my-auto flex flex-col items-center">

            <div id="secao-titulo-atividade" class="text-center max-w-4xl mb-6 space-y-2">
                <h2 class="font-gooddog text-5xl sm:text-6xl md:text-7xl lg:text-8xl font-bold text-tinta-escura leading-tight tracking-wider">
                    MATEMÁTICA
                </h2>
                <p class="font-bingo text-lg sm:text-xl md:text-2xl font-extrabold text-gray-700 tracking-wide">
                    Escolha um dos jogos abaixo para começar!
                </p>
            </div>

            <div id="grade-atividades" class="w-full max-w-5xl grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 sm:gap-8 px-2 py-4">

                <!-- CARD 1: CALCULANDO -->
                <a id="card-calculando"
                   href="jogo_calculando.php"
                   class="cartao-dinamico cartao-zigzag-cima cartao-inclinado-esquerda bg-laranja-vibrante text-white cartao-borda-laranja cartao-arredondado p-6 shadow-sombra-cartum-laranja flex flex-col justify-between min-h-[280px] cursor-pointer group no-underline">
                    <div class="flex items-center justify-between">
                        <span class="bg-papel-branco text-laranja-escuro font-bingo text-xs font-extrabold px-3 py-1 border-2 border-laranja-escuro rounded-xl">
                            JOGO 01
                        </span>
                        <div class="w-10 h-10 bg-white/25 border-2 border-laranja-escuro rounded-xl flex items-center justify-center font-gooddog font-bold text-lg text-white shrink-0">
                            ✖
                        </div>
                    </div>
                    <div class="my-3">
                        <h3 class="font-gooddog text-3xl sm:text-4xl font-bold leading-tight uppercase text-white tracking-wider">
                            CALCULANDO
                        </h3>
                        <p class="font-bingo text-xs sm:text-sm font-extrabold text-orange-100 mt-1 tracking-wide leading-snug">
                            Resolva contas de +, −, × e ÷ contra o relógio!
                        </p>
                    </div>
                    <div class="w-full bg-papel-branco text-laranja-escuro font-bingo font-extrabold text-sm py-2.5 px-3 border-2 border-laranja-escuro rounded-2xl text-center flex items-center justify-center gap-2 group-hover:bg-amarelo-vibrante group-hover:text-tinta-escura transition-colors">
                        <span>JOGAR</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="3.5" viewBox="0 0 24 24">
                            <path d="M5 12h14M12 5l7 7-7 7"/>
                        </svg>
                    </div>
                </a>

                <!-- CARD 2: FRACIONANDO -->
                <a id="card-fracionando"
                   href="jogo_fracionando.php"
                   class="cartao-dinamico cartao-zigzag-baixo cartao-inclinado-direita bg-azul-vibrante text-white cartao-borda-azul cartao-arredondado p-6 shadow-sombra-cartum-azul flex flex-col justify-between min-h-[280px] cursor-pointer group no-underline">
                    <div class="flex items-center justify-between">
                        <span class="bg-papel-branco text-azul-escuro font-bingo text-xs font-extrabold px-3 py-1 border-2 border-azul-escuro rounded-xl">
                            JOGO 02
                        </span>
                        <div class="w-10 h-10 bg-white/25 border-2 border-azul-escuro rounded-xl flex items-center justify-center font-gooddog font-bold text-lg text-white shrink-0">
                            ½
                        </div>
                    </div>
                    <div class="my-3">
                        <h3 class="font-gooddog text-3xl sm:text-4xl font-bold leading-tight uppercase text-white tracking-wider">
                            FRACIONANDO
                        </h3>
                        <p class="font-bingo text-xs sm:text-sm font-extrabold text-blue-100 mt-1 tracking-wide leading-snug">
                            Leia o gráfico de pizza e identifique a fração!
                        </p>
                    </div>
                    <div class="w-full bg-papel-branco text-azul-escuro font-bingo font-extrabold text-sm py-2.5 px-3 border-2 border-azul-escuro rounded-2xl text-center flex items-center justify-center gap-2 group-hover:bg-amarelo-vibrante group-hover:text-tinta-escura transition-colors">
                        <span>JOGAR</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="3.5" viewBox="0 0 24 24">
                            <path d="M5 12h14M12 5l7 7-7 7"/>
                        </svg>
                    </div>
                </a>

                <!-- CARD 3: TROCO -->
                <a id="card-troco"
                   href="jogo_troco.php"
                   class="cartao-dinamico cartao-zigzag-cima cartao-inclinado-leve-esquerda bg-verde-vibrante text-white cartao-borda-verde cartao-arredondado p-6 shadow-sombra-cartum-verde flex flex-col justify-between min-h-[280px] cursor-pointer group no-underline">
                    <div class="flex items-center justify-between">
                        <span class="bg-papel-branco text-verde-escuro font-bingo text-xs font-extrabold px-3 py-1 border-2 border-verde-escuro rounded-xl">
                            JOGO 03
                        </span>
                        <div class="w-10 h-10 bg-white/25 border-2 border-verde-escuro rounded-xl flex items-center justify-center font-gooddog font-bold text-lg text-white shrink-0">
                            💵
                        </div>
                    </div>
                    <div class="my-3">
                        <h3 class="font-gooddog text-3xl sm:text-4xl font-bold leading-tight uppercase text-white tracking-wider">
                            JOGO DO TROCO
                        </h3>
                        <p class="font-bingo text-xs sm:text-sm font-extrabold text-green-100 mt-1 tracking-wide leading-snug">
                            Calcule o troco correto em reais e centavos!
                        </p>
                    </div>
                    <div class="w-full bg-papel-branco text-verde-escuro font-bingo font-extrabold text-sm py-2.5 px-3 border-2 border-verde-escuro rounded-2xl text-center flex items-center justify-center gap-2 group-hover:bg-amarelo-vibrante group-hover:text-tinta-escura transition-colors">
                        <span>JOGAR</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="3.5" viewBox="0 0 24 24">
                            <path d="M5 12h14M12 5l7 7-7 7"/>
                        </svg>
                    </div>
                </a>

            </div>

        </main>

    </div>

    <script src="js/acessibilidade.js"></script>
</body>
</html>
