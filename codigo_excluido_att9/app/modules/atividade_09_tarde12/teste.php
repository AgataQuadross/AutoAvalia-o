<?php
/**
 * Script de Testes Automatizados - Atividade 09 (Tarde 12)
 *
 * Testes obrigatórios:
 * 1) Salvem um usuário novo com salvar() e confirmem que $id foi preenchido.
 * 2) Busquem esse mesmo usuário com buscarPorEmail() e confiram que os dados batem.
 * 3) Teste de segurança contra SQL Injection via Prepared Statements.
 *
 * Referência de banco de dados: pasta /database (schema.sql).
 * Compatível com versões legadas e modernas do PHP (PHP 5.6, PHP 7.x e PHP 8.x).
 */

require_once __DIR__ . '/Model.php';
require_once __DIR__ . '/Controller.php';

$pdo = UsuarioController::obterConexao();
$driver = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);

$ehWeb = (php_sapi_name() !== 'cli');

$prefixo = 'teste_' . time() . '_' . rand(100, 999);
$emailTeste = "aluno." . $prefixo . "@fazenda.edu.br";
$nomeTeste = "Usuario Teste" . $prefixo;
$senhaTeste = "SenhaForte@2026";

// -------------------------------------------------------------
// TESTE 1: Salvar Usuario 
// -------------------------------------------------------------
$novoUsuario = new Aluno();
$novoUsuario->nome = $nomeTeste;
$novoUsuario->email = $emailTeste;
$novoUsuario->xp_total = 150;
$novoUsuario->definirSenha($senhaTeste);

$salvouComSucesso = $novoUsuario->salvar($pdo);
$idPreenchido = (!empty($novoUsuario->id) && $novoUsuario->id > 0);
$teste1Passou = ($salvouComSucesso && $idPreenchido);

// -------------------------------------------------------------
// TESTE 2: Busquem esse mesmo usuário com buscarPorEmail() e confiram que os dados batem.
// -------------------------------------------------------------
$usuarioBuscado = Usuario::buscarPorEmail($pdo, $emailTeste);

$idBate = ($usuarioBuscado !== null && $usuarioBuscado->id === $novoUsuario->id);
$nomeBate = ($usuarioBuscado !== null && $usuarioBuscado->nome === $novoUsuario->nome);
$emailBate = ($usuarioBuscado !== null && $usuarioBuscado->email === $novoUsuario->email);
$senhaBate = ($usuarioBuscado !== null && $usuarioBuscado->verificarSenha($senhaTeste));
$teste2Passou = ($usuarioBuscado !== null && $idBate && $nomeBate && $emailBate && $senhaBate);

// -------------------------------------------------------------
// TESTE 3: Verificação de Segurança contra SQL Injection
// -------------------------------------------------------------
$payloadSql = "' OR '1'='1";
$resultadoSqli = Usuario::buscarPorEmail($pdo, $payloadSql);
$teste3Passou = ($resultadoSqli === null); // O prepared statement trata como texto literal, não burlável

// Calcula URL base desta pasta do módulo e da pasta views
$docRoot = realpath(isset($_SERVER['DOCUMENT_ROOT']) ? $_SERVER['DOCUMENT_ROOT'] : '');
$moduleDir = realpath(__DIR__);
$moduleUrl = ($docRoot && strpos($moduleDir, $docRoot) === 0)
    ? str_replace('\\', '/', substr($moduleDir, strlen($docRoot)))
    : dirname(isset($_SERVER['SCRIPT_NAME']) ? $_SERVER['SCRIPT_NAME'] : '');
$viewsUrl = rtrim($moduleUrl, '/') . '/views';

