<?
/**
* TRIBUNAL REGIONAL FEDERAL DA 4� REGI�O
*
* 02/05/2011 - criado por mga
*
* Vers�o do Gerador de C�digo: 1.31.0
*
* Vers�o no CVS: $Id$
*/

require_once dirname(__FILE__).'/../../../SEI.php';

class ProtocoloIntegradoBD extends InfraBD {

  public function __construct(InfraIBanco $objInfraIBanco){
  	 parent::__construct($objInfraIBanco);
  }
   /**
  ** Fun��o Criada para recuperar o nome chaves estrangeiras em base Mysql da tabela de pacote
  ** Dependendo da vers�o a rodar o script de atualiza��o para 1.1.3,a foreign key ter� nomes diferentes.
  ** 
  **/
  public function recuperarChavesEstrangeirasv112(){

       $objPacoteDTO = new ProtocoloIntegradoDTO();
       $chaveEstrangeira = ""; 
       if (BancoSEI::getInstance() instanceof InfraMySql || BancoSEI::getInstance() instanceof InfraSqlServer){

           $sql = "SELECT constraint_name FROM information_schema.TABLE_CONSTRAINTS  WHERE information_schema.TABLE_CONSTRAINTS.CONSTRAINT_TYPE = 'FOREIGN KEY' AND information_schema.TABLE_CONSTRAINTS.TABLE_SCHEMA = 'sei' AND information_schema.TABLE_CONSTRAINTS.TABLE_NAME = 'protocolo_integrado';";
           $rs = $this->getObjInfraIBanco()->consultarSql($sql);
           //var_dump($rs);
           return $rs;


       }

  }
}
?>