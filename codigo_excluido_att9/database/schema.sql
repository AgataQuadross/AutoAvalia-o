-- =====================================================================
-- The Farmer Edu — schema.sql
-- SENAI · Aprendizagem em Assistente de Programação Web
-- Banco: MySQL / MariaDB
--
-- Este script cria o banco a partir do modelo de dados revisado
-- (seção 6 da Documentação Oficial do Sistema). Cada tabela referencia
-- a entidade correspondente do diagrama de classes entre colchetes.
--
-- Ordem de criação respeita as dependências de chave estrangeira —
-- execute o arquivo de cima para baixo, sem pular trechos.
-- =====================================================================

CREATE DATABASE IF NOT EXISTS the_farmer_edu
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE the_farmer_edu;

-- ---------------------------------------------------------------------
-- 1. USUÁRIOS  [Usuario]
-- Tabela base de autenticação. Instrutor e Aluno herdam dela
-- (uma linha em "usuarios" + uma linha na tabela específica do perfil).
-- ---------------------------------------------------------------------
CREATE TABLE usuarios (
  id_usuario     INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nome           VARCHAR(150)    NOT NULL,
  email          VARCHAR(150)    NOT NULL,
  senha_hash     VARCHAR(255)    NOT NULL,   -- gerado com password_hash() do PHP
  tipo_usuario   ENUM('instrutor', 'aluno') NOT NULL,
  criado_em      TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_usuarios_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 2. INSTRUTORES  [Instrutor]
-- ---------------------------------------------------------------------
CREATE TABLE instrutores (
  id_usuario     INT UNSIGNED PRIMARY KEY,
  CONSTRAINT fk_instrutores_usuario
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE materias (
  id_materia     INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nome_materia   VARCHAR(100)    NOT NULL,
  descricao      TEXT            NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE materias_instrutores (
  id_materia     INT UNSIGNED NOT NULL,
  id_instrutor   INT UNSIGNED NOT NULL,
  PRIMARY KEY (id_materia, id_instrutor),
  CONSTRAINT fk_materias_instrutores_instrutor
    FOREIGN KEY (id_instrutor) REFERENCES instrutores(id_usuario)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_materias_instrutores_materia
    FOREIGN KEY (id_materia) REFERENCES materias(id_materia)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 3. ALUNOS  [Aluno]
-- ---------------------------------------------------------------------
CREATE TABLE alunos (
  id_usuario     INT UNSIGNED PRIMARY KEY,
  matricula      VARCHAR(30)     NOT NULL,
  xp_total       INT UNSIGNED    NOT NULL DEFAULT 0,
  CONSTRAINT fk_alunos_usuario
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario)
    ON DELETE CASCADE ON UPDATE CASCADE,
  UNIQUE KEY uq_alunos_matricula (matricula)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 4. CURSOS  [Curso]
-- ---------------------------------------------------------------------
CREATE TABLE cursos (
  id_curso       INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nome_curso     VARCHAR(150)    NOT NULL,
  descricao      TEXT            NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 5. TURMAS  [Turma]
-- Uma turma pertence a um curso e tem um instrutor responsável.
-- ---------------------------------------------------------------------
CREATE TABLE turmas (
  id_turma       INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nome_turma     VARCHAR(100)    NOT NULL,
  turno          ENUM('manha', 'tarde', 'noite') NOT NULL,
  id_curso       INT UNSIGNED    NOT NULL,
  id_instrutor   INT UNSIGNED    NOT NULL,
  CONSTRAINT fk_turmas_curso
    FOREIGN KEY (id_curso) REFERENCES cursos(id_curso)
    ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT fk_turmas_instrutor
    FOREIGN KEY (id_instrutor) REFERENCES instrutores(id_usuario)
    ON DELETE RESTRICT ON UPDATE CASCADE,
  INDEX idx_turmas_curso (id_curso)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 6. MATRÍCULAS  (associativa Turma N—N Aluno, implícita no diagrama)
-- Existe como tabela própria para guardar a data de matrícula e evitar
-- que um aluno seja matriculado duas vezes na mesma turma.
-- ---------------------------------------------------------------------
CREATE TABLE matriculas (
  id_matricula     INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  id_turma         INT UNSIGNED  NOT NULL,
  id_aluno         INT UNSIGNED  NOT NULL,
  data_matricula   TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_matriculas_turma
    FOREIGN KEY (id_turma) REFERENCES turmas(id_turma)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_matriculas_aluno
    FOREIGN KEY (id_aluno) REFERENCES alunos(id_usuario)
    ON DELETE CASCADE ON UPDATE CASCADE,
  UNIQUE KEY uq_matricula_turma_aluno (id_turma, id_aluno)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 7. ATIVIDADES  [Atividade]
-- resposta_referencia e criterios_correcao alimentam a comparação da IA
-- (RF06/RF07/RF14). linguagem é usada para mapear o language_id do Judge0
-- quando tipo_atividade = 'codigo'.
-- ---------------------------------------------------------------------
CREATE TABLE atividades (
  id_atividade         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  id_turma             INT UNSIGNED  NOT NULL,
  titulo               VARCHAR(150)  NOT NULL,
  descricao            TEXT          NULL,
  tipo_atividade       ENUM('codigo', 'quiz', 'texto') NOT NULL DEFAULT 'codigo',
  linguagem            VARCHAR(30)   NULL,
  resposta_referencia  TEXT          NULL,
  criterios_correcao   JSON          NULL,
  data_entrega         DATETIME      NOT NULL,
  criado_em            TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_atividades_turma
    FOREIGN KEY (id_turma) REFERENCES turmas(id_turma)
    ON DELETE RESTRICT ON UPDATE CASCADE,
  INDEX idx_atividades_turma (id_turma)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 8. SUBMISSÕES  [Submissao]
-- Uma linha por tentativa — é aqui (e não em Atividade) que mora a
-- resposta e o vínculo com o aluno, permitindo notas diferentes por
-- aluno na mesma atividade e histórico de tentativas (RF13/RN07).
-- ---------------------------------------------------------------------
CREATE TABLE submissoes (
  id_submissao        INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  id_atividade         INT UNSIGNED  NOT NULL,
  id_aluno             INT UNSIGNED  NOT NULL,
  conteudo_resposta    LONGTEXT      NOT NULL,
  tentativa_num        INT UNSIGNED  NOT NULL DEFAULT 1,
  status               ENUM('enviada', 'processando', 'corrigida', 'erro') NOT NULL DEFAULT 'enviada',
  resultado_execucao   TEXT          NULL,     -- stdout/stderr/veredito retornados pelo Judge0
  data_envio           TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_submissoes_atividade
    FOREIGN KEY (id_atividade) REFERENCES atividades(id_atividade)
    ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT fk_submissoes_aluno
    FOREIGN KEY (id_aluno) REFERENCES alunos(id_usuario)
    ON DELETE RESTRICT ON UPDATE CASCADE,
  UNIQUE KEY uq_submissao_tentativa (id_atividade, id_aluno, tentativa_num),
  INDEX idx_submissoes_aluno (id_aluno)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 9. FEEDBACK DA IA  [FeedbackIA]  — 1:0..1 com Submissão
-- ---------------------------------------------------------------------
CREATE TABLE feedback_ia (
  id_feedback        INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  id_submissao       INT UNSIGNED  NOT NULL,
  pontos_positivos   TEXT          NULL,
  pontos_negativos   TEXT          NULL,
  sugestao_resposta  TEXT          NULL,
  gerado_em          TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_feedback_submissao
    FOREIGN KEY (id_submissao) REFERENCES submissoes(id_submissao)
    ON DELETE CASCADE ON UPDATE CASCADE,
  UNIQUE KEY uq_feedback_submissao (id_submissao)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 10. NOTAS  [Nota]  — 1:0..1 com Submissão
-- origem='ia' até que um instrutor valide (RF06/RN04); nesse momento
-- passa para origem='instrutor' e id_instrutor_revisor é preenchido.
-- ---------------------------------------------------------------------
CREATE TABLE notas (
  id_nota                INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  id_submissao           INT UNSIGNED   NOT NULL,
  valor                  DECIMAL(4,2)   NOT NULL,
  origem                 ENUM('ia', 'instrutor') NOT NULL DEFAULT 'ia',
  id_instrutor_revisor   INT UNSIGNED   NULL,
  data_atribuicao        TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_notas_submissao
    FOREIGN KEY (id_submissao) REFERENCES submissoes(id_submissao)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_notas_revisor
    FOREIGN KEY (id_instrutor_revisor) REFERENCES instrutores(id_usuario)
    ON DELETE SET NULL ON UPDATE CASCADE,
  UNIQUE KEY uq_nota_submissao (id_submissao),
  CONSTRAINT chk_notas_valor CHECK (valor >= 0 AND valor <= 10)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 11. FAZENDAS  [Fazenda]  — 1:1 com Aluno
-- ---------------------------------------------------------------------
CREATE TABLE fazendas (
  id_fazenda      INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  id_aluno        INT UNSIGNED  NOT NULL,
  nivel           INT UNSIGNED  NOT NULL DEFAULT 1,
  estado_visual   VARCHAR(50)   NOT NULL DEFAULT 'inicial',
  atualizado_em   TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP
                                  ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_fazendas_aluno
    FOREIGN KEY (id_aluno) REFERENCES alunos(id_usuario)
    ON DELETE CASCADE ON UPDATE CASCADE,
  UNIQUE KEY uq_fazenda_aluno (id_aluno)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 12. ELEMENTOS DA FAZENDA  [ElementoFazenda]
-- Construção, plantação e animal unificados em uma tabela com "tipo",
-- para simplificar consultas; separe em tabelas próprias apenas se
-- cada tipo ganhar atributos muito diferentes entre si.
-- ---------------------------------------------------------------------
CREATE TABLE elementos_fazenda (
  id_elemento    INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  id_fazenda     INT UNSIGNED  NOT NULL,
  tipo           ENUM('construcao', 'plantacao', 'animal') NOT NULL,
  nivel          INT UNSIGNED  NOT NULL DEFAULT 1,
  criado_em      TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_elementos_fazenda
    FOREIGN KEY (id_fazenda) REFERENCES fazendas(id_fazenda)
    ON DELETE CASCADE ON UPDATE CASCADE,
  INDEX idx_elementos_fazenda (id_fazenda)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 13. HISTÓRICO DE XP  [XPLog]
-- quantidade aceita valores negativos para representar retrocesso
-- (RF19). id_submissao é opcional pois nem todo XP vem de uma atividade.
-- ---------------------------------------------------------------------
CREATE TABLE xp_logs (
  id_xp          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  id_aluno       INT UNSIGNED  NOT NULL,
  quantidade     INT           NOT NULL,
  motivo         VARCHAR(255)  NOT NULL,
  id_submissao   INT UNSIGNED  NULL,
  data           TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_xplogs_aluno
    FOREIGN KEY (id_aluno) REFERENCES alunos(id_usuario)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_xplogs_submissao
    FOREIGN KEY (id_submissao) REFERENCES submissoes(id_submissao)
    ON DELETE SET NULL ON UPDATE CASCADE,
  INDEX idx_xplogs_aluno (id_aluno)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 14. NOTIFICAÇÕES  (extensão — mapeia o módulo "notificacoes" da
-- arquitetura; não estava no diagrama de classes original, mas é
-- necessária para RN sobre avisos de atividade pendente/nota).
-- ---------------------------------------------------------------------
CREATE TABLE notificacoes (
  id_notificacao   INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  id_usuario       INT UNSIGNED  NOT NULL,
  tipo             VARCHAR(50)   NOT NULL,   -- ex.: 'atividade_pendente', 'nota_atribuida'
  mensagem         VARCHAR(255)  NOT NULL,
  lida             BOOLEAN       NOT NULL DEFAULT FALSE,
  criado_em        TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_notificacoes_usuario
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario)
    ON DELETE CASCADE ON UPDATE CASCADE,
  INDEX idx_notificacoes_usuario (id_usuario, lida)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;