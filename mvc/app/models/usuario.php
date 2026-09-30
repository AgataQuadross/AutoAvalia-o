<?php
/**
 * Modelo Base: Usuario
 * 
 * Demonstra os conceitos fundamentais de Programação Orientada a Objetos (POO):
 * 1. Abstração e Herança: Serve como classe mãe para 'Aluno' e 'Instrutor'.
 * 2. Encapsulamento: Protege dados sensíveis (a senha nunca é exposta em texto puro).
 */
class Usuario {
    // Propriedades herdáveis acessíveis diretamente pelas classes filhas
    public int $id;
    public string $nome;
    public string $email;
    public string $tipo;

    // ENCAPSULAMENTO: A propriedade $senha_hash é privada.
    // Nenhuma classe externa ou filha pode ler ou alterar essa propriedade diretamente.
    private ?string $senha_hash = null;

    /**
     * Construtor da classe base Usuario.
     */
    public function __construct(int $id = 0, string $nome = '', string $email = '', string $tipo = 'Usuário') {
        $this->id = $id;
        $this->nome = $nome;
        $this->email = $email;
        $this->tipo = $tipo;
    }

    /**
     * Retorna a saudação padrão herdada por todos os tipos de usuários.
     */
    public function saudacao(): string {
        return "Olá, {$this->nome}!";
    }

    /**
     * ENCAPSULAMENTO - Escrita segura:
     * Recebe a senha em texto puro e gera o hash criptográfico seguro usando BCRYPT.
     * O texto original da senha é descartado da memória após o cálculo.
     */
    public function definirSenha(string $senha): void {
        $this->senha_hash = password_hash($senha, PASSWORD_BCRYPT);
    }

    /**
     * ENCAPSULAMENTO - Validação segura:
     * Compara a senha digitada com o hash protegido utilizando a função nativa password_verify().
     * Retorna booleano (true para correta, false para divergente).
     */
    public function verificarSenha(string $senha): bool {
        if (empty($this->senha_hash)) {
            return false;
        }
        return password_verify($senha, $this->senha_hash);
    }

    /**
     * Método em Português para visualização educacional do hash armazenado.
     */
    public function obterHashSenha(): ?string {
        return $this->senha_hash;
    }

    /**
     * Mantém compatibilidade com chamadas anteriores
     */
    public function getSenhaHash(): ?string {
        return $this->obterHashSenha();
    }
}

/**
 * HERANÇA: Classe Aluno herda todas as propriedades e métodos de Usuario.
 * Possui a propriedade pública específica $xp_total (iniciando em 0).
 */
class Aluno extends Usuario {
    public int $xp_total = 0;

    public function __construct(int $id = 0, string $nome = '', string $email = '', int $xp_total = 0) {
        parent::__construct($id, $nome, $email, 'Aluno');
        $this->xp_total = $xp_total;
    }
}

/**
 * HERANÇA: Classe Instrutor herda todas as propriedades e métodos de Usuario.
 * Possui a propriedade pública específica $materias_leciona (array).
 */
class Instrutor extends Usuario {
    public array $materias_leciona = [];

    public function __construct(int $id = 0, string $nome = '', string $email = '', array $materias_leciona = []) {
        parent::__construct($id, $nome, $email, 'Instrutor');
        $this->materias_leciona = $materias_leciona;
    }
}