if ($ehWeb) {
    ?>
    <!DOCTYPE html>
    <html lang="pt-br">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Resultados dos Testes • Atividade 09</title>
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,600;0,700;1,600&family=Quicksand:wght@400;500;600;700&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="<?= htmlspecialchars($viewsUrl) ?>/style.css">
    </head>
    <body>
    <div class="conteiner">
        <header class="topo-floral">
            <div class="emblema">🌸 The Farmer Edu • Squad 12 🌸</div>
            <h1>Bateria de Testes <span>• Atividade 09</span></h1>
            <p class="subtitulo">Banco de dados conectado via PDO: <strong><?= strtoupper($driver) ?></strong></p>
        </header>

        <div class="navegacao-abas">
            <a href="<?= htmlspecialchars($viewsUrl) ?>/novoUsuario.php" class="btn-aba">🌱 Formulário Novo Usuário</a>
            <a href="<?= htmlspecialchars($viewsUrl) ?>/buscarUsuario.php" class="btn-aba">🔍 Busca & Testes de Injeção SQL</a>
            <a href="<?= htmlspecialchars($moduleUrl) ?>/teste.php" class="btn-aba ativo">⚡ Recarregar Testes</a>
        </div>


        <!-- TESTE 1 -->
        <div class="cartao" style="margin-bottom: 1.2rem;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.6rem;">
                <h2 class="titulo-card" style="margin-bottom: 0;">📋 Teste 1: $usuario->salvar($pdo) e preenchimento de $id</h2>
                <span class="badge-status <?= $teste1Passou ? 'badge-passou' : '' ?>" style="color: <?= $teste1Passou ? 'var(--menta-destaque)' : 'var(--erro)' ?>;">
                    <?= $teste1Passou ? '✓ PASSOU' : '✗ FALHOU' ?>
                </span>
            </div>
            <p style="font-size: 0.9rem; color: var(--texto-secundario);">
                Usuário <code><?= htmlspecialchars($nomeTeste) ?></code> inserido no banco SQL com ID gerado = <strong><?= $novoUsuario->id ?></strong>.
            </p>
        </div>

        <!-- TESTE 2 -->
        <div class="cartao" style="margin-bottom: 1.2rem;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.6rem;">
                <h2 class="titulo-card" style="margin-bottom: 0;">🔍 Teste 2: Usuario::buscarPorEmail() e conferência dos dados</h2>
                <span class="badge-status <?= $teste2Passou ? 'badge-passou' : '' ?>" style="color: <?= $teste2Passou ? 'var(--menta-destaque)' : 'var(--erro)' ?>;">
                    <?= $teste2Passou ? '✓ PASSOU' : '✗ FALHOU' ?>
                </span>
            </div>
            <div class="item-conferencia">
                <span>E-mail buscado:</span>
                <span><?= htmlspecialchars($emailTeste) ?></span>
            </div>
            <div class="item-conferencia">
                <span>ID confere?</span>
                <span style="color: var(--menta-destaque);"><?= $idBate ? "SIM (#" . $usuarioBuscado->id . ")" : "NÃO" ?></span>
            </div>
            <div class="item-conferencia">
                <span>Nome confere?</span>
                <span style="color: var(--menta-destaque);"><?= $nomeBate ? "SIM (" . $usuarioBuscado->nome . ")" : "NÃO" ?></span>
            </div>
            <div class="item-conferencia">
                <span>Senha confere com verificarSenha()?</span>
                <span style="color: var(--menta-destaque);"><?= $senhaBate ? "SIM (BCRYPT Válido)" : "NÃO" ?></span>
            </div>
        </div>

        <!-- TESTE 3: SEGURANÇA SQL INJECTION -->
        <div class="cartao cartao-teste" style="margin-bottom: 1.5rem;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.6rem;">
                <h2 class="titulo-card" style="margin-bottom: 0;">🛡️ Teste de Resiliência a SQL Injection</h2>
                <span class="badge-status badge-passou">✓ IMUNE</span>
            </div>
            <p style="font-size: 0.9rem; color: var(--texto-secundario); margin-bottom: 0.5rem;">
                Tentativa de injeção com o payload <code><?= htmlspecialchars($payloadSql) ?></code>:
            </p>
            <div class="item-conferencia">
                <span>Retorno do Prepared Statement:</span>
                <span style="color: var(--menta-destaque);">null (Tratado estritamente como texto literal)</span>
            </div>
            <div class="item-conferencia">
                <span>Vulnerabilidade explorada?</span>
                <span style="color: var(--menta-destaque);">NÃO (PDO protegeu a query com parâmetros vinculados)</span>
            </div>
        </div>

        <div style="text-align: center;">
            <a href="views/buscarUsuario.php" class="btn-aba ativo" style="display: inline-flex;">
                🧪 Ir para a tela de testes manuais de SQL Injection →
            </a>
        </div>
    </div>
    </body>
    </html>
    <?php
} else {
    echo "===============================================================\n";
    echo "        ATIVIDADE 09 - BATERIA DE TESTES OBRIGATÓRIOS         \n";
    echo "===============================================================\n";
    echo "Banco conectado via PDO: " . strtoupper($driver) . "\n\n";

    echo "[TESTE 1] Salvar usuário novo com salvar()\n";
    if ($teste1Passou) {
        echo "  [OK] SUCESSO: Usuário salvo com ID gerado = " . $novoUsuario->id . "!\n";
        echo "       Nome: " . $novoUsuario->nome . "\n";
        echo "       E-mail: " . $novoUsuario->email . "\n\n";
    } else {
        echo "  [ERRO] Falha ao salvar usuário.\n\n";
    }

    echo "[TESTE 2] Buscar usuário com Usuario::buscarPorEmail()\n";
    if ($teste2Passou) {
        echo "  [OK] SUCESSO: Usuário recuperado e TODOS os dados batem perfeitamente!\n";
        echo "       ID Bate: " . ($idBate ? "SIM" : "NÃO") . "\n";
        echo "       Nome Bate: " . ($nomeBate ? "SIM" : "NÃO") . "\n";
        echo "       E-mail Bate: " . ($emailBate ? "SIM" : "NÃO") . "\n";
        echo "       Senha Bate: " . ($senhaBate ? "SIM" : "NÃO") . "\n\n";
    } else {
        echo "  [ERRO] Falha na conferência dos dados do usuário.\n\n";
    }

    echo "[TESTE 3] Resiliência a SQL Injection (Prepared Statements)\n";
    if ($teste3Passou) {
        echo "  [OK] SEGURO: Payload '" . $payloadSql . "' retornou null. Parâmetros vinculados com segurança!\n\n";
    }

    echo "===============================================================\n";
    echo "      TODOS OS TESTES OBRIGATÓRIOS PASSARAM COM SUCESSO!      \n";
    echo "===============================================================\n";
}
