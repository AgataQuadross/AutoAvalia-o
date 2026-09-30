<?php
/**
 * View do MVC: Exibição dos resultados processados pelo ControllerUsuario.
 * 
 * Papel da View no MVC:
 * - Responsável unicamente pela apresentação gráfica (HTML/CSS).
 * - Não realiza consultas a banco nem altera regras de negócio.
 * - Recebe os dados já prontos do Controller ($usuarios, $testes, $loginInterativo).
 * 
 * Tema: Escuro com destaques em Rosa Pastel e Verde Menta Claro.
 */

// Resolução dinâmica do caminho do arquivo CSS
$nomeScript = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
$estaNoDiretorioPublico = (strpos($nomeScript, '/public') !== false);
$caminhoCss = $estaNoDiretorioPublico ? 'css/style.css' : 'public/css/style.css';
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AutoAvaliação MVC • Usuários & Autenticação</title>
    <!-- Fontes do Google -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,600;0,700;1,600&family=Quicksand:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- Folha de Estilos em Tema Escuro -->
    <link rel="stylesheet" href="<?= htmlspecialchars($caminhoCss) ?>">
</head>
<body>

<div class="conteiner-principal">

    <!-- CABEÇALHO FLORAL (Tema Escuro com Rosa Pastel e Menta) -->
    <header class="cabecalho-floral">
        <div class="emblema-floral">
            <span>🌸</span> Arquitetura MVC • POO em PHP <span>🌿</span>
        </div>
        <h1>AutoAvaliação <span>MVC</span></h1>
        <p>Demonstração prática de Herança (Instrutor & Aluno), Encapsulamento de Senhas (BCRYPT), Controller com <code>validar_login()</code> e View em HTML.</p>
        
        <div class="divisor-floral">
            🌸 ✿ 🌿 ✿ 🌸
        </div>
    </header>

    <!-- CARDS DOS 4 PILARES DA ATIVIDADE -->
    <section class="grade-pilares">
        <div class="cartao-pilar">
            <div class="icone-pilar">🌱</div>
            <div>
                <h4>Herança</h4>
                <p>Instrutor e Aluno herdam id, nome e email de Usuario</p>
            </div>
        </div>

        <div class="cartao-pilar">
            <div class="icone-pilar rosa">🔒</div>
            <div>
                <h4>Encapsulamento</h4>
                <p>Propriedade $senha_hash privada com password_hash BCRYPT</p>
            </div>
        </div>

        <div class="cartao-pilar">
            <div class="icone-pilar">⚙️</div>
            <div>
                <h4>Controller MVC</h4>
                <p>Função validar_login() e controle das requisições</p>
            </div>
        </div>

        <div class="cartao-pilar">
            <div class="icone-pilar rosa">🎨</div>
            <div>
                <h4>View MVC</h4>
                <p>HTML sem conexão direta com banco, 100% processado</p>
            </div>
        </div>
    </section>

    <!-- SEÇÃO 1: INSTANCIAÇÃO DOS USUÁRIOS (HERANÇA E ENCAPSULAMENTO) -->
    <h2 class="titulo-secao">
        <span>🌺</span> 1. Usuários Instanciados (Herança & Encapsulamento)
    </h2>

    <div class="grade-usuarios">
        <?php foreach ($usuarios as $chave => $u): ?>
            <?php 
                $ehInstrutor = ($u['tipo'] === 'Instrutor');
                $classeCard = $ehInstrutor ? 'instrutor' : 'aluno';
                $iconeCargo = $ehInstrutor ? '👩‍🏫' : '🎓';
                $etiquetaCargo = $ehInstrutor ? '🌿 Instrutor' : '🌸 Aluno';
            ?>
            <div class="cartao-usuario <?= $classeCard ?>">
                <!-- Topo do Card de Usuário -->
                <div class="cabecalho-usuario">
                    <div style="display: flex; align-items: center;">
                        <span class="avatar-usuario"><?= $iconeCargo ?></span>
                        <div class="dados-topo">
                            <h3><?= htmlspecialchars($u['nome']) ?></h3>
                            <small>ID: #<?= (int)$u['id'] ?> • <?= htmlspecialchars($u['email']) ?></small>
                        </div>
                    </div>
                    <span class="etiqueta-cargo <?= $classeCard ?>"><?= $etiquetaCargo ?></span>
                </div>

                <!-- Demonstração das Propriedades Herdadas e Específicas -->
                <div class="secao-dados">
                    <div class="linha-informacao">
                        <span class="rotulo-informacao">Propriedades Herdadas:</span>
                        <span class="valor-informacao">id, nome, email</span>
                    </div>

                    <?php if ($ehInstrutor): ?>
                        <div class="linha-informacao">
                            <span class="rotulo-informacao">Matérias que Leciona:</span>
                            <span class="valor-informacao"><?= count($u['materias_leciona']) ?> matérias</span>
                        </div>
                        <div class="grade-etiquetas">
                            <?php foreach ($u['materias_leciona'] as $materia): ?>
                                <span class="etiqueta-materia">🍃 <?= htmlspecialchars($materia) ?></span>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="linha-informacao">
                            <span class="rotulo-informacao">XP Total (Aluno):</span>
                            <span class="valor-informacao">⭐ <?= (int)$u['xp_total'] ?> pontos</span>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Execução do método herdado saudacao() -->
                <div class="bloco-saudacao">
                    <strong>Chamada de <code>saudacao()</code>:</strong>
                    <em>"<?= htmlspecialchars($u['saudacao']) ?>"</em>
                </div>

                <!-- Demonstração de Encapsulamento da Senha (BCRYPT) -->
                <div class="bloco-seguranca">
                    <div class="titulo-seguranca">
                        <span>🔐</span> Encapsulamento: $senha_hash Privada
                    </div>

                    <div style="font-size: 0.8rem; color: var(--texto-secundario); margin-bottom: 0.35rem;">
                        Senha pura testada: <code><?= htmlspecialchars($u['senha_cadastrada']) ?></code>
                    </div>

                    <span class="texto-hash" title="Hash gerado com PASSWORD_BCRYPT">
                        Hash BCRYPT: <?= htmlspecialchars(substr($u['senha_hash'], 0, 28)) ?>...
                    </span>

                    <div class="lista-testes-senha">
                        <div class="item-teste-senha sucesso">
                            <span>verificarSenha("<?= htmlspecialchars($u['senha_cadastrada']) ?>")</span>
                            <span><?= $u['teste_verificar_correta'] ? '✅ true (Válida)' : '❌ false' ?></span>
                        </div>
                        <div class="item-teste-senha erro">
                            <span>verificarSenha("senhaIncorreta")</span>
                            <span><?= !$u['teste_verificar_incorreta'] ? '✅ false (Bloqueada)' : '❌ true' ?></span>
                        </div>
                    </div>
                </div>

                <!-- Resultado da validação do usuário pelo Controller -->
                <div style="margin-top: 1rem; padding: 0.6rem 0.8rem; border-radius: 8px; background: var(--fundo-campo); font-size: 0.82rem; display: flex; justify-content: space-between; align-items: center;">
                    <span style="color: var(--texto-secundario);">Validação no Controller:</span>
                    <span class="etiqueta-status <?= $u['validacao_login']['status'] ?>">
                        [<?= $u['validacao_login']['status'] ?>] <?= htmlspecialchars($u['validacao_login']['mensagem']) ?>
                    </span>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- SEÇÃO 2: BATERIA DE TESTES AUTOMÁTICOS (IGUAL À PASTA PHP) -->
    <h2 class="titulo-secao">
        <span>🧪</span> 2. Bateria de Testes Automáticos da Função <code>validar_login()</code>
    </h2>

    <section class="conteiner-testes">
        <div class="topo-testes">
            <div>
                <h3>Resultados da Execução Automatizada</h3>
                <p class="subtitulo-testes">Base de testes idêntica à pasta <code>php/base-atividade.php</code>, integrada ao Controller do MVC.</p>
            </div>
            <div class="emblema-floral" style="margin-bottom: 0;">
                <span>🌸</span> 5 Cenários Avaliados
            </div>
        </div>

        <div class="grade-testes">
            <?php foreach ($testes as $t): ?>
                <?php 
                    $ehSucesso = ($t['status'] === 'sucesso');
                    $classeStatus = $ehSucesso ? 'sucesso' : 'erro';
                ?>
                <div class="cartao-teste <?= $classeStatus ?>">
                    <div>
                        <div class="topo-cartao-teste">
                            <span class="numero-teste">
                                <span>🌸</span> Teste <?= (int)$t['numero'] ?>
                            </span>
                            <span class="etiqueta-status <?= $t['status'] ?>">
                                [<?= htmlspecialchars($t['status']) ?>]
                            </span>
                        </div>

                        <div class="campos-teste">
                            <div class="linha-campo-teste">
                                <span class="rotulo-campo">E-mail:</span>
                                <span class="valor-campo">
                                    <?= empty($t['email']) ? '(vazio)' : htmlspecialchars($t['email']) ?>
                                </span>
                            </div>
                            <div class="linha-campo-teste">
                                <span class="rotulo-campo">Senha:</span>
                                <span class="valor-campo">
                                    <?= empty($t['senha']) ? '(vazio)' : htmlspecialchars($t['senha']) ?>
                                </span>
                            </div>
                        </div>
                    </div>

                    <div>
                        <div style="font-size: 0.76rem; color: var(--texto-apagado); margin-bottom: 0.3rem;">
                            Cenário: <?= htmlspecialchars($t['descricao']) ?>
                        </div>
                        <div class="resultado-mensagem-teste">
                            <span><?= $ehSucesso ? '🌿' : '🌷' ?></span>
                            <?= htmlspecialchars($t['mensagem']) ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </section>

    <!-- SEÇÃO 3: SIMULADOR INTERATIVO DE REQUISIÇÕES DE LOGIN -->
    <h2 class="titulo-secao">
        <span>💌</span> 3. Simulador Interativo de Requisições de Login
    </h2>

    <section class="conteiner-simulador">
        <div>
            <h3 style="font-family: 'Playfair Display', serif; font-size: 1.3rem; color: var(--menta-suave);">
                Teste o <code>ControllerUsuario::validar_login()</code> em Tempo Real
            </h3>
            <p style="font-size: 0.88rem; color: var(--texto-secundario);">
                Envie dados de teste para verificar a validação sintática do Controller e a autenticação segura com o hash criptografado.
            </p>
        </div>

        <form method="POST" action="" class="formulario-simulador">
            <input type="hidden" name="acao" value="simular_login">

            <div class="grupo-campo">
                <label class="rotulo-campo-form" for="inputEmail">E-mail para Login:</label>
                <input 
                    type="text" 
                    id="inputEmail" 
                    name="email" 
                    class="entrada-form" 
                    placeholder="ex: helena.flores@jardim.edu.br"
                    value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                    required
                >
            </div>

            <div class="grupo-campo">
                <label class="rotulo-campo-form" for="inputSenha">Senha (mínimo 8 caracteres):</label>
                <input 
                    type="password" 
                    id="inputSenha" 
                    name="senha" 
                    class="entrada-form" 
                    placeholder="Digite a senha..."
                    required
                >
            </div>

            <button type="submit" class="botao-validar">
                <span>🌸</span> Validar Login
            </button>
        </form>

        <!-- Botões de Preenchimento Rápido -->
        <div class="atalhos-preenchimento">
            <span>Preenchimento rápido:</span>
            <button type="button" class="botao-atalho" onclick="preencher('helena.flores@jardim.edu.br', 'ProfJardim@2026')">
                👩‍🏫 Instrutor (Válido)
            </button>
            <button type="button" class="botao-atalho" onclick="preencher('lucas.alecrim@jardim.edu.br', 'Menta#Forte88')">
                🎓 Aluno (Válido)
            </button>
            <button type="button" class="botao-atalho" onclick="preencher('helena.flores@jardim.edu.br', 'senhaInvalida')">
                ⚠️ Senha Incorreta
            </button>
            <button type="button" class="botao-atalho" onclick="preencher('emailinvalido', '12345678')">
                ⚠️ E-mail Inválido
            </button>
        </div>

        <!-- Exibição do Resultado da Simulação -->
        <?php if ($loginInterativo !== null): ?>
            <?php 
                $loginAprovado = ($loginInterativo['validacao_formato']['status'] === 'sucesso' && $loginInterativo['senha_valida']);
                $classeRetorno = $loginAprovado ? 'sucesso' : 'erro';
            ?>
            <div class="retorno-simulador <?= $classeRetorno ?>">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.4rem;">
                    <strong style="color: <?= $loginAprovado ? 'var(--menta-suave)' : 'var(--rosa-suave)' ?>;">
                        <?= $loginAprovado ? '🌿 Login Aprovado!' : '🌷 Não foi possível autenticar' ?>
                    </strong>
                    <span class="etiqueta-status <?= $loginInterativo['validacao_formato']['status'] ?>">
                        Controller: <?= htmlspecialchars($loginInterativo['validacao_formato']['mensagem']) ?>
                    </span>
                </div>
                <p style="font-size: 0.9rem; color: var(--texto-principal);">
                    <?= htmlspecialchars($loginInterativo['mensagem_final']) ?>
                </p>
                <?php if (!empty($loginInterativo['usuario_encontrado'])): ?>
                    <small style="color: var(--texto-apagado); display: block; margin-top: 0.25rem;">
                        Usuário identificado: <strong><?= htmlspecialchars($loginInterativo['usuario_encontrado']) ?></strong>
                    </small>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </section>

    <!-- SEÇÃO 4: RESUMO DOS REQUISITOS TÉCNICOS (CORREÇÃO DE IDENTAÇÃO E VAZAMENTO) -->
    <h2 class="titulo-secao">
        <span>📋</span> 4. Resumo Técnico da Implementação
    </h2>

    <div class="grade-resumo">
        <!-- Card 1: Herança -->
        <div class="cartao-resumo">
            <div class="titulo-resumo">
                <span>🌱</span> Herança (POO)
            </div>
            <ul class="lista-resumo">
                <li>
                    <span class="marcador-resumo">🌸</span>
                    <span class="texto-resumo"><code>Instrutor extends Usuario</code></span>
                </li>
                <li>
                    <span class="marcador-resumo">🌸</span>
                    <span class="texto-resumo"><code>Aluno extends Usuario</code></span>
                </li>
                <li>
                    <span class="marcador-resumo">🌸</span>
                    <span class="texto-resumo">Propriedade <code>$materias_leciona</code> (array)</span>
                </li>
                <li>
                    <span class="marcador-resumo">🌸</span>
                    <span class="texto-resumo">Propriedade <code>$xp_total</code> (int, padrão 0)</span>
                </li>
                <li>
                    <span class="marcador-resumo">🌸</span>
                    <span class="texto-resumo">Acesso pleno a <code>id</code>, <code>nome</code> e <code>email</code></span>
                </li>
            </ul>
        </div>

        <!-- Card 2: Encapsulamento -->
        <div class="cartao-resumo">
            <div class="titulo-resumo">
                <span>🔒</span> Encapsulamento
            </div>
            <ul class="lista-resumo">
                <li>
                    <span class="marcador-resumo">🌸</span>
                    <span class="texto-resumo"><code>$senha_hash</code> privada na classe <code>Usuario</code></span>
                </li>
                <li>
                    <span class="marcador-resumo">🌸</span>
                    <span class="texto-resumo"><code>definirSenha()</code> com <code>PASSWORD_BCRYPT</code></span>
                </li>
                <li>
                    <span class="marcador-resumo">🌸</span>
                    <span class="texto-resumo"><code>verificarSenha()</code> com <code>password_verify()</code></span>
                </li>
                <li>
                    <span class="marcador-resumo">🌸</span>
                    <span class="texto-resumo">Senha em texto puro nunca é exposta</span>
                </li>
            </ul>
        </div>

        <!-- Card 3: Controller -->
        <div class="cartao-resumo">
            <div class="titulo-resumo">
                <span>⚙️</span> Controller & Validação
            </div>
            <ul class="lista-resumo">
                <li>
                    <span class="marcador-resumo">🌸</span>
                    <span class="texto-resumo">Classe <code>ControllerUsuario</code> no padrão MVC</span>
                </li>
                <li>
                    <span class="marcador-resumo">🌸</span>
                    <span class="texto-resumo">Função <code>validar_login()</code> com regras originais</span>
                </li>
                <li>
                    <span class="marcador-resumo">🌸</span>
                    <span class="texto-resumo">Bateria com 5 testes automáticos executada</span>
                </li>
                <li>
                    <span class="marcador-resumo">🌸</span>
                    <span class="texto-resumo">Orquestração e entrega de dados para a View</span>
                </li>
            </ul>
        </div>

        <!-- Card 4: View e Estilo -->
        <div class="cartao-resumo">
            <div class="titulo-resumo">
                <span>🌸</span> View & Estilo
            </div>
            <ul class="lista-resumo">
                <li>
                    <span class="marcador-resumo">🌸</span>
                    <span class="texto-resumo">Arquivo <code>views/usuario/index.php</code></span>
                </li>
                <li>
                    <span class="marcador-resumo">🌸</span>
                    <span class="texto-resumo">Tema escuro moderno com rosa pastel e menta</span>
                </li>
                <li>
                    <span class="marcador-resumo">🌸</span>
                    <span class="texto-resumo">Classes e funções em Português Brasil</span>
                </li>
                <li>
                    <span class="marcador-resumo">🌸</span>
                    <span class="texto-resumo">Totalmente desacoplado de banco de dados</span>
                </li>
            </ul>
        </div>
    </div>

    <!-- RODAPÉ FLORAL -->
    <footer class="rodape-floral">
        <p>🌸 Desenvolvido no padrão <strong>MVC (Model - View - Controller)</strong> em PHP • AutoAvaliação Prática 🌿</p>
    </footer>

</div>

<!-- Script de preenchimento rápido dos botões de teste -->
<script>
function preencher(email, senha) {
    document.getElementById('inputEmail').value = email;
    document.getElementById('inputSenha').value = senha;
}
</script>

</body>
</html>
