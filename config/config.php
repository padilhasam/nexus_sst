<?php
/**
 * Caminho público da aplicação.
 *
 * Instalação atual: http(s)://servidor/orcamento/public
 * Para outra pasta, altere apenas APP_BASE_URL no ambiente ou o valor padrão abaixo.
 */
$baseUrlConfigurada = getenv('APP_BASE_URL');

if ($baseUrlConfigurada === false || trim($baseUrlConfigurada) === '') {
    $baseUrlConfigurada = '/nexus_sst/public';
}

define('BASE_URL', '/' . trim($baseUrlConfigurada, '/'));
define('APP_NAME', 'Nexus SST');
define('APP_ENV', getenv('APP_ENV') ?: 'development');
