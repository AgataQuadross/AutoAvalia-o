<?php
/**
 * View: Busca de Usuário por E-mail e Teste Obrigatório 2 / SQL Injection Manual
 * Atividade 09 - Tarde 12
 * 
 * Pode ser acessada diretamente pelo XAMPP ou via include do Controller.
 * Os links e actions usam $baseUrl absoluto para funcionar nos dois casos.
 */

// Se for acessado diretamente sem passar pelo Controller
if (!isset($resultadoTeste2)) {
    require_once __DIR__ . '/../Controller.php';
    $ctrl = new UsuarioController();
    $ctrl->showBuscarUsuario();
    exit;
}

// $baseUrl foi calculado pelo Controller. Fallback para acesso direto.
$baseUrl = isset($baseUrl) ? $baseUrl : (isset($_SERVER['SCRIPT_NAME']) ? dirname($_SERVER['SCRIPT_NAME']) : '');
$baseCss  = $baseUrl . '/style.css';
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buscar Usuário & SQL Injection • Atividade 09</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,600;0,700;1,600&family=Quicksand:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= htmlspecialchars($baseCss) ?>">
</head>
<body>

<div class="conteiner">
    <!-- CABEÇALHO -->
    <header class="topo-floral">
        <div class="emblema">
            🌸 The Farmer Edu • Squad 12 🌸
        </div>
        <h1>Atividade 09 <span>• Busca & Testes de Injeção SQL</span></h1>
        <p class="subtitulo">
            Parâmetros vinculados via Prepared Statement — o PDO os trata como texto literal, nunca como SQL.
        </p>
    </header>

    <!-- NAVEGAÇÃO com URLs absolutas -->
    <div class="navegacao-abas">
        <a href="<?= $baseUrl ?>/novoUsuario.php" class="btn-aba">
            🌱 Novo Usuário (Teste 1)
        </a>
        <a href="<?= $baseUrl ?>/buscarUsuario.php" class="btn-aba ativo">
            🔍 Busca & SQL Injection (Teste 2)
        </a>
        <a href="<?= dirname($baseUrl) ?>/teste.php" class="btn-aba">
            ⚡ Executar Testes Completos
        </a>
    </div>

    <!-- FEEDBACK DE BUSCA -->
    <?php if (!empty($mensagemBusca)): ?>
        <div class="alerta <?= htmlspecialchars($mensagemBusca['tipo']) ?>">
            <?= htmlspecialchars($mensagemBusca['texto']) ?>
        </div>
    <?php endif; ?>

    <!-- GRADE DUPLA: BUSCA INTERATIVA / SQLI + TESTE 2 -->
    <div class="grade-dupla">

        <!-- LADO 1: FORMULÁRIO DE CONSULTA E TESTE DE SQL INJECTION -->
        <div class="cartao">
            <h2 class="titulo-card">
                <span>🛡️</span> Consulta & Teste de SQL Injection
            </h2>

            <!-- action com URL absoluta -->
            <form method="GET" action="<?= $baseUrl ?>/buscarUsuario.php" novalidate>
                <div class="campo">
                    <label for="campo-busca">E-mail ou Payload SQL Injection</label>
                    <input type="text" id="campo-busca" name="email"
                           value="<?= htmlspecialchars($emailPesquisado) ?>"
                           placeholder="Ex: ' OR '1'='1 ou usuario@fazenda.edu.br" required>
                </div>

                <div class="campo">
                    <label for="senha_teste">Conferir Senha com verificarSenha() (Opcional)</label>
                    <input type="password" id="senha_teste" name="senha_teste" placeholder="Digite a senha para validação BCRYPT">
                </div>

                <button type="submit" class="btn-enviar menta">
                    🔎 Executar Usuario::buscarPorEmail($pdo, $email)
                </button>
            </form>

            <!-- BOTÕES RÁPIDOS DE PAYLOAD -->
            <div class="painel-payloads">
                <p><strong>⚡ Payloads de teste rápido para SQL Injection:</strong></p>
                <div class="grade-botoes-payload">
                    <button type="button" class="btn-payload" onclick="preencherPayload(this.innerText)">' OR '1'='1</button>
                    <button type="button" class="btn-payload" onclick="preencherPayload(this.innerText)">' OR 1=1 -- </button>
                    <button type="button" class="btn-payload" onclick="preencherPayload(this.innerText)">admin' -- </button>
                    <button type="button" class="btn-payload" onclick="preencherPayload(this.innerText)">' UNION SELECT 1,'x','x@x.com','Aluno',NULL,0,NULL,NOW() -- </button>
                </div>
            </div>

            <!-- DIAGNÓSTICO DO PREPARED STATEMENT -->
            <?php if (!empty($emailPesquisado)): ?>
                <div style="margin-top: 1.2rem; padding-top: 1rem; border-top: 1px solid var(--borda-suave);">
                    <h3 style="color: var(--menta-destaque); font-size: 1rem; margin-bottom: 0.6rem;">
                        Diagnóstico da Consulta Preparada:
                    </h3>

                    <div class="item-conferencia">
                        <span>Query Preparada:</span>
                        <code style="color: var(--rosa-pastel); font-size: 0.82rem;">SELECT * FROM usuarios WHERE email = :email LIMIT 1</code>
                    </div>
                    <div class="item-conferencia">
                        <span>Parâmetro <code>:email</code> vinculado:</span>
                        <code style="color: var(--menta-claro);"><?= htmlspecialchars($emailPesquisado) ?></code>
                    </div>
                    <div class="item-conferencia">
                        <span>Status de Segurança:</span>
                        <span style="color: var(--sucesso);">🛡️ Seguro (Prepared Statement)</span>
                    </div>
                    <div class="item-conferencia">
                        <span>Retorno do Método:</span>
                        <span style="color: <?= $usuarioBuscado ? 'var(--sucesso)' : 'var(--texto-secundario)' ?>;">
                            <?= $usuarioBuscado
                                ? get_class($usuarioBuscado) . " (ID #{$usuarioBuscado->id})"
                                : "null — Nenhum registro burlado" ?>
                        </span>
                    </div>

                    <?php if ($usuarioBuscado !== null): ?>
                        <div style="margin-top: 0.8rem; background: #0c1214; padding: 0.8rem; border-radius: 8px; border: 1px solid var(--borda-suave);">
                            <p style="font-size: 0.85rem; color: var(--rosa-pastel); font-weight: 600; margin-bottom: 0.4rem;">Dados do Usuário Encontrado:</p>
                            <p style="font-size: 0.84rem;">
                                <strong>ID:</strong> <?= $usuarioBuscado->id ?>
                                &nbsp;|&nbsp; <strong>Nome:</strong> <?= htmlspecialchars($usuarioBuscado->nome) ?>
                                &nbsp;|&nbsp; <strong>Tipo:</strong> <?= htmlspecialchars($usuarioBuscado->tipo) ?>
                            </p>
                            <p style="font-size: 0.84rem;">
                                <strong>E-mail:</strong> <?= htmlspecialchars($usuarioBuscado->email) ?>
                                &nbsp;|&nbsp; <strong>Saudação:</strong> <?= htmlspecialchars($usuarioBuscado->saudacao()) ?>
                            </p>
                            <?php if ($senhaVerificada !== null): ?>
                                <p style="font-size: 0.84rem; margin-top: 0.3rem;">
                                    <strong>Senha confere?</strong>
                                    <span style="color: <?= $senhaVerificada ? 'var(--sucesso)' : 'var(--erro)' ?>;">
                                        <?= $senhaVerificada ? '✓ SIM (BCRYPT Válido)' : '✗ NÃO (Incorreta)' ?>
                                    </span>
                                </p>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- LADO 2: TESTE OBRIGATÓRIO 2 SIMPLIFICADO -->
        <div class="cartao cartao-teste">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.8rem;">
                <h2 class="titulo-card" style="margin-bottom: 0;">
                    <span>📋</span> Teste Obrigatório 2
                </h2>
                <?php if ($resultadoTeste2['passou']): ?>
                    <span class="badge-status badge-passou">✓ PASSOU</span>
                <?php endif; ?>
            </div>

            <p style="color: var(--texto-secundario); font-size: 0.85rem; margin-bottom: 0.8rem;">
                Busca com <code>Usuario::buscarPorEmail()</code> e confirma que todos os dados conferem.
            </p>

            <div class="item-conferencia">
                <span>Resultado:</span>
                <span style="color: var(--menta-destaque);"><?= htmlspecialchars($resultadoTeste2['mensagem']) ?></span>
            </div>

            <div class="item-conferencia">
                <span>E-mail consultado:</span>
                <span><?= htmlspecialchars($resultadoTeste2['email_pesquisado']) ?></span>
            </div>

            <div class="item-conferencia">
                <span>ID bate?</span>
                <span style="color: var(--menta-destaque);"><?= $resultadoTeste2['id_bate'] ? "SIM (#{$resultadoTeste2['buscado']->id})" : "NÃO" ?></span>
            </div>

            <div class="item-conferencia">
                <span>Nome bate?</span>
                <span style="color: var(--menta-destaque);"><?= $resultadoTeste2['nome_bate'] ? "SIM ({$resultadoTeste2['buscado']->nome})" : "NÃO" ?></span>
            </div>

            <div class="item-conferencia">
                <span>E-mail bate?</span>
                <span style="color: var(--menta-destaque);"><?= $resultadoTeste2['email_bate'] ? "SIM" : "NÃO" ?></span>
            </div>

            <div class="item-conferencia">
                <span>Senha bate (verificarSenha)?</span>
                <span style="color: var(--menta-destaque);"><?= $resultadoTeste2['senha_bate'] ? "SIM (BCRYPT Válido)" : "NÃO" ?></span>
            </div>

            <div class="caixa-codigo">
$usuario = Usuario::buscarPorEmail($pdo, "<?= htmlspecialchars($resultadoTeste2['email_pesquisado']) ?>");

assert($usuario->id === $original->id);       // Bate!
assert($usuario->nome === $original->nome);   // Bate!
assert($usuario->email === $original->email); // Bate!
assert($usuario->verificarSenha($senhaOriginal)); // Bate!
            </div>
        </div>

    </div>
</div>

<script>
function preencherPayload(payload) {
    document.getElementById('campo-busca').value = payload;
}
</script>

</body>
</html>
