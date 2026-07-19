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

$router->get('/funcionarios', 'FuncionariosController', 'index');
$router->get('/funcionarios/criar', 'FuncionariosController', 'criar');
$router->post('/funcionarios/salvar', 'FuncionariosController', 'salvar');
$router->get('/funcionarios/editar/{id}', 'FuncionariosController', 'editar');
$router->post('/funcionarios/atualizar/{id}', 'FuncionariosController', 'atualizar');
$router->post('/funcionarios/inativar/{id}', 'FuncionariosController', 'inativar');
$router->post('/funcionarios/reativar/{id}', 'FuncionariosController', 'reativar');
$router->post('/funcionarios/excluir/{id}', 'FuncionariosController', 'excluir');

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

$router->get('/agenda', 'AgendasController', 'index');
$router->get('/agenda/criar', 'AgendasController', 'criar');
$router->post('/agenda/salvar', 'AgendasController', 'salvar');
$router->get('/agenda/visualizar/{id}', 'AgendasController', 'visualizar');
$router->get('/agenda/editar/{id}', 'AgendasController', 'editar');
$router->post('/agenda/concluir/{id}', 'AgendasController', 'concluir');
$router->post('/agenda/atualizar/{id}', 'AgendasController', 'atualizar');
$router->post('/agenda/reagendar/{id}', 'AgendasController', 'reagendar');
$router->post('/agenda/cancelar/{id}', 'AgendasController', 'cancelar');
$router->post('/agenda/excluir/{id}', 'AgendasController', 'excluir');

$router->get('/visitas', 'VisitasController', 'index');
$router->get('/visitas/criar', 'VisitasController', 'criar');
$router->post('/visitas/salvar', 'VisitasController', 'salvar');
$router->get('/visitas/visualizar/{id}', 'VisitasController', 'visualizar');
$router->get('/visitas/editar/{id}', 'VisitasController', 'editar');
$router->post('/visitas/atualizar/{id}', 'VisitasController', 'atualizar');
$router->post('/visitas/atualizarData', 'VisitasController', 'atualizarData');
$router->post('/visitas/atualizarStatus', 'VisitasController', 'atualizarStatus');
$router->post('/visitas/cancelar/{id}', 'VisitasController', 'cancelar');
$router->post('/visitas/excluir/{id}', 'VisitasController', 'excluir');

$router->get('/checklists', 'ChecklistsController', 'index');
$router->post('/checklists/iniciar/{id}', 'ChecklistsController', 'iniciar');
$router->get('/checklists/visualizar/{id}', 'ChecklistsController', 'visualizar');
$router->post('/checklists/{id}/hierarquia/salvar', 'ChecklistsController', 'salvarHierarquia');
$router->post('/checklists/{id}/funcionarios/salvar', 'ChecklistsController', 'salvarFuncionario');
$router->post('/checklists/{id}/funcionarios/inativar/{funcionarioId}', 'ChecklistsController', 'inativarFuncionario');
$router->post('/checklists/{id}/ghe/salvar', 'ChecklistsController', 'salvarGhe');
$router->post('/checklists/{id}/ghe/{gheId}/riscos/salvar', 'ChecklistsController', 'salvarRiscoGhe');
$router->post('/checklists/{id}/finalizar', 'ChecklistsController', 'finalizar');

$router->any('/levantamentos', 'LevantamentosController', 'index');
$router->any('/levantamentos/criar', 'LevantamentosController', 'criar');
$router->post('/levantamentos/salvar', 'LevantamentosController', 'salvar');

$router->get('/ghe', 'GHEController', 'index');
$router->get('/ghe/criar', 'GHEController', 'criar');
$router->post('/ghe/salvar', 'GHEController', 'salvar');
$router->get('/ghe/visualizar/{id}', 'GHEController', 'visualizar');
$router->get('/ghe/editar/{id}', 'GHEController', 'editar');
$router->post('/ghe/atualizar/{id}', 'GHEController', 'atualizar');
$router->post('/ghe/inativar/{id}', 'GHEController', 'inativar');
$router->post('/ghe/reativar/{id}', 'GHEController', 'reativar');
$router->post('/ghe/{id}/riscos/salvar', 'GHEController', 'salvarRisco');
$router->post('/ghe/{id}/riscos/remover/{riscoId}', 'GHEController', 'removerRisco');

$router->any('/quantificacoes', 'QuantificacoesController', 'index');
$router->any('/quantificacoes/criar', 'QuantificacoesController', 'criar');
$router->post('/quantificacoes/salvar', 'QuantificacoesController', 'salvar');

$router->any('/nao_conformidades', 'NaoConformidadesController', 'index');
$router->any('/nao_conformidades/criar', 'NaoConformidadesController', 'criar');
$router->post('/nao_conformidades/salvar', 'NaoConformidadesController', 'salvar');

$router->any('/mensagens', 'MensagensController', 'index');
$router->any('/historico', 'HistoricoController', 'index');
$router->any('/uploads', 'UploadsController', 'index');