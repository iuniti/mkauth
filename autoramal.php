<?php
/* AUTO RAMAL */

// Dados para acessar o banco
$hostdb = "localhost";
$userdb = "root";
$passdb = "vertrigo";
$mkradiusdb = "mkradius";
// Conexao ao banco mkradius
$mkradius = mysql_connect($hostdb, $userdb, $passdb, TRUE) or die (mysql_error());
@mysql_select_db($mkradiusdb, $mkradius) or die (mysql_error());

/* INICIA A CONSULTA POR CLIENTES CONECTADOS SEM RAMAL DEFINIDO */
// Faz consulta na tabela sis_cliente e sis_adicional
$busca_cliente = mysql_query("SELECT sis_cliente.`login` 
                              FROM `sis_cliente` 
                              WHERE sis_cliente.`cli_ativado` = 's'  
                              AND sis_cliente.`ramal` = 'todos' 
                              OR sis_cliente.`ramal` = '' 
                              OR sis_cliente.`ramal` IS NULL 
                              UNION ALL
                              SELECT sis_adicional.`username` 
                              FROM `sis_adicional` 
                              WHERE sis_adicional.`ramal` = 'todos' 
                              OR sis_adicional.`ramal` = '' 
                              OR sis_adicional.`ramal` IS NULL 
                              ORDER BY `login`", $mkradius) or die (mysql_error());

// Verifica resultado da consulta
if (@mysql_num_rows($busca_cliente) == 0){  
exit;
}else{
while($ramal_cliente = mysql_fetch_array($busca_cliente)) {	 
$login = $ramal_cliente['login'];

// Faz consulta na tabela radacct por ramal que o cliente esta conectado no momento			
$busca_nas = mysql_query("SELECT `nasipaddress` FROM `radacct` WHERE `username` = '".$login."' 
                          AND `acctstoptime` IS NULL ORDER BY `acctstarttime` DESC", $mkradius) or die (mysql_error());
                          $ramal_cliente_on = mysql_fetch_assoc($busca_nas);
                          $ramal = $ramal_cliente_on['nasipaddress'];
// Define o ramal que o cliente esta conectado na tabela sis_cliente		   
mysql_query("UPDATE `sis_cliente` SET `ramal` = '".$ramal."' WHERE `login` = '".$login."'", $mkradius) or die (mysql_error());
// Define o ramal que o cliente esta conectado na tabela sis_adicional		   
mysql_query("UPDATE `sis_adicional` SET `ramal` = '".$ramal."' WHERE `username` = '".$login."'", $mkradius) or die (mysql_error());
 }
}
// Define o ramal "todos" para clientes que nao foram alterados na tabela sis_cliente
mysql_query("UPDATE `sis_cliente` SET `ramal` = 'todos' WHERE `ramal` = '' OR `ramal` IS NULL", $mkradius) or die (mysql_error());
// Define o ramal "todos" para clientes que nao foram alterados na tabela sis_adicional
mysql_query("UPDATE `sis_adicional` SET `ramal` = 'todos' WHERE `ramal` = '' OR `ramal` IS NULL", $mkradius) or die (mysql_error());
// Fecha a conexao com banco
mysql_close($mkradius);
?>