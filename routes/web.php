<?php

$router->get('/', 'LoginController', 'index');

$router->get('/login', 'LoginController', 'index');
$router->post('/login/autenticar', 'LoginController', 'autenticar');
$router->get('/logout', 'LoginController', 'logout');

$router->get('/dashboard', 'DashboardController', 'index');

$router->any('/usuarios', 'UsuariosController', 'index');
$router->any('/usuarios/criar', 'UsuariosController', 'criar');
$router->post('/usuarios/salvar', 'UsuariosController', 'salvar');
$router->any('/usuarios/editar/{id}', 'UsuariosController', 'editar');
$router->post('/usuarios/atualizar/{id}', 'UsuariosController', 'atualizar');
$router->any('/usuarios/excluir/{id}', 'UsuariosController', 'excluir');

$router->any('/empresas', 'EmpresasController', 'index');
$router->any('/empresas/criar', 'EmpresasController', 'criar');
$router->post('/empresas/armazenar', 'EmpresasController', 'armazenar');
$router->any('/empresas/editar/{id}', 'EmpresasController', 'editar');
$router->post('/empresas/atualizar/{id}', 'EmpresasController', 'atualizar');
$router->any('/empresas/excluir/{id}', 'EmpresasController', 'excluir');

$router->any('/unidades', 'UnidadesController', 'index');
$router->any('/unidades/criar', 'UnidadesController', 'criar');
$router->post('/unidades/salvar', 'UnidadesController', 'salvar');
$router->any('/unidades/editar/{id}', 'UnidadesController', 'editar');
$router->post('/unidades/atualizar/{id}', 'UnidadesController', 'atualizar');
$router->any('/unidades/excluir/{id}', 'UnidadesController', 'excluir');

$router->any('/setores', 'SetoresController', 'index');
$router->any('/setores/criar', 'SetoresController', 'criar');
$router->post('/setores/salvar', 'SetoresController', 'salvar');
$router->any('/setores/editar/{id}', 'SetoresController', 'editar');
$router->post('/setores/atualizar/{id}', 'SetoresController', 'atualizar');
$router->any('/setores/excluir/{id}', 'SetoresController', 'excluir');

$router->any('/cargos', 'CargosController', 'index');
$router->any('/cargos/criar', 'CargosController', 'criar');
$router->post('/cargos/salvar', 'CargosController', 'salvar');
$router->any('/cargos/editar/{id}', 'CargosController', 'editar');
$router->post('/cargos/atualizar/{id}', 'CargosController', 'atualizar');
$router->any('/cargos/excluir/{id}', 'CargosController', 'excluir');

$router->any('/funcionarios', 'FuncionariosController', 'index');
$router->any('/funcionarios/criar', 'FuncionariosController', 'criar');
$router->post('/funcionarios/salvar', 'FuncionariosController', 'salvar');
$router->any('/funcionarios/editar/{id}', 'FuncionariosController', 'editar');
$router->post('/funcionarios/atualizar/{id}', 'FuncionariosController', 'atualizar');
$router->any('/funcionarios/excluir/{id}', 'FuncionariosController', 'excluir');

$router->any('/veiculos', 'VeiculosController', 'index');
$router->any('/veiculos/criar', 'VeiculosController', 'criar');
$router->post('/veiculos/salvar', 'VeiculosController', 'salvar');
$router->any('/veiculos/editar/{id}', 'VeiculosController', 'editar');
$router->post('/veiculos/atualizar/{id}', 'VeiculosController', 'atualizar');
$router->any('/veiculos/excluir/{id}', 'VeiculosController', 'excluir');

$router->any('/equipamentos', 'EquipamentosController', 'index');

$router->any('/hierarquias', 'HierarquiasController', 'index');
$router->any('/hierarquias/criar', 'HierarquiasController', 'criar');
$router->post('/hierarquias/salvar', 'HierarquiasController', 'salvar');
$router->any('/hierarquias/editar/{id}', 'HierarquiasController', 'editar');
$router->post('/hierarquias/atualizar/{id}', 'HierarquiasController', 'atualizar');
$router->any('/hierarquias/excluir/{id}', 'HierarquiasController', 'excluir');
$router->any('/hierarquias/estrutura/{id}', 'HierarquiasController', 'estrutura');
$router->any('/hierarquias/importar', 'HierarquiasController', 'importar');
$router->post('/hierarquias/processarImportacao', 'HierarquiasController', 'processarImportacao');

$router->any('/riscos', 'RiscosController', 'index');
$router->any('/riscos/listar/{categoria}', 'RiscosController', 'listar');
$router->any('/riscos/criar/{categoria}', 'RiscosController', 'criar');
$router->post('/riscos/salvar', 'RiscosController', 'salvar');
$router->any('/riscos/editar/{id}', 'RiscosController', 'editar');
$router->post('/riscos/atualizar/{id}', 'RiscosController', 'atualizar');
$router->any('/riscos/excluir/{id}', 'RiscosController', 'excluir');

$router->any('/agenda', 'AgendasController', 'index');
$router->any('/agenda/criar', 'AgendasController', 'criar');
$router->post('/agenda/salvar', 'AgendasController', 'salvar');
$router->any('/agenda/editar/{id}', 'AgendasController', 'editar');
$router->post('/agenda/atualizar/{id}', 'AgendasController', 'atualizar');
$router->post('/agenda/cancelar/{id}', 'AgendasController', 'cancelar');
$router->post('/agenda/excluir/{id}', 'AgendasController', 'excluir');

$router->any('/visitas', 'VisitasController', 'index');
$router->any('/visitas/criar', 'VisitasController', 'criar');
$router->post('/visitas/salvar', 'VisitasController', 'salvar');
$router->any('/visitas/visualizar', 'VisitasController', 'visualizar');
$router->any('/visitas/visualizar/{id}', 'VisitasController', 'visualizar');
$router->any('/visitas/editar', 'VisitasController', 'editar');
$router->any('/visitas/editar/{id}', 'VisitasController', 'editar');
$router->post('/visitas/atualizar', 'VisitasController', 'atualizar');
$router->post('/visitas/atualizar/{id}', 'VisitasController', 'atualizar');
$router->post('/visitas/atualizarData', 'VisitasController', 'atualizarData');
$router->post('/visitas/atualizarStatus', 'VisitasController', 'atualizarStatus');
$router->any('/visitas/cancelar', 'VisitasController', 'cancelar');
$router->any('/visitas/excluir', 'VisitasController', 'excluir');

$router->any('/checklists', 'ChecklistsController', 'index');
$router->any('/checklists/iniciar/{id}', 'ChecklistsController', 'iniciar');
$router->any('/checklists/visualizar/{id}', 'ChecklistsController', 'visualizar');

$router->any('/levantamentos', 'LevantamentosController', 'index');
$router->any('/levantamentos/criar', 'LevantamentosController', 'criar');
$router->post('/levantamentos/salvar', 'LevantamentosController', 'salvar');

$router->any('/ghe', 'GHEController', 'index');

$router->any('/quantificacoes', 'QuantificacoesController', 'index');
$router->any('/quantificacoes/criar', 'QuantificacoesController', 'criar');
$router->post('/quantificacoes/salvar', 'QuantificacoesController', 'salvar');

$router->any('/nao_conformidades', 'NaoConformidadesController', 'index');
$router->any('/nao_conformidades/criar', 'NaoConformidadesController', 'criar');
$router->post('/nao_conformidades/salvar', 'NaoConformidadesController', 'salvar');

$router->any('/mensagens', 'MensagensController', 'index');
$router->any('/historico', 'HistoricoController', 'index');
$router->any('/uploads', 'UploadsController', 'index');