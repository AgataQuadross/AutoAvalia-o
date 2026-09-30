<?php
/**
 * Ponto de Entrada da Aplicação MVC (Front Controller)
 * 
 * Funcionamento:
 * 1. Carrega os Modelos (Usuario, Aluno, Instrutor) com encapsulamento e herança.
 * 2. Carrega o ControllerUsuario que gerencia as validações e requisições.
 * 3. Cria o objeto controlador e inicia a montagem dos dados para a View.
 */

require_once __DIR__ . '/../app/models/usuario.php';
require_once __DIR__ . '/../app/controllers/ControllerUsuario.php';

// Inicializa o Controller e executa o fluxo principal
$controlador = new ControllerUsuario();
$controlador->iniciar();