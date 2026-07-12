// Incluir no arquivo httpd.conf

<Directory />
    AllowOverride none
    Require all denied
</Directory>

<Directory "C:/xampp/htdocs">
    AllowOverride All
    Require all granted
</Directory>

<Directory "C:/xampp/htdocs/orcamento">
    AllowOverride All
    Require all granted
</Directory>
