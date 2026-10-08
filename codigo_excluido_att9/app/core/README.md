# `core` — uso restrito

Este módulo é a base compartilhada por todos os squads. **Não editem estes
arquivos diretamente** — qualquer mudança aqui afeta todo mundo. Se precisar
de algo novo no `core`, abram uma Issue e conversem com o instrutor.

## Estrutura

```
app/
├── core/
│   ├── config.php     # configuração de conexão com o banco
│   ├── database.php   # iniciarPDO(): retorna a conexão PDO única
│   ├── session.php     # login, logout, usuarioLogado(), exigirLogin()
│   └── router.php      # enviarRota(): resolve ?rota=modulo/acao
└── modules/
    ├── home.php         # exemplo mínimo (ver este arquivo como referência)
    ├── auth/             # squad 10
    ├── turmas_cursos/    # squad 4
    ├── atividades/       # squad 6
    ├── submissoes/       # squads 2 e 12
    ├── feedback_ia/      # squads 8 e 9
    ├── notas/            # squad 3
    ├── gamificacao/      # squad 11
    ├── fazenda/          # squads 1 e 7
    └── notificacoes/     # squads 5 e 13

public/
└── index.php   # ponto único de entrada — tudo passa por aqui
```

## Como criar uma página no seu módulo

1. Criem o arquivo VIEW em `app/modules/<seu-modulo>/views/<acao>.php`.
2. Criem ou modifiquem o MODEL em `app/modules/<seu-modulo>/Model.php`.
3. Criem ou modifiquem o Controller em `app/modules/<seu-modulo>/Controller.php`.
4. Adicionem a rota para o arquivo dentro de `app/modules/<seu-modulo>/routes.php`,
5. Acessem via `/<seu-modulo>/<acao>` (ex.: `/atividades/listar`).
6. Dentro do arquivo, vocês já têm disponíveis (sem precisar de `require`):

Exemplo:
```php
<?php
// Pegar o usuário logado (ou null se ninguém estiver logado)
$usuario = usuarioLogado();

// Exigir que esteja logado (redireciona para o login se não estiver)
solicitarLogin();

// Exigir um perfil específico
solicitarPerfilDeAcesso('instrutor'); // ou 'aluno'

// Pegar a conexão com o banco
$pdo = iniciarPDO();
$stmt = $pdo->prepare('SELECT * FROM atividades WHERE id_turma = ?');
$stmt->execute([$idTurma]);
$atividades = $stmt->fetchAll();
```

## Rodando local

- Abram o XAMPP, iniciem o Apache e o MySQL
- Acessem `http://localhost/The-Famer-Edu/?rota=home` (ou `http://localhost:8000/`
para a rota padrão).

## Regras importantes

- **Nunca** criem uma nova conexão PDO — sempre usem `conectar()`.
- **Nunca** montem SQL concatenando variáveis — sempre prepared statements
  (`?` ou `:nome`), como no Encontro 3.
- **Nunca** acessem um módulo direto pela URL/arquivo — sempre pela rota
  (`?rota=...`), para manter o roteador como porta única de entrada.
- Dúvidas ou necessidade de mudança no `core`: abram uma Issue.
