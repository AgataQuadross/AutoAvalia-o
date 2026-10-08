<?php
/**
 * Atividade 09 - Tarde 12
 * 
 * Modelo: Usuario (com persistência em banco de dados SQL via PDO)
 * Baseado no modelo POO desenvolvido no repositório AutoAvaliação.
 * 
 * Referência de banco de dados: pasta /database (arquivo schema.sql).
 * Compatível com versões legadas e modernas do PHP (PHP 5.6, PHP 7.x e PHP 8.x).
 * 
 * Pilares implementados:
 * 1. Herança: 'Aluno' e 'Instrutor' estendem a classe base 'Usuario'.
 * 2. Encapsulamento: $senha_hash privada, com escrita via definirSenha() (BCRYPT) e verificação via verificarSenha().
 * 3. Persistência SQL (Prepared Statements):
 *    - $usuario->salvar($pdo): insere na tabela 'usuarios' e tabelas específicas conforme schema.sql.
 *    - Usuario::buscarPorEmail($pdo, $email): método estático que busca por e-mail e retorna objeto Usuario ou null.
 */

class Usuario {
    /**
     * Identificador do usuário.
     * @var int|null
     */
    public $id = null;

    /**
     * Alias de id para alinhamento com a coluna id_usuario do schema.sql.
     * @var int|null
     */
    public $id_usuario = null;

    /**
     * Nome do usuário.
     * @var string
     */
    public $nome = '';

    /**
     * E-mail do usuário.
     * @var string
     */
    public $email = '';

    /**
     * Tipo do usuário ('aluno' ou 'instrutor').
     * @var string
     */
    public $tipo = 'aluno';

    /**
     * ENCAPSULAMENTO: A propriedade $senha_hash é privada.
     * Nenhuma classe externa ou filha pode ler ou alterar essa propriedade diretamente.
     * @var string|null
     */
    private $senha_hash = null;

    /**
     * Construtor da classe base Usuario.
     * 
     * @param int|null $id
     * @param string $nome
     * @param string $email
     * @param string $tipo
     */
    public function __construct($id = null, $nome = '', $email = '', $tipo = 'aluno') {
        $this->id = ($id !== null) ? (int)$id : null;
        $this->id_usuario = $this->id;
        $this->nome = (string)$nome;
        $this->email = (string)$email;
        $this->tipo = (string)$tipo;
    }

    /**
     * Retorna a saudação padrão herdada por todos os tipos de usuários.
     * 
     * @return string
     */
    public function saudacao() {
        return "Olá, " . $this->nome . "!";
    }

    /**
     * ENCAPSULAMENTO - Escrita segura:
     * Recebe a senha em texto puro e gera o hash criptográfico seguro usando BCRYPT.
     * 
     * @param string $senha
     * @return void
     */
    public function definirSenha($senha) {
        $this->senha_hash = password_hash((string)$senha, PASSWORD_BCRYPT);
    }

    /**
     * ENCAPSULAMENTO - Validação segura:
     * Compara a senha digitada com o hash protegido utilizando a função nativa password_verify().
     * 
     * @param string $senha
     * @return bool
     */
    public function verificarSenha($senha) {
        if (empty($this->senha_hash)) {
            return false;
        }
        return password_verify((string)$senha, $this->senha_hash);
    }

    /**
     * Método para recuperação educacional do hash armazenado.
     * 
     * @return string|null
     */
    public function obterHashSenha() {
        return $this->senha_hash;
    }

    /**
     * Método auxiliar para restaurar o hash do banco de dados no objeto.
     * 
     * @param string|null $hash
     * @return void
     */
    public function definirHashDireto($hash) {
        $this->senha_hash = $hash;
    }

    /**
     * Mantém compatibilidade com chamadas anteriores.
     * 
     * @return string|null
     */
    public function getSenhaHash() {
        return $this->obterHashSenha();
    }

