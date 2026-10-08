<?php
/**
 * View: Cadastro de Novo Usuário e Teste Obrigatório 1
 * Atividade 09 - Tarde 12
 * 
 * Pode ser acessada diretamente pelo XAMPP ou via include do Controller.
 * Os links e actions usam $baseUrl absoluto para funcionar nos dois casos.
 */

// Se for acessado diretamente sem passar pelo Controller, o Controller cuida de tudo
if (!isset($resultadoTeste1)) {
    require_once __DIR__ . '/../Controller.php';
    $ctrl = new UsuarioController();
    $ctrl->showNovoUsuario();
    exit;
}

// $baseUrl foi calculado pelo Controller antes do require desta view
// Fallback para acesso direto (quando a view já executou o Controller acima e
// o $baseUrl foi definido pelo calcularBaseUrl() do controller)
$baseUrl = isset($baseUrl) ? $baseUrl : (isset($_SERVER['SCRIPT_NAME']) ? dirname($_SERVER['SCRIPT_NAME']) : '');
$baseCss  = $baseUrl . '/style.css';
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Novo Usuário • Atividade 09 - The Farmer Edu</title>
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
        <h1>Atividade 09 <span>• Cadastro & Persistência SQL</span></h1>
        <p class="subtitulo">
            Persistência em SQL com Prepared Statements. Teste manual do <code>$usuario->salvar($pdo)</code>.
        </p>
    </header>

    <!-- NAVEGAÇÃO com URLs absolutas — funcionam de qualquer contexto -->
    <div class="navegacao-abas">
        <a href="<?= $baseUrl ?>/novoUsuario.php" class="btn-aba ativo">
            🌱 Novo Usuário (Teste 1)
        </a>
        <a href="<?= $baseUrl ?>/buscarUsuario.php" class="btn-aba">
            🔍 Busca & SQL Injection (Teste 2)
        </a>
        <a href="<?= dirname($baseUrl) ?>/teste.php" class="btn-aba">
            ⚡ Executar Testes Completos
        </a>
    </div>

    <!-- FEEDBACK DE SUBMISSÃO -->
    <?php if (!empty($mensagemFeedback)): ?>
        <div class="alerta <?= htmlspecialchars($mensagemFeedback['tipo']) ?>">
            <?= htmlspecialchars($mensagemFeedback['texto']) ?>
        </div>
    <?php endif; ?>

    <!-- GRADE DUPLA: FORMULÁRIO + TESTE 1 -->
    <div class="grade-dupla">

        <!-- LADO 1: FORMULÁRIO DE CADASTRO -->
        <div class="cartao">
            <h2 class="titulo-card">
                <span>🌱</span> Inserir Novo Usuário
            </h2>

            <!-- action com URL absoluta garante que o POST vai para o lugar certo -->
            <form method="POST" action="<?= $baseUrl ?>/novoUsuario.php" novalidate>
                <input type="hidden" name="acao" value="cadastrar">

                <div class="campo">
                    <label for="nome">Nome Completo</label>
                    <input type="text" id="nome" name="nome" placeholder="Ex: Ágata Quadros" required>
                </div>

                <div class="campo">
                    <label for="email">E-mail</label>
                    <input type="text" id="email" name="email" placeholder="usuario@fazenda.edu.br" required>
                </div>

                <div class="campo">
                    <label for="senha">Senha (mín. 8 caracteres)</label>
                    <input type="password" id="senha" name="senha" placeholder="Digite uma senha forte" required>
                </div>

                <div class="campo">
                    <label for="tipo">Tipo (Herança POO)</label>
                    <select id="tipo" name="tipo" onchange="alternarCampos(this.value)">
                        <option value="Aluno">Aluno (com XP Total)</option>
                        <option value="Instrutor">Instrutor (com Matérias)</option>
                        <option value="Usuário">Usuário Genérico</option>
                    </select>
                </div>

                <div class="campo" id="campo-xp">
                    <label for="xp_total">XP Total Inicial</label>
                    <input type="number" id="xp_total" name="xp_total" value="50" min="0">
                </div>

                <div class="campo" id="campo-materias" style="display: none;">
                    <label for="materias_leciona">Matérias (separadas por vírgula)</label>
                    <input type="text" id="materias_leciona" name="materias_leciona" placeholder="Ex: PHP, SQL, POO">
                </div>

                <button type="submit" class="btn-enviar">
                    💾 Executar $usuario->salvar($pdo)
                </button>
            </form>
        </div>

        <!-- LADO 2: TESTE OBRIGATÓRIO 1 SIMPLIFICADO -->
        <div class="cartao cartao-teste">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.8rem;">
                <h2 class="titulo-card" style="margin-bottom: 0;">
                    <span>📋</span> Teste Obrigatório 1
                </h2>
                <?php if ($resultadoTeste1['passou']): ?>
                    <span class="badge-status badge-passou">✓ PASSOU</span>
                <?php endif; ?>
            </div>

            <p style="color: var(--texto-secundario); font-size: 0.85rem; margin-bottom: 0.8rem;">
                Confirma que <code>$usuario->salvar($pdo)</code> insere no SQL e preenche <code>$usuario->id</code>.
            </p>

            <div class="item-conferencia">
                <span>Resultado:</span>
                <span style="color: var(--menta-destaque);"><?= htmlspecialchars($resultadoTeste1['mensagem']) ?></span>
            </div>

            <div class="item-conferencia">
                <span>$id antes de salvar:</span>
                <span><?= $resultadoTeste1['id_antes'] === null ? 'null' : $resultadoTeste1['id_antes'] ?></span>
            </div>

            <div class="item-conferencia">
                <span>$id gerado (lastInsertId):</span>
                <span style="color: var(--rosa-pastel); font-size: 1.05rem;"><?= htmlspecialchars((string)$resultadoTeste1['id_depois']) ?></span>
            </div>

            <div class="item-conferencia">
                <span>E-mail salvo:</span>
                <span><?= htmlspecialchars($resultadoTeste1['email']) ?></span>
            </div>

            <div class="caixa-codigo">
$usuario = new Aluno(null, "<?= htmlspecialchars($resultadoTeste1['objeto']->nome) ?>", "...");
$usuario->definirSenha("SenhaSegura");
$usuario->salvar($pdo); // Prepared Statement INSERT

echo $usuario->id; // <?= $resultadoTeste1['id_depois'] ?> (Preenchido!)
            </div>
        </div>

    </div>
</div>

<script>
function alternarCampos(tipo) {
    document.getElementById('campo-xp').style.display  = (tipo === 'Aluno')     ? 'block' : 'none';
    document.getElementById('campo-materias').style.display = (tipo === 'Instrutor') ? 'block' : 'none';
}
</script>

</body>
</html>
