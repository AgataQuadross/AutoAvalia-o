<?php
echo "
<hr>
<h2>Desafio Prático: Função validar_login()</h2>";

/**
* Função para validar os dados de entrada de um login.
*
* Regras:
* 1. O e-mail e a senha não podem estar vazios.
* 2. O e-mail deve ter um formato válido (dica: use filter_var).
* 3. A senha deve ter no mínimo 8 caracteres (dica: use strlen), letras maiusculas e minusculas, e caracter
* especial.
*
* @param string $email
* @param string $senha
* @return array Retorna um array com o 'status' (sucesso ou erro) e a 'mensagem'.
*/


function validar_login(string $email, string $senha): array {
    // Desenvolva o código da função aqui, seguindo as regras acima.
    $erros = [];

    if (empty($email) || empty($senha)){// validando vazios
        $erros["mensagem"] = "Preencha todos os campos.";
        $erros["status"] = "erro";
    }elseif(!filter_var($email, FILTER_VALIDATE_EMAIL) && strlen($senha) < 8){
        $erros["mensagem"] = "E-mail e Senha inválidos.";
        $erros["status"] = "erro";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erros["mensagem"] = "E-mail inválido.";
        $erros["status"] = "erro";
    } elseif (strlen($senha) < 8){
        $erros["mensagem"] = "Senha deve ter no mínimo 8 caracteres." ;
        $erros["status"] = "erro";
    } else{
        $erros["mensagem"] = "Tudo Certo!";
        $erros["status"] = "sucesso";
    }

    return $erros;
} 

// ÁREA DE TESTES (Simulandoo envio de um formulário) 
    $testes = [
        ['email' => '' , 'senha' => ''],
        ['email' => 'teste@email.com', 'senha' => '123'],
        ['email' => 'testeemail', 'senha' => '12345678'],
        ['email' => 'testeemail', 'senha' => '123'],
        ['email' => 'teste@email.com', 'senha' => '12345678']
    ];

    echo "<h3>Resultados dos Testes:</h3><ul>";
    foreach ($testes as $i => $teste) {
    $resultado = validar_login($teste['email'], $teste['senha']);
    $cor = $resultado['status'] === 'sucesso' ? 'green' : 'red';

    echo "<li>";
        echo "<strong>Teste " . ($i + 1) . "</strong> ({$teste['email']} / {$teste['senha']}): <br>";
        echo "<span style='color: {$cor};'>[{$resultado['status']}] {$resultado['mensagem']}</span>";
        echo "</li><br>";
    }
    echo "</ul>";

    // Informação Extra: Como o PHP lida com senhas
    /*
    Nós nunca salvamos a senha em texto puro!
    Quando o formulário for validado com sucesso, o sistema faria algo assim:

    $senhaHashDoBanco = password_hash("SenhaForte123", PASSWORD_DEFAULT);
    $senhaDigitadaCorreta = password_verify($teste['senha'], $senhaHashDoBanco);
    */

?>