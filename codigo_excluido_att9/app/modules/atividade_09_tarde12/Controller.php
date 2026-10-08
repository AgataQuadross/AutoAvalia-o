<?php

require_once __DIR__ . '/Model.php';

class UsuarioController {
    /**
     * @var PDO
     */
    private $pdo = null;

    /**
     * @param PDO|null $pdo
     */
    public function __construct($pdo = null) {
        $this->pdo = ($pdo instanceof PDO) ? $pdo : self::obterConexao();
    }

    /**
     * Retorna a conexão ativa com o banco de dados.
     * Utiliza o core do sistema se disponível e o arquivo /database/schema.sql como referência.
     * 
     * @return PDO
     */
    public static function obterConexao() {
        static $instancia = null;
        if ($instancia !== null) {
            return $instancia;
        }

        // 1. Tenta utilizar a função iniciarPDO() do módulo core se disponível
        $caminhoCore = dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'core' . DIRECTORY_SEPARATOR . 'database.php';
        if (file_exists($caminhoCore)) {
            @require_once $caminhoCore;
            if (function_exists('iniciarPDO')) {
                try {
                    $instancia = iniciarPDO();
                    Usuario::garantirTabelas($instancia);
                    return $instancia;
                } catch (Exception $eCore) {
                    // Fallback para conexão direta se iniciarPDO() falhar
                }
            }
        }
        try {
            $pdoRoot = new PDO("mysql:host=" . $host . ";charset=utf8mb4", $usuario, $senha, array(
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ));

            $pdoRoot->exec("CREATE DATABASE IF NOT EXISTS `" . $banco . "` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");

            $instancia = new PDO("mysql:host=" . $host . ";dbname=" . $banco . ";charset=utf8mb4", $usuario, $senha, array(
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ));
        } catch (Exception $eMysql) {
            // Fallback para SQLite local caso o MySQL do XAMPP não esteja iniciado
            $caminhoSqlite = __DIR__ . DIRECTORY_SEPARATOR . 'banco_atividade09.sqlite';
            $instancia = new PDO("sqlite:" . $caminhoSqlite, null, null, array(
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ));
        }

        // Garante a existência das tabelas tendo /database/schema.sql como referência
        Usuario::garantirTabelas($instancia);

        return $instancia;
    }

    /**
     * Calcula o URL base absoluto que aponta para a pasta /views/ do módulo.
     * 
     * @return string
     */
    private function calcularBaseUrl() {
        $viewsDir = realpath(__DIR__ . '/views');
        $docRoot = realpath(isset($_SERVER['DOCUMENT_ROOT']) ? $_SERVER['DOCUMENT_ROOT'] : '');

        if ($docRoot && $viewsDir && strpos($viewsDir, $docRoot) === 0) {
            return str_replace('\\', '/', substr($viewsDir, strlen($docRoot)));
        }

        $scriptDir = dirname(isset($_SERVER['SCRIPT_NAME']) ? $_SERVER['SCRIPT_NAME'] : '');
        if (strpos($scriptDir, 'views') !== false) {
            return $scriptDir;
        }

        return rtrim($scriptDir, '/') . '/views';
    }

    /**
     * Validação sintática de login.
     * 
     * @param string $email
     * @param string $senha
     * @return array
     */
    public function validar_login($email, $senha) {
        $email = (string)$email;
        $senha = (string)$senha;
        $erros = array();

        if (empty($email) || empty($senha)) {
            $erros["mensagem"] = "Preencha todos os campos.";
            $erros["status"] = "erro";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL) && strlen($senha) < 8) {
            $erros["mensagem"] = "E-mail e Senha inválidos.";
            $erros["status"] = "erro";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $erros["mensagem"] = "E-mail inválido.";
            $erros["status"] = "erro";
        } elseif (strlen($senha) < 8) {
            $erros["mensagem"] = "Senha deve ter no mínimo 8 caracteres.";
            $erros["status"] = "erro";
        } else {
            $erros["mensagem"] = "Tudo Certo!";
            $erros["status"] = "sucesso";
        }