    /**
     * REQUISITO OBRIGATÓRIO:
     * Insere o usuário no banco de dados usando prepared statement,
     * respeitando as tabelas e relacionamentos definidos em /database/schema.sql,
     * e atualiza $this->id com o ID gerado pelo banco.
     *
     * @param PDO $pdo Conexão PDO com o banco de dados SQL.
     * @return bool Retorna true em caso de sucesso ou false em caso de falha.
     */
    public function salvar(PDO $pdo) {
        // Garante que a estrutura referenciada na pasta database esteja criada
        self::garantirTabelas($pdo);

        $tipoDb = (strtolower($this->tipo) === 'instrutor') ? 'instrutor' : 'aluno';
        $emTransacao = false;

        try {
            $emTransacao = $pdo->beginTransaction();
        } catch (Exception $e) {
            $emTransacao = false;
        }

        try {
            // 1. Inserção na tabela principal 'usuarios' definida no schema.sql
            $sqlUsuarios = "INSERT INTO usuarios (nome, email, senha_hash, tipo_usuario) VALUES (:nome, :email, :senha_hash, :tipo_usuario)";
            $stmtUsuarios = $pdo->prepare($sqlUsuarios);
            $sucesso = $stmtUsuarios->execute(array(
                ':nome'         => $this->nome,
                ':email'        => $this->email,
                ':senha_hash'   => (string)$this->obterHashSenha(),
                ':tipo_usuario' => $tipoDb
            ));

            if (!$sucesso) {
                if ($emTransacao && $pdo->inTransaction()) {
                    $pdo->rollBack();
                }
                return false;
            }

            $idGerado = (int)$pdo->lastInsertId();
            $this->id = $idGerado;
            $this->id_usuario = $idGerado;

            // 2. Tabela filha 'alunos' (conforme /database/schema.sql)
            if ($this instanceof Aluno) {
                $matricula = !empty($this->matricula)
                    ? $this->matricula
                    : ('MAT-' . str_pad($idGerado, 5, '0', STR_PAD_LEFT));
                $this->matricula = $matricula;
                $xp = isset($this->xp_total) ? (int)$this->xp_total : 0;

                $stmtAluno = $pdo->prepare("INSERT INTO alunos (id_usuario, matricula, xp_total) VALUES (:id_usuario, :matricula, :xp_total)");
                $stmtAluno->execute(array(
                    ':id_usuario' => $idGerado,
                    ':matricula'  => $matricula,
                    ':xp_total'   => $xp
                ));
            }
            // 3. Tabela filha 'instrutores' (conforme /database/schema.sql)
            elseif ($this instanceof Instrutor) {
                $stmtInstrutor = $pdo->prepare("INSERT INTO instrutores (id_usuario) VALUES (:id_usuario)");
                $stmtInstrutor->execute(array(
                    ':id_usuario' => $idGerado
                ));

                if (!empty($this->materias_leciona) && is_array($this->materias_leciona)) {
                    foreach ($this->materias_leciona as $nomeMateria) {
                        $nomeMateria = trim($nomeMateria);
                        if ($nomeMateria === '') {
                            continue;
                        }

                        $stmtBuscaMat = $pdo->prepare("SELECT id_materia FROM materias WHERE nome_materia = :nome LIMIT 1");
                        $stmtBuscaMat->execute(array(':nome' => $nomeMateria));
                        $idMateria = $stmtBuscaMat->fetchColumn();

                        if (!$idMateria) {
                            $stmtInsMat = $pdo->prepare("INSERT INTO materias (nome_materia) VALUES (:nome)");
                            $stmtInsMat->execute(array(':nome' => $nomeMateria));
                            $idMateria = (int)$pdo->lastInsertId();
                        }

                        if ($idMateria) {
                            $stmtRel = $pdo->prepare("INSERT INTO materias_instrutores (id_materia, id_instrutor) VALUES (:id_materia, :id_instrutor)");
                            $stmtRel->execute(array(
                                ':id_materia'   => $idMateria,
                                ':id_instrutor' => $idGerado
                            ));
                        }
                    }
                }
            }

            if ($emTransacao && $pdo->inTransaction()) {
                $pdo->commit();
            }

            return true;
        } catch (Exception $e) {
            if ($emTransacao && $pdo->inTransaction()) {
                $pdo->rollBack();
            }

            // Fallback caso a tabela 'usuarios' seja uma versão anterior de teste
            try {
                $sqlPadrao = "INSERT INTO usuarios (nome, email, tipo, senha_hash) VALUES (:nome, :email, :tipo, :senha_hash)";
                $stmt = $pdo->prepare($sqlPadrao);
                $sucesso = $stmt->execute(array(
                    ':nome'       => $this->nome,
                    ':email'      => $this->email,
                    ':tipo'       => $this->tipo,
                    ':senha_hash' => (string)$this->obterHashSenha()
                ));
                if ($sucesso) {
                    $this->id = (int)$pdo->lastInsertId();
                    $this->id_usuario = $this->id;
                    return true;
                }
            } catch (Exception $e2) {
                return false;
            }

            return false;
        }
    }

