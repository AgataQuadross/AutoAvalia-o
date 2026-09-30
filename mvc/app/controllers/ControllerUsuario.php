<?php
require_once __DIR__ . '/../models/usuario.php';

/**
 * Controller do MVC: ControllerUsuario
 * 
 * Papel no padrão MVC:
 * - Atua como intermediário entre os Modelos (dados e regras de negócio) e as Views (telas).
 * - Centraliza a validação das requisições e a orquestração dos dados.
 * - Neste projeto sem banco de dados, executa a base automatizada de testes e repassa os resultados à View.
 */
class ControllerUsuario {

    /**
     * CONTROLE DE REQUISIÇÕES:
     * Função solicitada para validação dos dados de entrada de um login.
     * 
     * Regras aplicadas:
     * 1. E-mail e senha não podem estar em branco.
     * 2. E-mail deve possuir estrutura válida (validado por filter_var).
     * 3. Senha deve possuir ao menos 8 caracteres (validado por strlen).
     *
     * @param string $email
     * @param string $senha
     * @return array Retorna o status ('sucesso' ou 'erro') e a mensagem explicativa.
     */
    public function validar_login(string $email, string $senha): array {
        $erros = [];

        // Regra 1: Validação de campos obrigatórios vazios
        if (empty($email) || empty($senha)) {
            $erros["mensagem"] = "Preencha todos os campos.";
            $erros["status"] = "erro";
        // Regra 2 & 3 juntas: Quando ambos os campos são inválidos
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL) && strlen($senha) < 8) {
            $erros["mensagem"] = "E-mail e Senha inválidos.";
            $erros["status"] = "erro";
        // Regra 2: E-mail em formato inválido
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $erros["mensagem"] = "E-mail inválido.";
            $erros["status"] = "erro";
        // Regra 3: Senha com menos de 8 caracteres
        } elseif (strlen($senha) < 8) {
            $erros["mensagem"] = "Senha deve ter no mínimo 8 caracteres.";
            $erros["status"] = "erro";
        // Sucesso: Todos os critérios foram atendidos
        } else {
            $erros["mensagem"] = "Tudo Certo!";
            $erros["status"] = "sucesso";
        }