        return $erros;
    }

    /**
     * Exibe a view de criação de novo usuário e processa o cadastro via $usuario->salvar($pdo)
     * 
     * @return void
     */
    public function showNovoUsuario() {
        $pdo = $this->pdo;
        $mensagemFeedback = null;
        $usuarioCriado = null;
        $resultadoTeste1 = null;

        // Processa formulário de cadastro caso enviado
        if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao']) && $_POST['acao'] === 'cadastrar') {
            $nome = trim(isset($_POST['nome']) ? $_POST['nome'] : '');
            $email = trim(isset($_POST['email']) ? $_POST['email'] : '');
            $senha = trim(isset($_POST['senha']) ? $_POST['senha'] : '');
            $tipo = trim(isset($_POST['tipo']) ? $_POST['tipo'] : 'Aluno');
            $xp_total = (int)(isset($_POST['xp_total']) ? $_POST['xp_total'] : 0);
            $materias = trim(isset($_POST['materias_leciona']) ? $_POST['materias_leciona'] : '');

            $validacao = $this->validar_login($email, $senha);

            if ($validacao['status'] === 'erro') {
                $mensagemFeedback = array('tipo' => 'erro', 'texto' => "Falha no cadastro: " . $validacao['mensagem']);
            } else {
                try {
                    $tipoLower = strtolower($tipo);
                    if ($tipoLower === 'aluno') {
                        $usuario = new Aluno(null, $nome, $email, $xp_total);
                    } elseif ($tipoLower === 'instrutor') {
                        $listaMaterias = !empty($materias) ? array_map('trim', explode(',', $materias)) : array();
                        $usuario = new Instrutor(null, $nome, $email, $listaMaterias);
                    } else {
                        $usuario = new Usuario(null, $nome, $email, $tipo);
                    }

                    // Encapsulamento da senha
                    $usuario->definirSenha($senha);

                    // REQUISITO OBRIGATÓRIO: Salvar no banco SQL
                    $sucesso = $usuario->salvar($pdo);

                    if ($sucesso && !empty($usuario->id)) {
                        $usuarioCriado = $usuario;
                        $mensagemFeedback = array(
                            'tipo' => 'sucesso',
                            'texto' => "Usuário '{$usuario->nome}' cadastrado com sucesso! ID gerado no banco SQL: {$usuario->id}"
                        );
                    } else {
                        $mensagemFeedback = array('tipo' => 'erro', 'texto' => "Não foi possível salvar o usuário no banco de dados.");
                    }
                } catch (Exception $e) {
                    $mensagemFeedback = array('tipo' => 'erro', 'texto' => "Erro ao salvar no banco: " . $e->getMessage());
                }
            }
        }

        // Executa demonstração automática do Teste 1 para exibição na tela
        $resultadoTeste1 = $this->executarTeste1Demonstracao();

        // Calcula o URL base das views para que os hrefs e actions funcionem
        $baseUrl = $this->calcularBaseUrl();

        // Carrega a View
        require __DIR__ . '/views/novoUsuario.php';
    }

    /**
     * Exibe a view de busca por e-mail e processa a consulta via Usuario::buscarPorEmail($pdo, $email)
     * 
     * @return void
     */
    public function showBuscarUsuario() {
        $pdo = $this->pdo;
        $usuarioBuscado = null;
        $emailPesquisado = '';
        if (isset($_GET['email'])) {
            $emailPesquisado = trim($_GET['email']);
        } elseif (isset($_POST['email'])) {
            $emailPesquisado = trim($_POST['email']);
        }

        $mensagemBusca = null;

        if (!empty($emailPesquisado)) {
            // REQUISITO OBRIGATÓRIO: Busca estática por e-mail
            $usuarioBuscado = Usuario::buscarPorEmail($pdo, $emailPesquisado);

            if ($usuarioBuscado !== null) {

                $mensagemBusca = array(
                    'tipo' => 'sucesso',
                    'texto' => "Usuário encontrado com sucesso na tabela 'usuarios'!"
                );
            } else {
                $mensagemBusca = array(
                    'tipo' => 'aviso',
                    'texto' => "Nenhum usuário encontrado com o e-mail '{$emailPesquisado}'."
                );
            }
        }

        // Executa demonstração automática do Teste 2 para exibição na tela
        $resultadoTeste2 = $this->executarTeste2Demonstracao();

        // Calcula o URL base das views para que os hrefs e actions funcionem
        $baseUrl = $this->calcularBaseUrl();

        // Carrega a View
        require __DIR__ . '/views/buscarUsuario.php';
    }

    /**
     * Executa o Teste Obrigatório 1: Salvar novo usuário e confirmar que $id foi preenchido.
     * 
     * @return array
     */
    public function executarTeste1Demonstracao() {
        $timestamp = time() . '_' . rand(10, 99);
        $email = "aluno.teste" . $timestamp . "@fazenda.edu.br";
        $senha = "SenhaSegura#" . $timestamp;

        $usuario = new Aluno();
        $usuario->nome = "Aluno Teste Demo " . $timestamp;
        $usuario->email = $email;
        $usuario->xp_total = 100;
        $usuario->definirSenha($senha);

        $idAntes = $usuario->id;
        $sucesso = $usuario->salvar($this->pdo);
        $idDepois = $usuario->id;

        $passou = ($sucesso && !empty($idDepois) && $idDepois > 0);

        return array(
            'titulo' => 'Teste Obrigatório 1: $usuario->salvar($pdo) e preenchimento de $id',
            'passou' => $passou,
            'id_antes' => $idAntes,
            'id_depois' => $idDepois,
            'email' => $email,
            'objeto' => $usuario,
            'mensagem' => $passou 
                ? "SUCESSO: \$usuario->salvar(\$pdo) executou e \$id foi atualizado para " . $idDepois . "!" 
                : "FALHA: \$id não foi preenchido corretamente."
        );
    }

    /**
     * Executa o Teste Obrigatório 2: Buscar mesmo usuário com buscarPorEmail() e conferir se dados batem.
     * 
     * @return array
     */
    public function executarTeste2Demonstracao() {
        $timestamp = time() . '_' . rand(100, 999);
        $email = "instrutor.teste" . $timestamp . "@fazenda.edu.br";
        $senha = "InstrutorPass#" . $timestamp;

        // 1. Cadastra
        $original = new Instrutor();
        $original->nome = "Profa. Helena Demo " . $timestamp;
        $original->email = $email;
        $original->materias_leciona = array("PHP SQL", "MVC POO");
        $original->definirSenha($senha);
        $original->salvar($this->pdo);

        // 2. Busca estática
        $buscado = Usuario::buscarPorEmail($this->pdo, $email);

        $encontrado = ($buscado !== null);
        $idBate = $encontrado && ($buscado->id === $original->id);
        $nomeBate = $encontrado && ($buscado->nome === $original->nome);
        $emailBate = $encontrado && ($buscado->email === $original->email);
        $senhaBate = $encontrado && $buscado->verificarSenha($senha);
        $todosBatem = $encontrado && $idBate && $nomeBate && $emailBate && $senhaBate;

        return array(
            'titulo' => 'Teste Obrigatório 2: Usuario::buscarPorEmail($pdo, $email) e conferência de dados',
            'passou' => $todosBatem,
            'email_pesquisado' => $email,
            'original' => $original,
            'buscado' => $buscado,
            'id_bate' => $idBate,
            'nome_bate' => $nomeBate,
            'email_bate' => $emailBate,
            'senha_bate' => $senhaBate,
            'mensagem' => $todosBatem 
                ? "SUCESSO: Usuário recuperado por e-mail e TODOS os dados batem perfeitamente!" 
                : "FALHA: Os dados não conferem ou usuário não foi encontrado."
        );
    }
}