    /**
     * REQUISITO OBRIGATÓRIO (método static):
     * Busca por e-mail no banco de dados e retorna um objeto Usuario preenchido,
     * ou null se não encontrar. Utiliza as tabelas do /database/schema.sql.
     *
     * @param PDO $pdo Conexão PDO com o banco de dados SQL.
     * @param string $email E-mail a ser pesquisado.
     * @return Usuario|null Objeto preenchido com os dados ou null se inexistente.
     */
    public static function buscarPorEmail(PDO $pdo, $email) {
        $dados = null;

        try {
            // Busca referenciando a estrutura oficial de /database/schema.sql
            $sql = "SELECT u.id_usuario, u.nome, u.email, u.senha_hash, u.tipo_usuario,
                           a.matricula, a.xp_total
                    FROM usuarios u
                    LEFT JOIN alunos a ON a.id_usuario = u.id_usuario
                    WHERE u.email = :email
                    LIMIT 1";
            $stmt = $pdo->prepare($sql);
            $stmt->execute(array(':email' => (string)$email));
            $dados = $stmt->fetch(PDO::FETCH_ASSOC);

            // Se não encontrou, tenta fallback na estrutura de coluna única
            if (!$dados) {
                $sqlLegado = "SELECT * FROM usuarios WHERE email = :email LIMIT 1";
                $stmtLegado = $pdo->prepare($sqlLegado);
                $stmtLegado->execute(array(':email' => (string)$email));
                $dadosLegado = $stmtLegado->fetch(PDO::FETCH_ASSOC);
                if ($dadosLegado) {
                    $dados = $dadosLegado;
                }
            }
        } catch (Exception $e) {
            // Fallback para tabelas de desenvolvimento
            try {
                $sqlLegado = "SELECT * FROM usuarios WHERE email = :email LIMIT 1";
                $stmtLegado = $pdo->prepare($sqlLegado);
                $stmtLegado->execute(array(':email' => (string)$email));
                $dados = $stmtLegado->fetch(PDO::FETCH_ASSOC);
            } catch (Exception $e2) {
                return null;
            }
        }

        if (!$dados) {
            return null;
        }

        $id = isset($dados['id_usuario']) ? (int)$dados['id_usuario'] : (isset($dados['id']) ? (int)$dados['id'] : null);
        $nome = isset($dados['nome']) ? (string)$dados['nome'] : '';
        $email = isset($dados['email']) ? (string)$dados['email'] : '';
        $tipo = isset($dados['tipo_usuario']) ? $dados['tipo_usuario'] : (isset($dados['tipo']) ? $dados['tipo'] : 'aluno');
        $tipoNorm = strtolower((string)$tipo);

        if ($tipoNorm === 'aluno') {
            $xpTotal = isset($dados['xp_total']) ? (int)$dados['xp_total'] : 0;
            $matricula = isset($dados['matricula']) ? (string)$dados['matricula'] : '';
            $usuario = new Aluno($id, $nome, $email, $xpTotal, $matricula);
        } elseif ($tipoNorm === 'instrutor') {
            $materias = array();
            if ($id) {
                try {
                    $stmtMat = $pdo->prepare("SELECT m.nome_materia 
                                              FROM materias_instrutores mi 
                                              JOIN materias m ON m.id_materia = mi.id_materia 
                                              WHERE mi.id_instrutor = :id_usuario");
                    $stmtMat->execute(array(':id_usuario' => $id));
                    $materias = $stmtMat->fetchAll(PDO::FETCH_COLUMN);
                } catch (Exception $eMat) {
                    $materias = array();
                }
            }
            if (empty($materias) && !empty($dados['materias_leciona'])) {
                $materias = is_array($dados['materias_leciona'])
                    ? $dados['materias_leciona']
                    : array_map('trim', explode(',', $dados['materias_leciona']));
            }
            $usuario = new Instrutor($id, $nome, $email, $materias);
        } else {
            $usuario = new Usuario($id, $nome, $email, $tipo);
        }

        $usuario->id = $id;
        $usuario->id_usuario = $id;

        if (!empty($dados['senha_hash'])) {
            $usuario->definirHashDireto($dados['senha_hash']);
        }

        return $usuario;
    }
}

/**
 * HERANÇA: Classe Aluno herda todas as propriedades e métodos de Usuario.
 * Possui as propriedades específicas $xp_total e $matricula conforme /database/schema.sql.
 */
class Aluno extends Usuario {
    /**
     * @var string
     */
    public $matricula = '';

    /**
     * @var int
     */
    public $xp_total = 0;

    /**
     * @param int|null $id
     * @param string $nome
     * @param string $email
     * @param int $xp_total
     * @param string $matricula
     */
    public function __construct($id = null, $nome = '', $email = '', $xp_total = 0, $matricula = '') {
        parent::__construct($id, $nome, $email, 'aluno');
        $this->xp_total = (int)$xp_total;
        $this->matricula = (string)$matricula;
    }
}

/**
 * HERANÇA: Classe Instrutor herda todas as propriedades e métodos de Usuario.
 * Possui a propriedade específica $materias_leciona (array).
 */
class Instrutor extends Usuario {
    /**
     * @var array
     */
    public $materias_leciona = array();

    /**
     * @param int|null $id
     * @param string $nome
     * @param string $email
     * @param array $materias_leciona
     */
    public function __construct($id = null, $nome = '', $email = '', $materias_leciona = array()) {
        parent::__construct($id, $nome, $email, 'instrutor');
        $this->materias_leciona = is_array($materias_leciona) ? $materias_leciona : array();
    }
}
