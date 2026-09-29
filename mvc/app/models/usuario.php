<?php
public class Usuario(){
    private int $id;
    public string $nome;
    private string $email;
    public string $tipo;

    function saudacao(): string {
        return "Olá, {$this->nome}!";
    }
}

class Aluno() extends Usuario() {
    public int xp_total;
}

class Instrutor() extends Usuario() {
    public array materias_leciona;
}

// validações
?>