<?
/**
* TRIBUNAL REGIONAL FEDERAL DA 4� REGI�O
*
* 07/05/2013 - criado por mga
*
*/

require_once dirname(__FILE__).'/../../../SEI.php';


class ProtocoloIntegradoVersaoRN extends InfraRN {

  private $numSeg = 0;
  private $versaoAtualDesteModulo = '1.1.5';
  private $nomeParametroModulo = 'PI_VERSAO';
  private $historicoVersoes = array('1.1.1','1.1.2','1.1.3','1.1.4','1.1.5');

  public function __construct(){
    //parent::__construct(); 
    $this->inicializar(' SEI - INICIALIZAR ');
  }

  protected function inicializarObjInfraIBanco(){
    return BancoSEI::getInstance();
  }

  private function inicializar($strTitulo){

    ini_set('max_execution_time','0');
    ini_set('memory_limit','-1');

   try {
			@ini_set('zlib.output_compression','0');
			@ini_set('implicit_flush', '1');
	}catch(Exception $e){}

	BancoSEI::getInstance()->abrirConexao();
	BancoSEI::getInstance()->abrirTransacao();

	ob_implicit_flush();

    InfraDebug::getInstance()->setBolLigado(true);
    InfraDebug::getInstance()->setBolDebugInfra(true);
    InfraDebug::getInstance()->setBolEcho(true);
    InfraDebug::getInstance()->limpar();

    $this->logar($strTitulo);
  }

  private function logar($strMsg){
    InfraDebug::getInstance()->gravar($strMsg);
    flush();
    //para encher o buffer e fazer o flush
    //echo str_repeat(' ',64*1024);  flush();

    //LogSEI::getInstance()->gravar($strMsg);
  }

  private function finalizar($strMsg=null, $bolErro){

    if (!$bolErro) {
      $this->numSeg = InfraUtil::verificarTempoProcessamento($this->numSeg);
      $this->logar('TEMPO TOTAL DE EXECUCAO: ' . $this->numSeg . ' s');
    }else{
      $strMsg = 'ERRO: '.$strMsg;
    }

    if ($strMsg!=null){
      $this->logar($strMsg);
    }

    InfraDebug::getInstance()->setBolLigado(false);
    InfraDebug::getInstance()->setBolDebugInfra(false);
    InfraDebug::getInstance()->setBolEcho(false);
    BancoSEI::getInstance()->cancelarTransacao();
    BancoSEI::getInstance()->fecharConexao();
    InfraDebug::getInstance()->limpar();
    $this->numSeg = 0;
    die;
  }