        return $erros;
    }

    /**
     * BATERIA DE TESTES AUTOMÁTICOS:
     * Reproduz exatamente os 5 casos de teste da pasta 'php/base-atividade.php',
     * permitindo verificar o comportamento de validar_login() sob diferentes entradas.
     *
     * @return array Lista com resultados dos testes processados.
     */
    public function executarBateriaTestesValidarLogin(): array {
        // Matriz de cenários idêntica à atividade original
        $casosTeste = [
            ['email' => '', 'senha' => '', 'descricao' => 'Ambos os campos vazios'],
            ['email' => 'teste@email.com', 'senha' => '123', 'descricao' => 'E-mail válido, senha com menos de 8 caracteres'],
            ['email' => 'testeemail', 'senha' => '12345678', 'descricao' => 'E-mail sem formato válido, senha de 8 caracteres'],
            ['email' => 'testeemail', 'senha' => '123', 'descricao' => 'E-mail inválido e senha curta'],
            ['email' => 'teste@email.com', 'senha' => '12345678', 'descricao' => 'E-mail e senha no formato válido']
        ];

        $resultados = [];
        foreach ($casosTeste as $indice => $teste) {
            // Executa a função do Controller para cada caso
            $retorno = $this->validar_login($teste['email'], $teste['senha']);
            $resultados[] = [
                'numero' => $indice + 1,
                'email' => $teste['email'],
                'senha' => $teste['senha'],
                'descricao' => $teste['descricao'],
                'status' => $retorno['status'],
                'mensagem' => $retorno['mensagem']
            ];
        }

        return $resultados;
    }

    /**
     * DEMONSTRAÇÃO DE POO (HERANÇA E ENCAPSULAMENTO):
     * Instancia dois usuários distintos (Instrutor e Aluno), configura suas propriedades herdadas
     * e específicas, define as senhas com BCRYPT, testa a verificação e chama saudacao().
     *
     * @return array Dados estruturados para envio à View.
     */
    public function processarUsuariosDemonstracao(): array {
        // 1. Instanciando Instrutor (Classe Filha)
        $instrutor = new Instrutor();
        // Acesso direto às propriedades herdadas de Usuario
        $instrutor->id = 1;
        $instrutor->nome = "Profª. Helena Flores";
        $instrutor->email = "helena.flores@jardim.edu.br";
        // Propriedade exclusiva da classe Instrutor
        $instrutor->materias_leciona = ["Programação Web MVC", "PHP Orientado a Objetos", "Arquitetura Limpa"];

        // 2. Instanciando Aluno (Classe Filha)
        $aluno = new Aluno();
        // Acesso direto às propriedades herdadas de Usuario
        $aluno->id = 2;
        $aluno->nome = "Lucas Alecrim";
        $aluno->email = "lucas.alecrim@jardim.edu.br";
        // Propriedade exclusiva da classe Aluno (iniciando em 0 e com pontos somados)
        $aluno->xp_total = 120;

        // 3. Encapsulamento: As senhas são gravadas apenas através de definirSenha() com BCRYPT
        $senhaInstrutor = "ProfJardim@2026";
        $instrutor->definirSenha($senhaInstrutor);

        $senhaAluno = "Menta#Forte88";
        $aluno->definirSenha($senhaAluno);

        // 4. Chamada do método de saudação herdado de Usuario
        $saudacaoInstrutor = $instrutor->saudacao();
        $saudacaoAluno = $aluno->saudacao();

        // 5. Verificação da senha com password_verify()
        $testeInstrutorCorreta = $instrutor->verificarSenha($senhaInstrutor);
        $testeInstrutorIncorreta = $instrutor->verificarSenha("senhaInvalida123");

        $testeAlunoCorreta = $aluno->verificarSenha($senhaAluno);
        $testeAlunoIncorreta = $aluno->verificarSenha("12345");

        // 6. Teste de validação sintática do login pelo Controller
        $validacaoLoginInstrutor = $this->validar_login($instrutor->email, $senhaInstrutor);
        $validacaoLoginAluno = $this->validar_login($aluno->email, $senhaAluno);

        return [
            'usuarios_objetos' => [
                'instrutor' => $instrutor,
                'aluno' => $aluno
            ],
            'dados_processados' => [
                'instrutor' => [
                    'tipo' => 'Instrutor',
                    'id' => $instrutor->id,
                    'nome' => $instrutor->nome,
                    'email' => $instrutor->email,
                    'materias_leciona' => $instrutor->materias_leciona,
                    'saudacao' => $saudacaoInstrutor,
                    'senha_hash' => $instrutor->obterHashSenha(),
                    'senha_cadastrada' => $senhaInstrutor,
                    'teste_verificar_correta' => $testeInstrutorCorreta,
                    'teste_verificar_incorreta' => $testeInstrutorIncorreta,
                    'validacao_login' => $validacaoLoginInstrutor
                ],
                'aluno' => [
                    'tipo' => 'Aluno',
                    'id' => $aluno->id,
                    'nome' => $aluno->nome,
                    'email' => $aluno->email,
                    'xp_total' => $aluno->xp_total,
                    'saudacao' => $saudacaoAluno,
                    'senha_hash' => $aluno->obterHashSenha(),
                    'senha_cadastrada' => $senhaAluno,
                    'teste_verificar_correta' => $testeAlunoCorreta,
                    'teste_verificar_incorreta' => $testeAlunoIncorreta,
                    'validacao_login' => $validacaoLoginAluno
                ]
            ]
        ];
    }

    /**
     * SIMULAÇÃO DE LOGIN INTERATIVO:
     * Recebe requisições POST do formulário, processa a validação pelo Controller
     * e confere o hash de senha através do Encapsulamento.
     *
     * @param array $usuarios
     * @return array|null Retorna os detalhes do processamento ou nulo se não houver envio.
     */
    public function processarSimulacaoLogin(array $usuarios): ?array {
        // Verifica se a requisição é do tipo POST para login
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['acao']) || $_POST['acao'] !== 'simular_login') {
            return null;
        }

        $email = trim($_POST['email'] ?? '');
        $senha = trim($_POST['senha'] ?? '');

        // Passo 1: O Controller valida a sintaxe dos campos digitados
        $validacaoFormato = $this->validar_login($email, $senha);

        $resultado = [
            'email_informado' => $email,
            'senha_informada' => $senha,
            'validacao_formato' => $validacaoFormato,
            'usuario_encontrado' => null,
            'senha_valida' => false,
            'mensagem_final' => ''
        ];

        // Se houver erro de formato, interrompe e retorna a mensagem
        if ($validacaoFormato['status'] === 'erro') {
            $resultado['mensagem_final'] = "Falha no login: " . $validacaoFormato['mensagem'];
            return $resultado;
        }

        // Passo 2: Localiza o usuário na lista simulada em memória
        $usuarioEncontrado = null;
        foreach ($usuarios as $usr) {
            if (strtolower($usr->email) === strtolower($email)) {
                $usuarioEncontrado = $usr;
                break;
            }
        }

        if ($usuarioEncontrado === null) {
            $resultado['mensagem_final'] = "Atenção: E-mail não localizado na base de usuários de teste.";
            return $resultado;
        }

        $resultado['usuario_encontrado'] = $usuarioEncontrado->nome . " (" . $usuarioEncontrado->tipo . ")";

        // Passo 3: Confere a senha através do método seguro verificarSenha()
        if ($usuarioEncontrado->verificarSenha($senha)) {
            $resultado['senha_valida'] = true;
            $resultado['mensagem_final'] = "Autenticação aprovada com sucesso! " . $usuarioEncontrado->saudacao();
        } else {
            $resultado['senha_valida'] = false;
            $resultado['mensagem_final'] = "Senha incorreta para o usuário informado.";
        }

        return $resultado;
    }

    /**
     * Ação principal em Português: reúne os dados processados e renderiza a View.
     */
    public function iniciar(): void {
        // Coleta e processa os dados de teste e das entidades
        $testesAutomaticos = $this->executarBateriaTestesValidarLogin();
        $demonstracao = $this->processarUsuariosDemonstracao();
        $simulacaoLogin = $this->processarSimulacaoLogin($demonstracao['usuarios_objetos']);

        // Variáveis disponibilizadas para a View (HTML)
        $usuarios = $demonstracao['dados_processados'];
        $testes = $testesAutomaticos;
        $loginInterativo = $simulacaoLogin;

        // Carrega a View responsável pela apresentação gráfica
        require __DIR__ . '/../views/usuario/index.php';
    }

    /**
     * Método index padrão para compatibilidade com rotas MVC.
     */
    public function index(): void {
        $this->iniciar();
    }
}