  /**
   * @throws InfraException
   */
  protected function atualizarVersaoControlado(){
  		
	try{
    	
		if (!(BancoSEI::getInstance() instanceof InfraMySql) && !(BancoSEI::getInstance() instanceof InfraSqlServer) && !(BancoSEI::getInstance() instanceof InfraOracle)){
			$this->finalizar('BANCO DE DADOS NAO SUPORTADO: '.get_parent_class(BancoSEI::getInstance()),true);
		}
		//Selecionando vers�o a ser instalada
		$objInfraParametro = new InfraParametro(BancoSEI::getInstance());
		$strVersaoPreviaModuloProtocoloIntegrado = $objInfraParametro->getValor('PI_VERSAO', false);
		$instalacao = array();
		switch($this->versaoAtualDesteModulo){

			case '1.1.2':
				// Vers�o do plugin com suporte apenas ao Mysql
				$instalacao = $this->instalarv112($strVersaoPreviaModuloProtocoloIntegrado);
				break;
			case '1.1.3':
				//Vers�o do plugin com suporte multibancos : Mysql,SqlServer e Oracle
				$instalacao = $this->instalarv113($strVersaoPreviaModuloProtocoloIntegrado);
				break;
			case '1.1.4':
				//Vers�o do plugin com suporte multibancos : Mysql,SqlServer e Oracle
				$instalacao = $this->instalarv114($strVersaoPreviaModuloProtocoloIntegrado);
				break;
			case '1.1.5':
				//Vers�o do plugin com suporte multibancos : Mysql,SqlServer e Oracle
				$instalacao = $this->instalarv115($strVersaoPreviaModuloProtocoloIntegrado);
				break;
			default:
					$instalacao["operacoes"] = null;
					$instalacao["erro"] = "Erro instalando/atualizando M�dulo Protocolo Integrado no SEI. Vers�o do m�dulo".$strVersaoPreviaModuloProtocoloIntegrado." inv�lida";

				   break;		
		}
		if(isset($instalacao["erro"])){

			 
			 $this->finalizar($instalacao["erro"],true);
		}else{

			 $this->logar("Instala��o/Atualiza��o realizada com sucesso");
		}
		
		
		InfraDebug::getInstance()->setBolLigado(false);
      	InfraDebug::getInstance()->setBolDebugInfra(false);
      	InfraDebug::getInstance()->setBolEcho(false);

      	LogSEI::getInstance()->gravar(InfraDebug::getInstance()->getStrDebug());
		
		BancoSEI::getInstance()->confirmarTransacao();
        BancoSEI::getInstance()->fecharConexao();
        InfraDebug::getInstance()->limpar();
    	
	}catch(Exception $e){
      	


      	InfraDebug::getInstance()->setBolLigado(false);
      	InfraDebug::getInstance()->setBolDebugInfra(false);
      	InfraDebug::getInstance()->setBolEcho(false);
		
		BancoSEI::getInstance()->cancelarTransacao();
   		BancoSEI::getInstance()->fecharConexao();

   		InfraDebug::getInstance()->limpar();
		throw new InfraException('Erro instalando/atualizando m�dulo do protocolo integrado no SEI.', $e);
		
		
	}
  }
  private function instalarv115($strVersaoPreviaModuloProtocoloIntegrado){

  		$objProtocoloIntegradoParametrosDTO = new ProtocoloIntegradoParametrosDTO();
  		$objProtocoloIntegradoParametrosRN  = new ProtocoloIntegradoParametrosRN();
      $objInfraMetaBD = new InfraMetaBD(BancoSEI::getInstance());

  		$resultado = array();
  		if( strlen(ProtocoloIntegradoParametrosRN::$CHAVE_MODULO_PI)!=ProtocoloIntegradoParametrosRN::$NUM_CARACTERES_CHAVE_PI){

  			$resultado["erro"] = "Erro instalando/atualizando M�dulo Protocolo Integrado no SEI.� necess�rio definir uma chave de 16 caracteres para vari�vel CHAVE_MODULO_PI no arquivo ProtocoloIntegradoParametrosRN.php ";
  			return $resultado;
  		}
  		if(trim($strVersaoPreviaModuloProtocoloIntegrado)==trim($this->versaoAtualDesteModulo)){


  				$resultado["erro"] = "Erro instalando/atualizando M�dulo Protocolo Integrado no SEI. Vers�o ".$strVersaoPreviaModuloProtocoloIntegrado." j� instalada";
  				return $resultado;

  		}else{

  			$this->instalarv114($strVersaoPreviaModuloProtocoloIntegrado);
  		}

      if(!InfraString::isBolVazia($strVersaoPreviaModuloProtocoloIntegrado)){
      
         
          $objInfraSequencia = new InfraSequencia(BancoSEI::getInstance());
          $objInfraSequenciaBD = new InfraSequenciaBD(BancoSEI::getInstance());
          $objInfraSequenciaDTO = new InfraSequenciaDTO();
          $objInfraSequenciaDTO->setStrNome('infra_agendamento_tarefa');
          $objInfraSequenciaDTO->retDblNumAtual();
          $objInfraSequencia = $objInfraSequenciaBD->consultar($objInfraSequenciaDTO);
          $numProximoValorSequencia = $objInfraSequencia->getDblNumAtual();
          $numMaxIdAgendamento = $this->getMaxIdAgendamento();

          if($numProximoValorSequencia<$numMaxIdAgendamento){

                
                $objInfraSequenciaDTO = new InfraSequenciaDTO();
          
                $objInfraSequenciaDTO->setDblNumAtual($numMaxIdAgendamento);
                $objInfraSequenciaDTO->setStrNome('infra_agendamento_tarefa');
                $objInfraSequenciaBD->alterar($objInfraSequenciaDTO);

          }

      }
      $objAgendamentoDTO  = $this->adicionarAgendamento('ProtocoloIntegradoAgendamentoRN::publicarProtocoloIntegrado','Processo de Publica��o do PI','D','2');
      $objAgendamentoDTO  = $this->adicionarAgendamento('ProtocoloIntegradoAgendamentoRN::notificarProcessosComFalhaPublicacaoProtocoloIntegrado','Agendamento do alarme de e-mail disparado quando h� falha na publica��o de pacotes','D','17');
      $objAgendamentoDTO  = $this->adicionarAgendamento('ProtocoloIntegradoAgendamentoRN::notificarNovosPacotesNaoSendoGerados','Agendamento do alarme de e-mail disparado quando novos pacotes n�o est�o sendo gerados','D','12');
      
      $objInfraMetaBD->excluirChaveEstrangeira('md_pi_mensagem','fk_md_pi_mensagem_tarefa');

      BancoSEI::getInstance()->executarSql('ALTER TABLE md_pi_mensagem ADD CONSTRAINT fk_md_pi_mensagem_tarefa FOREIGN KEY (id_tarefa) REFERENCES tarefa (id_tarefa) ON DELETE CASCADE');

  		BancoSEI::getInstance()->executarSql('update infra_parametro set valor=\''.$this->versaoAtualDesteModulo.'\' where nome=\'PI_VERSAO\'');
  }
  private function instalarv114($strVersaoPreviaModuloProtocoloIntegrado){

  		$objProtocoloIntegradoParametrosDTO = new ProtocoloIntegradoParametrosDTO();
  		$objProtocoloIntegradoParametrosRN  = new ProtocoloIntegradoParametrosRN();

  		$resultado = array();
  		if( strlen(ProtocoloIntegradoParametrosRN::$CHAVE_MODULO_PI)!=ProtocoloIntegradoParametrosRN::$NUM_CARACTERES_CHAVE_PI){

  			$resultado["erro"] = "Erro instalando/atualizando M�dulo Protocolo Integrado no SEI.� necess�rio definir uma chave de 16 caracteres para vari�vel CHAVE_MODULO_PI no arquivo ProtocoloIntegradoParametrosRN.php ";
  			return $resultado;
  		}
     	if(InfraString::isBolVazia($strVersaoPreviaModuloProtocoloIntegrado)|| trim($strVersaoPreviaModuloProtocoloIntegrado)=='1.1.2'){

  			  $instalacao = $this->instalarv113($strVersaoPreviaModuloProtocoloIntegrado);
	          if(!isset($instalacao["erro"])){
				
					return $this->instalarv114('1.1.3');  
	          }else{

	              return $instalacao;
	          }
  				
  		}else if(trim($strVersaoPreviaModuloProtocoloIntegrado)=='1.1.3'){
			
			//Alterando tamanho da coluna senha_webservice nos tr�s bancos
		  	if (BancoSEI::getInstance() instanceof InfraMySql){
			    BancoSEI::getInstance()->executarSql("alter table md_pi_parametros modify column senha_webservice varchar(100)");
			} else if (BancoSEI::getInstance() instanceof InfraSqlServer){
				BancoSEI::getInstance()->executarSql("alter table md_pi_parametros alter column senha_webservice varchar(100)");
			}else if (BancoSEI::getInstance() instanceof InfraOracle){
			  		BancoSEI::getInstance()->executarSql("alter table md_pi_parametros modify( senha_webservice varchar(100))");
			}
			$objProtocoloIntegradoParametrosDTO->retTodos();
			$objParametrosRetornados = $objProtocoloIntegradoParametrosRN->consultar($objProtocoloIntegradoParametrosDTO);
		  
			if($objParametrosRetornados->getStrSenhaWebservice()!=null && strlen(trim($objParametrosRetornados->getStrSenhaWebservice()))>0){

			  $senhaEncriptada = rawurlencode($objProtocoloIntegradoParametrosRN->encriptaSenha(trim($objParametrosRetornados->getStrSenhaWebservice())));
			  $objParametrosRetornados->setStrSenhaWebservice($senhaEncriptada);
			  $objProtocoloIntegradoParametrosRN->alterar($objParametrosRetornados);
			}
      
  			
			BancoSEI::getInstance()->executarSql('update infra_parametro set valor=\'1.1.4\' where nome=\'PI_VERSAO\'');

  		}else if(trim($strVersaoPreviaModuloProtocoloIntegrado)==trim($this->versaoAtualDesteModulo)){


  				$resultado["erro"] = "Erro instalando/atualizando M�dulo Protocolo Integrado no SEI. Vers�o ".$strVersaoPreviaModuloProtocoloIntegrado." j� instalada";
  				return $resultado;

  		}
  }
  private function instalarv113($strVersaoPreviaModuloProtocoloIntegrado){

  		
  		$objInfraMetaBD = new InfraMetaBD(BancoSEI::getInstance());
  		$this->logar(' INICIANDO OPERACOES DA INSTALACAO DA VERSAO 1.1.3 DO MODULO PROTOCOLO INTEGRADO NA BASE DO SEI');
  		
  		$erros = null;
     	$versao = '1.1.3';

  		$resultado = array();
  		$resultado["operacoes"] = null;
  		$comandosExecutados = '';
  		if(InfraString::isBolVazia($strVersaoPreviaModuloProtocoloIntegrado)){

  				//Criando a tabela de pacotes nos tr�s bancos
  				BancoSEI::getInstance()->executarSql("CREATE TABLE md_pi_pacote_envio (
							id_md_pi_pacote_envio ".$objInfraMetaBD->tipoNumeroGrande()." NOT NULL,
							id_protocolo ".$objInfraMetaBD->tipoNumeroGrande()." NOT NULL,
							dth_metadados ".$objInfraMetaBD->tipoDataHora()."  NULL,
							dth_situacao ".$objInfraMetaBD->tipoDataHora()."  NULL,
							sta_integracao ".$objInfraMetaBD->tipoTextoFixo(2)." NOT NULL,
							arquivo_metadados ".$objInfraMetaBD->tipoTextoGrande()." NULL,
							arquivo_erro ".$objInfraMetaBD->tipoTextoGrande()." NULL,
							num_tentativas_envio ".$objInfraMetaBD->tipoNumero()." DEFAULT '0',
							dth_agendamento_executado ".$objInfraMetaBD->tipoDataHora()."  NULL)");

  				$objInfraMetaBD->adicionarChavePrimaria('md_pi_pacote_envio','pk_id_md_pi_pacote_envio',array('id_md_pi_pacote_envio'));

  				$objInfraMetaBD->adicionarChaveEstrangeira('fk_md_pacote_pi_protocolo','md_pi_pacote_envio',array('id_protocolo'),'protocolo',array('id_protocolo'));

  				if (BancoSEI::getInstance() instanceof InfraMySql){
			  		BancoSEI::getInstance()->executarSql('create table seq_md_pi_pacote_envio (id bigint not null primary key AUTO_INCREMENT, campo char(1) null) AUTO_INCREMENT = 1');
			  	} else if (BancoSEI::getInstance() instanceof InfraSqlServer){
			  		BancoSEI::getInstance()->executarSql('create table seq_md_pi_pacote_envio (id bigint identity(1,1), campo char(1) null)');
			  	} else if (BancoSEI::getInstance() instanceof InfraOracle){
			  		BancoSEI::getInstance()->criarSequencialNativa('seq_md_pi_pacote_envio', 1);
			  	}

  				//Criando a tabela de monitoramento de processos nos tr�s bancos
  				BancoSEI::getInstance()->executarSql("CREATE TABLE md_pi_monitora_processos (
							id_md_pi_monitora_processos ".$objInfraMetaBD->tipoNumeroGrande()."  NOT NULL,
							id_atividade ".$objInfraMetaBD->tipoNumero()."  NOT NULL,
							dth_cadastro ".$objInfraMetaBD->tipoDataHora()." NULL,
							id_md_pi_pacote_envio ".$objInfraMetaBD->tipoNumeroGrande()." NOT NULL)");
  				
  				/*$objInfraMetaBD->adicionarChavePrimaria('md_pi_monitora_processos','pk_id_md_pi_monitora_processos',array('id_md_pi_monitora_processos'));*/

  				$objInfraMetaBD->adicionarChaveEstrangeira('fk_md_pi_monit_processo_ativ','md_pi_monitora_processos',array('id_atividade'),'atividade',array('id_atividade'));
				$objInfraMetaBD->adicionarChaveEstrangeira('fk_md_pi_monit_processo_pacote','md_pi_monitora_processos',array('id_md_pi_pacote_envio'),'md_pi_pacote_envio',array('id_md_pi_pacote_envio'));	
				
				$objInfraMetaBD->criarIndice('md_pi_monitora_processos','i01_md_pi_monitora_processos',array('id_atividade'));
				$objInfraMetaBD->criarIndice('md_pi_monitora_processos','i02_md_pi_monitora_processos',array('id_md_pi_pacote_envio'));

				if (BancoSEI::getInstance() instanceof InfraMySql){
			  		BancoSEI::getInstance()->executarSql('create table seq_md_pi_monitora_processos (id bigint not null primary key AUTO_INCREMENT, campo char(1) null) AUTO_INCREMENT = 1');
			  	} else if (BancoSEI::getInstance() instanceof InfraSqlServer){
			  		BancoSEI::getInstance()->executarSql('create table seq_md_pi_monitora_processos (id bigint identity(1,1), campo char(1) null)');
			  	} else if (BancoSEI::getInstance() instanceof InfraOracle){
			  		BancoSEI::getInstance()->criarSequencialNativa('seq_md_pi_monitora_processos', 1);
			  	}

  				//Criando a tabela de configura��o de mensagens de publica��o no Protocolo Integrado nos tr�s bancos  	
			  	BancoSEI::getInstance()->executarSql("CREATE TABLE md_pi_mensagem (
			  				id_md_pi_mensagem ".$objInfraMetaBD->tipoNumeroGrande()." NOT NULL,
							id_tarefa ".$objInfraMetaBD->tipoNumero()."  NULL, 
							sin_publicar ".$objInfraMetaBD->tipoTextoFixo(1)." NOT NULL,
							mensagem_publicacao ".$objInfraMetaBD->tipoTextoVariavel(255)." NOT NULL)");
			  
			  	$objInfraMetaBD->adicionarChavePrimaria('md_pi_mensagem','pk_id_md_pi_mensagem',array('id_md_pi_mensagem'));
			  	$objInfraMetaBD->adicionarChaveEstrangeira('fk_md_pi_mensagem_tarefa','md_pi_mensagem',array('id_tarefa'),'tarefa',array('id_tarefa'));

  				$objInfraMetaBD->criarIndice('md_pi_mensagem','i01_md_pi_mensagem',array('id_tarefa'));

  				//Criando a tabela de configura��o de par�metros do m�dulo  nos tr�s bancos
  				BancoSEI::getInstance()->executarSql("CREATE TABLE md_pi_parametros (
							id_md_pi_parametros ".$objInfraMetaBD->tipoNumeroGrande()." NOT NULL,
							url_webservice ".$objInfraMetaBD->tipoTextoVariavel(255)." NOT NULL,
							quantidade_tentativas ".$objInfraMetaBD->tipoNumero()." NOT NULL,
							email_administrador ".$objInfraMetaBD->tipoTextoVariavel(255)." NULL,
							dth_ultimo_processamento ".$objInfraMetaBD->tipoDataHora()."   NULL,
							login_webservice ".$objInfraMetaBD->tipoTextoVariavel(10)."  NULL,
							senha_webservice ".$objInfraMetaBD->tipoTextoVariavel(20)."  NULL,
							sin_executando_publicacao ".$objInfraMetaBD->tipoTextoFixo(1)."  DEFAULT 'N',
							sin_publicacao_restritos ".$objInfraMetaBD->tipoTextoFixo(1)."  DEFAULT 'S',
							num_atividades_carregar ".$objInfraMetaBD->tipoNumero()."  NULL)");
					
  				$objInfraMetaBD->adicionarChavePrimaria('md_pi_parametros','pk_id_md_pi_parametros',array('id_md_pi_parametros'));
			  
  				//Inserindo as atividades que devem ser enviadas,por padr�o,ao Protocolo Integrado 	
  				BancoSEI::getInstance()->executarSql("insert into md_pi_mensagem (id_md_pi_mensagem, id_tarefa,sin_publicar,mensagem_publicacao) select id_tarefa, id_tarefa,'N',nome from tarefa");
  				
  				$objProtocoloIntegradoRN = new ProtocoloIntegradoRN();
  				$tarefasPublicacao =  $objProtocoloIntegradoRN->montaTarefasPadraoPublicacao();
  				foreach($tarefasPublicacao as $key=>$value){

  						BancoSEI::getInstance()->executarSql("UPDATE md_pi_mensagem set sin_publicar = 'S' where id_tarefa = ".$value." ");

  				}
  				BancoSEI::getInstance()->executarSql("INSERT INTO md_pi_parametros (id_md_pi_parametros,url_webservice,quantidade_tentativas,email_administrador,
		  			login_webservice,senha_webservice,sin_executando_publicacao,sin_publicacao_restritos,num_atividades_carregar) VALUES (1,'https://protocolointegrado.gov.br/ProtocoloWS/integradorService?wsdl',15,'','','','N','S',100000)");
  				
  				
  				/*
  				$numCountElementos = BancoSEI::getInstance()->executarSql("select * from infra_agendamento_tarefa where comando='ProtocoloIntegradoAgendamentoRN::publicarProtocoloIntegrado'");
				if ($numCountElementos==0){

					$comando ="INSERT INTO infra_agendamento_tarefa (id_infra_agendamento_tarefa, descricao, comando, sta_periodicidade_execucao, periodicidade_complemento, sin_ativo,sin_sucesso) 
						VALUES ((select max(iat.id_infra_agendamento_tarefa)+1 from infra_agendamento_tarefa iat), 'Processo de Publica��o do PI', 'ProtocoloIntegradoAgendamentoRN::publicarProtocoloIntegrado', 
						'D', '2', 'S','N')";
					$comandosExecutados .= '<label>'.$comando . '</label>'.'<br/>'.'<br/>';
			  		
					BancoSEI::getInstance()->executarSql($comando);
				}
				
				$numCountElementos = BancoSEI::getInstance()->executarSql("select * from infra_agendamento_tarefa where comando='ProtocoloIntegradoAgendamentoRN::notificarNovosPacotesNaoSendoGerados'");
				if ($numCountElementos==0){
					$comando = "INSERT INTO infra_agendamento_tarefa (id_infra_agendamento_tarefa, descricao, comando, sta_periodicidade_execucao, periodicidade_complemento, 
						parametro, sin_ativo,sin_sucesso) VALUES ((select max(iat.id_infra_agendamento_tarefa)+1 from infra_agendamento_tarefa iat), 'Agendamento do alarme de e-mail disparado quando novos pacotes n�o 
						est�o sendo gerados', 'ProtocoloIntegradoAgendamentoRN::notificarNovosPacotesNaoSendoGerados', 'D', '12', '2', 'S','N')";
					$comandosExecutados .= '<label>'.$comando . '</label>'.'<br/>'.'<br/>';
			  		
					BancoSEI::getInstance()->executarSql($comando);
				}      
				
				$numCountElementos = BancoSEI::getInstance()->executarSql("select * from infra_agendamento_tarefa where comando='ProtocoloIntegradoAgendamentoRN::notificarProcessosComFalhaPublicacaoProtocoloIntegrado'");
				if ($numCountElementos==0){

					$comando = "INSERT INTO infra_agendamento_tarefa (id_infra_agendamento_tarefa,descricao,comando,sta_periodicidade_execucao,periodicidade_complemento,sin_ativo,sin_sucesso)
						VALUES ((select max(iat.id_infra_agendamento_tarefa)+1 from infra_agendamento_tarefa iat),'Agendamento do alarme de e-mail disparado quando h� falha na publoca��o de pacotes',
						'ProtocoloIntegradoAgendamentoRN::notificarProcessosComFalhaPublicacaoProtocoloIntegrado','D','17','S','N')";
					$comandosExecutados .= '<label>'.$comando . '</label>'.'<br/>'.'<br/>';
			  		
					BancoSEI::getInstance()->executarSql($comando);
				}      
				*/
			  BancoSEI::getInstance()->executarSql('insert into infra_parametro(nome,valor) values(\'PI_VERSAO\', \''.$versao.'\')');	
				
				
				
  		}else if(trim($strVersaoPreviaModuloProtocoloIntegrado)==$versao){


  				$resultado["erro"] = "Erro instalando/atualizando M�dulo Protocolo Integrado no SEI. Vers�o ".$strVersaoPreviaModuloProtocoloIntegrado." j� instalada";
  				return $resultado;

  		}else if(trim($strVersaoPreviaModuloProtocoloIntegrado)=='1.1.2'){

          $objProtocoloIntegradoBD = new ProtocoloIntegradoBD($this->getObjInfraIBanco());
          $chavesEstrangeirasProtocoloIntegrado = $objProtocoloIntegradoBD->recuperarChavesEstrangeirasv112();
          foreach($chavesEstrangeirasProtocoloIntegrado as $key=>$arrChaveEstrangeiraProtocoloIntegrado){

                $objInfraMetaBD->excluirChaveEstrangeira('protocolo_integrado',$arrChaveEstrangeiraProtocoloIntegrado[0]);
          }
  				
  				$objPacoteBD = new ProtocoloIntegradoPacoteEnvioBD($this->getObjInfraIBanco());
  				$chavesEstrangeirasPacote = $objPacoteBD->recuperarChavesEstrangeirasv112();

  				foreach($chavesEstrangeirasPacote as $key=>$arrChaveEstrangeiraPacote){

  						//$this->logar($chaveEstrangeiraPacote);
  						$objInfraMetaBD->excluirChaveEstrangeira('protocolo_integrado_pacote_envio',$arrChaveEstrangeiraPacote[0]);
  				}

          $objProtocoloIntegradoMonitoramentoProcessosBD = new ProtocoloIntegradoMonitoramentoProcessosBD($this->getObjInfraIBanco());
          $chavesEstrangeirasMonitoramentoProcessos = $objProtocoloIntegradoMonitoramentoProcessosBD->recuperarChavesEstrangeirasv112();
  				
          foreach($chavesEstrangeirasMonitoramentoProcessos as $key=>$arrChaveEstrangeiraMonitoramentoProcessos){

              //$this->logar($chaveEstrangeiraPacote);
              $objInfraMetaBD->excluirChaveEstrangeira('protocolo_integrado_monitoramento_processos',$arrChaveEstrangeiraMonitoramentoProcessos[0]);
          }
				
  				if (BancoSEI::getInstance() instanceof InfraMySql){
			  		BancoSEI::getInstance()->executarSql('RENAME TABLE protocolo_integrado to md_pi_mensagem');
			  	} else if (BancoSEI::getInstance() instanceof InfraSqlServer){
			  		BancoSEI::getInstance()->executarSql("EXEC sp_rename 'protocolo_integrado', 'md_pi_mensagem';");
			  	} 

			  	if (BancoSEI::getInstance() instanceof InfraMySql){
			  		BancoSEI::getInstance()->executarSql("Alter TABLE md_pi_mensagem CHANGE id_protocolo_integrado id_md_pi_mensagem BIGINT(20)");
			  	} else if (BancoSEI::getInstance() instanceof InfraSqlServer){
			  		BancoSEI::getInstance()->executarSql("EXEC sp_RENAME 'md_pi_mensagem.id_protocolo_integrado' , 'id_md_pi_mensagem', 'COLUMN' ");
			  	} 

				/*$objInfraMetaBD->adicionarChavePrimaria('md_pi_mensagem','pk_id_md_pi_mensagem',array('id_md_pi_mensagem'));*/
			  	$objInfraMetaBD->adicionarChaveEstrangeira('fk_md_pi_mensagem_tarefa','md_pi_mensagem',array('id_tarefa'),'tarefa',array('id_tarefa'));
				

				
				$arrPacotesPrevios = array();
				$objPacoteBD = new ProtocoloIntegradoPacoteEnvioBD($this->getObjInfraIBanco());
				if (BancoSEI::getInstance() instanceof InfraMySql){
			  		
					
					
			  		BancoSEI::getInstance()->executarSql('RENAME TABLE protocolo_integrado_pacote_envio to md_pi_pacote_envio');

			  		
			  	} else if (BancoSEI::getInstance() instanceof InfraSqlServer){

			  		BancoSEI::getInstance()->executarSql("EXEC sp_rename 'protocolo_integrado_pacote_envio', 'md_pi_pacote_envio';");

			  		$arrPacotesPrevios = $objPacoteBD->recuperarColunaTabelaPacote('id_protocolo_integrado_pacote_envio');


			  	} 
				
				if (BancoSEI::getInstance() instanceof InfraMySql){
			  		BancoSEI::getInstance()->executarSql("ALTER TABLE md_pi_pacote_envio CHANGE id_protocolo_integrado_pacote_envio id_md_pi_pacote_envio BIGINT(20)");
			  	} else if (BancoSEI::getInstance() instanceof InfraSqlServer){

			  		BancoSEI::getInstance()->executarSql("ALTER TABLE md_pi_pacote_envio add  id_md_pi_pacote_envio bigint;");

					BancoSEI::getInstance()->executarSql("update md_pi_pacote_envio set id_md_pi_pacote_envio=id_protocolo_integrado_pacote_envio"); 
					$nomeRestricaoChavePrimaria = $objPacoteBD->recuperarChavePrimaria();
					
					BancoSEI::getInstance()->executarSql("ALTER TABLE md_pi_pacote_envio drop constraint ".$nomeRestricaoChavePrimaria."; ");

					BancoSEI::getInstance()->executarSql("ALTER TABLE md_pi_pacote_envio drop column id_protocolo_integrado_pacote_envio; ");

					BancoSEI::getInstance()->executarSql("ALTER TABLE md_pi_pacote_envio alter column id_md_pi_pacote_envio bigint not null; ");

					$objInfraMetaBD->adicionarChavePrimaria('md_pi_pacote_envio','pk_id_md_pi_pacote_envio',array('id_md_pi_pacote_envio'));
			  	} 

							  	

  				$objInfraMetaBD->adicionarChaveEstrangeira('fk_md_pacote_pi_protocolo','md_pi_pacote_envio',array('id_protocolo'),'protocolo',array('id_protocolo'));


  				if(BancoSEI::getInstance() instanceof InfraSqlServer){

  					BancoSEI::getInstance()->executarSql("ALTER TABLE md_pi_pacote_envio ALTER COLUMN arquivo_metadados ".$objInfraMetaBD->tipoTextoGrande()." NULL");
  					BancoSEI::getInstance()->executarSql("ALTER TABLE md_pi_pacote_envio ALTER COLUMN arquivo_erro ".$objInfraMetaBD->tipoTextoGrande()." NULL");
  				}
  				

  				if (BancoSEI::getInstance() instanceof InfraMySql){
			  		BancoSEI::getInstance()->executarSql("RENAME TABLE protocolo_integrado_parametros to md_pi_parametros");
			  	} else if (BancoSEI::getInstance() instanceof InfraSqlServer){
			  		BancoSEI::getInstance()->executarSql("EXEC sp_rename 'protocolo_integrado_parametros', 'md_pi_parametros';");
			  	} 

			  	if (BancoSEI::getInstance() instanceof InfraMySql){
			  		BancoSEI::getInstance()->executarSql("Alter TABLE md_pi_parametros CHANGE id_protocolo_integrado_parametros id_md_pi_parametros BIGINT(20)");
			  	} else if (BancoSEI::getInstance() instanceof InfraSqlServer){
			  		BancoSEI::getInstance()->executarSql("EXEC sp_RENAME 'md_pi_parametros.id_protocolo_integrado_parametros' , 'id_md_pi_parametros', 'COLUMN' ");
			  	} 

			  	
			  	$arrProcessosMonitoradosPrevios = array();
			  	$objMonitoramentoProcessosBD = new ProtocoloIntegradoMonitoramentoProcessosBD($this->getObjInfraIBanco());
			  	if (BancoSEI::getInstance() instanceof InfraMySql){
					
					BancoSEI::getInstance()->executarSql( "RENAME TABLE protocolo_integrado_monitoramento_processos to md_pi_monitora_processos");
			  	} else if (BancoSEI::getInstance() instanceof InfraSqlServer){
			  		
			  		
			  		$arrProcessosMonitoradosPrevios = $objMonitoramentoProcessosBD->recuperarIdsTabelaMonitoramentov112();
			  		BancoSEI::getInstance()->executarSql("EXEC sp_rename 'protocolo_integrado_monitoramento_processos', 'md_pi_monitora_processos';");
			  	} 
				
				if (BancoSEI::getInstance() instanceof InfraMySql){
			  		BancoSEI::getInstance()->executarSql("Alter TABLE md_pi_monitora_processos CHANGE id_protocolo_integrado_monitoramento_processos id_md_pi_monitora_processos BIGINT(20)");
			  	} else if (BancoSEI::getInstance() instanceof InfraSqlServer){

			  		BancoSEI::getInstance()->executarSql("ALTER TABLE md_pi_monitora_processos add  id_md_pi_monitora_processos bigint;");
			  		$objMonitoraProcessosRN = new ProtocoloIntegradoMonitoramentoProcessosRN();
			  		foreach($arrProcessosMonitoradosPrevios as $key=>$value){

						BancoSEI::getInstance()->executarSql('update md_pi_monitora_processos set id_md_pi_monitora_processos=\''.$value->getNumIdProtocoloIntegradoMonitoramentoProcessos().'\' where id_protocolo_integrado_monitoramento_processos=\''.$value->getNumIdProtocoloIntegradoMonitoramentoProcessos().'\';');

					}
					$nomeRestricaoChavePrimaria = $objMonitoramentoProcessosBD->recuperarChavePrimaria();
					
					BancoSEI::getInstance()->executarSql("ALTER TABLE md_pi_monitora_processos drop constraint ".$nomeRestricaoChavePrimaria."; ");

					BancoSEI::getInstance()->executarSql("ALTER TABLE md_pi_monitora_processos drop column id_protocolo_integrado_monitoramento_processos; ");

					BancoSEI::getInstance()->executarSql("ALTER TABLE md_pi_monitora_processos alter column id_md_pi_monitora_processos bigint not null; ");

					$objInfraMetaBD->adicionarChavePrimaria('md_pi_monitora_processos','pk_id_md_pi_monitora_processos',array('id_md_pi_monitora_processos'));
					/*
			  		BancoSEI::getInstance()->executarSql("EXEC sp_RENAME 'md_pi_monitora_processos.id_protocolo_integrado_monitoramento_processos' , 'id_md_pi_monitora_processos', 'COLUMN' ");*/
			  	} 

				if (BancoSEI::getInstance() instanceof InfraMySql){
			  		BancoSEI::getInstance()->executarSql("Alter TABLE md_pi_monitora_processos CHANGE id_protocolo_integrado_pacote_envio id_md_pi_pacote_envio BIGINT(20)");
			  	} else if (BancoSEI::getInstance() instanceof InfraSqlServer){

			  		
			  		BancoSEI::getInstance()->executarSql("EXEC sp_RENAME 'md_pi_monitora_processos.id_protocolo_integrado_pacote_envio' , 'id_md_pi_pacote_envio', 'COLUMN' ");
			  	} 

			  	/*$objInfraMetaBD->adicionarChavePrimaria('md_pi_monitora_processos','pk_id_md_pi_monitora_processos',array('id_md_pi_monitora_processos'));*/

  				$objInfraMetaBD->adicionarChaveEstrangeira('fk_md_pi_monit_processo_ativ','md_pi_monitora_processos',array('id_atividade'),'atividade',array('id_atividade'));
				$objInfraMetaBD->adicionarChaveEstrangeira('fk_md_pi_monit_processo_pacote','md_pi_monitora_processos',array('id_md_pi_pacote_envio'),'md_pi_pacote_envio',array('id_md_pi_pacote_envio'));	
					
  				$objInfraMetaBD->criarIndice('md_pi_monitora_processos','i01_md_pi_monitora_processos',array('id_atividade'));
				$objInfraMetaBD->criarIndice('md_pi_monitora_processos','i02_md_pi_monitora_processos',array('id_md_pi_pacote_envio'));


				/*$objProtocoloIntegradoPacoteRN = new ProtocoloIntegradoPacoteEnvioRN();
				$objProtocoloIntegradoPacoteDTO = new ProtocoloIntegradoPacoteEnvioDTO();
  				$numPacotes =  $objProtocoloIntegradoPacoteRN->contar($objProtocoloIntegradoPacoteDTO);*/

        $maxIdPacote = $this->getMaxIdPacote();

				if (BancoSEI::getInstance() instanceof InfraMySql){
			  		BancoSEI::getInstance()->executarSql('create table seq_md_pi_pacote_envio (id bigint not null primary key AUTO_INCREMENT, campo char(1) null) AUTO_INCREMENT = '.($maxIdPacote+1));
			  	} else if (BancoSEI::getInstance() instanceof InfraSqlServer){
			  		BancoSEI::getInstance()->executarSql('create table seq_md_pi_pacote_envio (id bigint identity('.($maxIdPacote+1).',1), campo char(1) null)');
			  	} else if (BancoSEI::getInstance() instanceof InfraOracle){
			  		BancoSEI::getInstance()->criarSequencialNativa('seq_md_pi_pacote_envio', ($maxIdPacote+1));
			  	}

				/*$objProtocoloIntegradoMonitoraProcessoRN = new ProtocoloIntegradoMonitoramentoProcessosRN();
				$objProtocoloIntegradoMonitoraProcessoDTO = new ProtocoloIntegradoMonitoramentoProcessosDTO();
  				$numAtividadesPacotes =  $objProtocoloIntegradoMonitoraProcessoRN->contar($objProtocoloIntegradoMonitoraProcessoDTO);*/

        $maxIdProcesso = $this->getMaxIdProcesso();

				if (BancoSEI::getInstance() instanceof InfraMySql){
			  		BancoSEI::getInstance()->executarSql('create table seq_md_pi_monitora_processos (id bigint not null primary key AUTO_INCREMENT, campo char(1) null) AUTO_INCREMENT = '.($maxIdProcesso+1));
			  	} else if (BancoSEI::getInstance() instanceof InfraSqlServer){
			  		BancoSEI::getInstance()->executarSql('create table seq_md_pi_monitora_processos (id bigint identity('.($maxIdProcesso+1).',1), campo char(1) null)');
			  	} else if (BancoSEI::getInstance() instanceof InfraOracle){
			  		BancoSEI::getInstance()->criarSequencialNativa('seq_md_pi_monitora_processos', ($maxIdProcesso+1));
			  	}

			  	BancoSEI::getInstance()->executarSql('update infra_parametro set valor=\''.$versao.'\' where nome=\'PI_VERSAO\';');
  		}else if(trim($strVersaoPreviaModuloProtocoloIntegrado)<'1.1.2'){

  			 $resultado["erro"] = "Erro instalando/atualizando M�dulo Protocolo Integrado no SEI. Vers�o ".$strVersaoPreviaModuloProtocoloIntegrado." n�o pode ser atualizada para vers�o ".$this->versaoAtualDesteModulo;
  				return $resultado;
  		}
		
		return $resultado;

		

  }
  private function instalarv112($strVersaoInstaladaModuloProtocoloIntegrado){

  		$erros = null;
  		$comandosExecutados = '';
  		$resultado = array();
  		$resultado["operacoes"] = null;
  		if(
  			//Verifica se n�o possui m�dulo pr�vios cadastrados
  			InfraString::isBolVazia($strVersaoInstaladaModuloProtocoloIntegrado) || 
  			// Atualizar vers�o pr�via.Neste caso,Atualizar do m�dulo 1.1.1 para 1.1.2
  			intval($strVersaoInstaladaModuloProtocoloIntegrado)<=intval($this->versaoAtualDesteModulo)){

  			if(intval($strVersaoInstaladaModuloProtocoloIntegrado)==intval($this->versaoAtualDesteModulo)){
  				
  				$resultado["erro"] = "Erro instalando/atualizando M�dulo Protocolo Integrado no SEI. Vers�o ".$strVersaoInstaladaModuloProtocoloIntegrado." j� instalada";
  				return $resultado;
  			}

	    	BancoSEI::getInstance()->executarSql("CREATE TABLE protocolo_integrado_pacote_envio (id_protocolo_integrado_pacote_envio bigint(20) NOT NULL AUTO_INCREMENT,id_protocolo bigint(20) NOT NULL,
		  			dth_metadados datetime DEFAULT NULL,dth_situacao datetime DEFAULT NULL,sta_integracao char(2) NOT NULL,arquivo_metadados MEDIUMBLOB,arquivo_erro blob,num_tentativas_envio int(11) DEFAULT '0',
		  			dth_agendamento_executado varchar(45) DEFAULT NULL,PRIMARY KEY (id_protocolo_integrado_pacote_envio),KEY fk_pacote_envio_protocolo_integrado_protocolo (id_protocolo),
		  			CONSTRAINT fk_pacote_envio_protocolo_integrado_protocolo FOREIGN KEY (id_protocolo) REFERENCES protocolo (id_protocolo) ON DELETE CASCADE ON UPDATE CASCADE) ENGINE=InnoDB;");
			
			
			BancoSEI::getInstance()->executarSql("CREATE TABLE protocolo_integrado_monitoramento_processos (id_protocolo_integrado_monitoramento_processos bigint(20) NOT NULL AUTO_INCREMENT,
			  		id_atividade int(11) NOT NULL,dth_cadastro datetime DEFAULT NULL,id_protocolo_integrado_pacote_envio bigint(20) NOT NULL,PRIMARY KEY (id_protocolo_integrado_monitoramento_processos),
			  		KEY id_atividade_idx (id_atividade),KEY fk_protocolo_integrado_monitoramento_processos_pacote (id_protocolo_integrado_pacote_envio),
			  		CONSTRAINT fk_protocolo_integrado_monitoramento_processos_atividade FOREIGN KEY (id_atividade) REFERENCES atividade (id_atividade) ON DELETE CASCADE ON UPDATE CASCADE,
			  		CONSTRAINT fk_protocolo_integrado_monitoramento_processos_pacote_envio FOREIGN KEY (id_protocolo_integrado_pacote_envio) 
			  		REFERENCES protocolo_integrado_pacote_envio (id_protocolo_integrado_pacote_envio) ON DELETE CASCADE ON UPDATE CASCADE) ENGINE=InnoDB;");  
			
			
			BancoSEI::getInstance()->executarSql("CREATE TABLE protocolo_integrado (id_protocolo_integrado bigint(20) NOT NULL AUTO_INCREMENT,id_tarefa int(11) DEFAULT NULL, 
		      		sin_publicar char(1) NOT NULL DEFAULT 'N',mensagem_publicacao varchar(255) NOT NULL,PRIMARY KEY (id_protocolo_integrado),KEY fk_protocolo_integrado_tarefa_idx (id_tarefa), 
		      		CONSTRAINT fk_protocolo_integrado_tarefa FOREIGN KEY (id_tarefa) REFERENCES tarefa (id_tarefa) ON DELETE CASCADE ON UPDATE CASCADE) ENGINE=InnoDB;");
			

			BancoSEI::getInstance()->executarSql("insert into protocolo_integrado (id_tarefa,sin_publicar,mensagem_publicacao) select id_tarefa,'N',nome from tarefa");

				$objProtocoloIntegradoRN = new ProtocoloIntegradoRN();
				$tarefasPublicacao =  $objProtocoloIntegradoRN->montaTarefasPadraoPublicacao();
				foreach($tarefasPublicacao as $key=>$value){

						BancoSEI::getInstance()->executarSql("UPDATE protocolo_integrado SET sin_publicar='S' WHERE id_tarefa= ".$value." ");

				}
		      	
			
			
			BancoSEI::getInstance()->executarSql("CREATE TABLE protocolo_integrado_parametros (id_protocolo_integrado_parametros bigint(20) NOT NULL AUTO_INCREMENT,url_webservice varchar(255) NOT NULL,
		  			quantidade_tentativas int(11) NOT NULL,email_administrador varchar(255) NOT NULL,dth_ultimo_processamento datetime DEFAULT NULL,login_webservice varchar(10) DEFAULT NULL,
		  			senha_webservice varchar(20) DEFAULT NULL,sin_executando_publicacao char(1) NOT NULL DEFAULT 'N',sin_publicacao_restritos char(1) NOT NULL DEFAULT 'S',num_atividades_carregar int(11) 
		  			DEFAULT NULL, PRIMARY KEY (id_protocolo_integrado_parametros)) ENGINE=InnoDB;");
			
			
			
			$numCountElementos = BancoSEI::getInstance()->executarSql("select * from protocolo_integrado_parametros");
			if ($numCountElementos==0){

				BancoSEI::getInstance()->executarSql("INSERT INTO protocolo_integrado_parametros (id_protocolo_integrado_parametros,url_webservice,quantidade_tentativas,email_administrador,
		  			login_webservice,senha_webservice,sin_executando_publicacao,sin_publicacao_restritos,num_atividades_carregar) VALUES (1,'https://protocolointegrado.gov.br/ProtocoloWS/integradorService?wsdl'
		  			,15,'','','','N','S',100000);");
			}
			
			/*
			$numCountElementos = BancoSEI::getInstance()->executarSql("select * from infra_agendamento_tarefa where comando='ProtocoloIntegradoAgendamentoRN::publicarProtocoloIntegrado'");
			if ($numCountElementos==0){
				BancoSEI::getInstance()->executarSql("INSERT INTO infra_agendamento_tarefa (id_infra_agendamento_tarefa, descricao, comando, sta_periodicidade_execucao, periodicidade_complemento, sin_ativo) 
					VALUES ((select max(iat.id_infra_agendamento_tarefa)+1 from infra_agendamento_tarefa iat), 'Processo de Publica��o do PI', 'ProtocoloIntegradoAgendamentoRN::publicarProtocoloIntegrado', 
					'D', '2', 'S');");
			}
			
			$numCountElementos = BancoSEI::getInstance()->executarSql("select * from infra_agendamento_tarefa where comando='ProtocoloIntegradoAgendamentoRN::notificarNovosPacotesNaoSendoGerados'");
			if ($numCountElementos==0){
				BancoSEI::getInstance()->executarSql("INSERT INTO infra_agendamento_tarefa (id_infra_agendamento_tarefa, descricao, comando, sta_periodicidade_execucao, periodicidade_complemento, 
					parametro, sin_ativo) VALUES ((select max(iat.id_infra_agendamento_tarefa)+1 from infra_agendamento_tarefa iat), 'Agendamento do alarme de e-mail disparado quando novos pacotes n�o 
					est�o sendo gerados', 'ProtocoloIntegradoAgendamentoRN::notificarNovosPacotesNaoSendoGerados', 'D', '12', '2', 'S');");
			}      
			
			$numCountElementos = BancoSEI::getInstance()->executarSql("select * from infra_agendamento_tarefa where comando='ProtocoloIntegradoAgendamentoRN::notificarProcessosComFalhaPublicacaoProtocoloIntegrado'");
			if ($numCountElementos==0){
				BancoSEI::getInstance()->executarSql("INSERT INTO infra_agendamento_tarefa (id_infra_agendamento_tarefa,descricao,comando,sta_periodicidade_execucao,periodicidade_complemento,sin_ativo)
					VALUES ((select max(iat.id_infra_agendamento_tarefa)+1 from infra_agendamento_tarefa iat),'Agendamento do alarme de e-mail disparado quando h� falha na publoca��o de pacotes',
					'ProtocoloIntegradoAgendamentoRN::notificarProcessosComFalhaPublicacaoProtocoloIntegrado','D','17','S');");
			}      
			*/
			$bolExiste = BancoSEI::getInstance()->executarSql('select * from infra_parametro where nome=\'PI_VERSAO\'');
			if (InfraString::isBolVazia($strVersaoInstaladaModuloProtocoloIntegrado)){
				
				BancoSEI::getInstance()->executarSql('insert into infra_parametro(nome,valor) values(\'PI_VERSAO\', \''.$this->versaoAtualDesteModulo.'\')');	
			}
			else{
				
				BancoSEI::getInstance()->executarSql('update infra_parametro set valor=\''.$this->versaoAtualDesteModulo.'\' where nome=\'PI_VERSAO\';');
			}
		}
		return $resultado;
  }
  private function getMaxIdAgendamento(){
      
      $objAgendamentoDTO = new InfraAgendamentoTarefaDTO();
      $objAgendamentoRN = new InfraAgendamentoTarefaRN();
      $objAgendamentoDTO->retNumIdInfraAgendamentoTarefa();
      $numMaxIdAgendamento = 0;
      
      $objAgendamentoBD = new InfraAgendamentoTarefaBD(BancoSEI::getInstance());
      $objAgendamentoDTO->retNumIdInfraAgendamentoTarefa();
      $arrAgendamentos =  $objAgendamentoBD->listar($objAgendamentoDTO);
      foreach($arrAgendamentos as $key =>$value){

            $idAgendamento = $value->getNumIdInfraAgendamentoTarefa();
            if($idAgendamento>$numMaxIdAgendamento){

                $numMaxIdAgendamento = $idAgendamento;
            }
      }
      return $numMaxIdAgendamento;
       
      
  }
  private function getMaxIdPacote(){
              
    $objPacoteDTO = new ProtocoloIntegradoPacoteEnvioDTO();
    $objPacoteRN = new ProtocoloIntegradoPacoteEnvioRN();
    $objPacoteDTO->retNumIdProtocoloIntegradoPacoteEnvio();
    $numMaxIdPacote = 0;
              
    $objPacoteBD = new ProtocoloIntegradoPacoteEnvioBD(BancoSEI::getInstance());
    $objPacoteDTO->retNumIdProtocoloIntegradoPacoteEnvio();
    $arrPacotes =  $objPacoteBD->listar($objPacoteDTO);
    foreach($arrPacotes as $key =>$value){

          $idPacote = $value->getNumIdProtocoloIntegradoPacoteEnvio();
          if($idPacote>$numMaxIdPacote){

              $numMaxIdPacote = $idPacote;
          }
    }
    return $numMaxIdPacote;


  }
  private function getMaxIdProcesso(){
              
    $objProcessoDTO = new ProtocoloIntegradoMonitoramentoProcessosDTO();
    $objProcessoRN = new ProtocoloIntegradoMonitoramentoProcessosRN();
    $objProcessoDTO->retNumIdProtocoloIntegradoMonitoramentoProcessos();
    $numMaxIdProcesso = 0;
              
    $objProcessoBD = new ProtocoloIntegradoMonitoramentoProcessosBD(BancoSEI::getInstance());
    $objProcessoDTO->retNumIdProtocoloIntegradoMonitoramentoProcessos();
    $arrProcessos =  $objProcessoBD->listar($objProcessoDTO);
    foreach($arrProcessos as $key =>$value){

          $idProcesso = $value->getNumIdProtocoloIntegradoMonitoramentoProcessos();
          if($idProcesso>$numMaxIdProcesso){

              $numMaxIdProcesso = $idProcesso;
          }
    }
    return $numMaxIdProcesso;


  }
  private function recuperaAgendamento($strComando){

       $objAgendamentoDTO = new InfraAgendamentoTarefaDTO();
       $objAgendamentoDTO->retNumIdInfraAgendamentoTarefa();
       $objAgendamentoDTO->setStrComando($strComando);
       $objAgendamentoDTO->setBolExclusaoLogica(false);

       $objAgendamentoRN = new InfraAgendamentoTarefaRN();
       $objAgendamentoBD = new InfraAgendamentoTarefaBD(BancoSEI::getInstance());
       $objAgendamentoDTO =  $objAgendamentoBD->consultar($objAgendamentoDTO);
       
       return $objAgendamentoDTO;
       
    }
    private function cadastrarAgendamento($objAgendamentoDTO){

       $objAgendamentoRN = new InfraAgendamentoTarefaRN();
       $objAgendamentoBD = new InfraAgendamentoTarefaBD(BancoSEI::getInstance());
       $objAgendamentoDTO =  $objAgendamentoBD->cadastrar($objAgendamentoDTO);
       
       return $objAgendamentoDTO;
       
    }
    private function adicionarAgendamento($strComando, $strDescricao, $strPeriodicidadeExecucao,$strComplementoPeriodicidade,$strParametro=null){

        $objAgendamentoDTO = $this->recuperaAgendamento($strComando);
        if ($objAgendamentoDTO==null){

            $objAgendamentoDTO = new InfraAgendamentoTarefaDTO();
            $objAgendamentoDTO->setNumIdInfraAgendamentoTarefa(null);
            $objAgendamentoDTO->setStrComando($strComando);
            $objAgendamentoDTO->setStrDescricao($strDescricao);
            $objAgendamentoDTO->setStrStaPeriodicidadeExecucao($strPeriodicidadeExecucao);
            $objAgendamentoDTO->setStrPeriodicidadeComplemento($strComplementoPeriodicidade);
            if($strParametro==null){

                 $objAgendamentoDTO->setStrParametro('');
              
            }else{

                 $objAgendamentoDTO->setStrParametro($strParametro);
            }
            $objAgendamentoDTO->setDthUltimaExecucao(null);
            $objAgendamentoDTO->setDthUltimaConclusao(null);
            $objAgendamentoDTO->setStrSinSucesso('N');
            $objAgendamentoDTO->setStrEmailErro('');
            
            $objAgendamentoDTO->setStrSinAtivo('N');
            $objAgendamentoDTO = $this->cadastrarAgendamento($objAgendamentoDTO);
       }
       return $objAgendamentoDTO;
    }

	
	
}
?>