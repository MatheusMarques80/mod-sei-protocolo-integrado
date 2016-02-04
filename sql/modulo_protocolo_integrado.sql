/*
	Aten��o : Este script leva em considera��o que a base de dados do SEI est� localizada no schema sei e a base de dados do SIP est� no schema sip
    
*/
-- Cria��o de tabela para armazenar pacotes de envio ao protocolo integrado
CREATE TABLE `sei`.`protocolo_integrado_pacote_envio` (
-- id da tabela
id_protocolo_integrado_pacote_envio bigint(20) NOT NULL AUTO_INCREMENT,
-- id do protocolo ao qual o pacote est� relacionado
id_protocolo bigint(20) NOT NULL,
-- Data que os metadados do pacote(XML de envio ao PI) foram gerados
dth_metadados datetime DEFAULT NULL,
-- Data indicando quando foi a �ltima atualiza��o no pacote
dth_situacao datetime DEFAULT NULL,
-- Situa��o do pacote
sta_integracao char(2) NOT NULL,
-- XMl contendo os metadados enviados ao Protocolo Integrado
arquivo_metadados MEDIUMBLOB,
-- XML contendo o arquivo de erro de envio caso o envio do pacote tenha falhado
arquivo_erro blob,
-- N�mero de vezes que o pacote � enviado ao PI
num_tentativas_envio int(11) DEFAULT '0',
-- Data em que o agendamento que gerou o pacote foi rodado
dth_agendamento_executado varchar(45) DEFAULT NULL,
-- Configurando chave prim�ria
PRIMARY KEY (id_protocolo_integrado_pacote_envio),
-- Chave estrangeira relacionando a tabela protocolo com a protocolo_integrado_pacote_envio.
CONSTRAINT fk_pacote_envio_protocolo_integrado_protocolo FOREIGN KEY (id_protocolo) REFERENCES protocolo (id_protocolo) ON DELETE CASCADE ON UPDATE CASCADE) ENGINE=InnoDB ;

-- Cria��o de tabela para armazenar opera��es dos pacotes que devem subir ao Protocolo Integrado
CREATE TABLE `sei`.`protocolo_integrado_monitoramento_processos` (
-- id da tabela
id_protocolo_integrado_monitoramento_processos bigint(20) NOT NULL AUTO_INCREMENT,
-- ids das opera��es a serem enviadas ao Protocolo Integrado
id_atividade int(11) NOT NULL,
-- Data que as opera��es foram inclu�das num pacote de envio ao protocolo integrado
dth_cadastro datetime DEFAULT NULL,
-- Pacote ao qual pertence cada atividade
id_protocolo_integrado_pacote_envio bigint(20) NOT NULL,
-- Definindo chave prim�ria
PRIMARY KEY (id_protocolo_integrado_monitoramento_processos),
-- Cria��o de �ndice na coluna id_atividade
KEY id_atividade_idx (id_atividade),KEY fk_protocolo_integrado_monitoramento_processos_pacote (id_protocolo_integrado_pacote_envio),
-- Chave estrangeira relacionando a tabela atividade com a protocolo_integrado_monitoramento_processos. 
CONSTRAINT fk_protocolo_integrado_monitoramento_processos_atividade FOREIGN KEY (id_atividade) REFERENCES atividade (id_atividade) ON DELETE CASCADE ON UPDATE CASCADE,
-- Chave estrangeira relacionando a tabela protocolo_integrado_pacote_envio com a protocolo_integrado_monitoramento_processos. 
CONSTRAINT fk_protocolo_integrado_monitoramento_processos_pacote_envio FOREIGN KEY (id_protocolo_integrado_pacote_envio) 
REFERENCES protocolo_integrado_pacote_envio (id_protocolo_integrado_pacote_envio) ON DELETE CASCADE ON UPDATE CASCADE) ENGINE=InnoDB;

-- Cria��o de tabela para controlar quais opera��es de processos devem ser publicadas no Protocolo Integrado
CREATE TABLE `sei`.`protocolo_integrado` (
-- id da tabela
id_protocolo_integrado bigint(20) NOT NULL AUTO_INCREMENT,
-- id das tarefas mapeadas
id_tarefa int(11) DEFAULT NULL, 
-- Flag indicando se a tarefa deve ser publicada ou n�o
sin_publicar char(1) NOT NULL DEFAULT 'N',
-- Mensagem para publica��o
mensagem_publicacao varchar(255) NOT NULL,
-- Definindo chave prim�ria
PRIMARY KEY (id_protocolo_integrado),KEY fk_protocolo_integrado_tarefa_idx (id_tarefa), 
-- Chave estrangeira relacionando a tabela tarefa com a protocolo_integrado. 
CONSTRAINT fk_protocolo_integrado_tarefa FOREIGN KEY (id_tarefa) REFERENCES tarefa (id_tarefa) ON DELETE CASCADE ON UPDATE CASCADE) ENGINE=InnoDB;

-- Preenchendo tabela com todas as opera��es de processos configuradas para n�o serem publicadas no Protocolo Integrado
insert into `sei`.`protocolo_integrado` (id_tarefa,sin_publicar,mensagem_publicacao) select id_tarefa,'N',nome from `sei`.`tarefa`;

-- Sequ�ncia de Uptades para configurar as 22 opera��es aptas por padr�o a serem publicadas no Protocolo Integrado
update `sei`.`protocolo_integrado` SET sin_publicar='S' where id_tarefa=1;
update `sei`.`protocolo_integrado` SET sin_publicar='S' where id_tarefa=18;
update `sei`.`protocolo_integrado` SET sin_publicar='S' where id_tarefa=19;
update `sei`.`protocolo_integrado` SET sin_publicar='S' where id_tarefa=20;
update `sei`.`protocolo_integrado` SET sin_publicar='S' where id_tarefa=21;
update`sei`.`protocolo_integrado` SET sin_publicar='S' where id_tarefa=28;
update `sei`.`protocolo_integrado` SET sin_publicar='S' where id_tarefa=29;
update `sei`.`protocolo_integrado` SET sin_publicar='S' where id_tarefa=32;
update `sei`.`protocolo_integrado` SET sin_publicar='S' where id_tarefa=42;
update `sei`.`protocolo_integrado` SET sin_publicar='S' where id_tarefa=43;
update `sei`.`protocolo_integrado` SET sin_publicar='S' where id_tarefa=44;
update `sei`.`protocolo_integrado` SET sin_publicar='S' where id_tarefa=45;
update `sei`.`protocolo_integrado` SET sin_publicar='S' where id_tarefa=48;
update `sei`.`protocolo_integrado` SET sin_publicar='S' where id_tarefa=58;
update `sei`.`protocolo_integrado` SET sin_publicar='S' where id_tarefa=63;
update `sei`.`protocolo_integrado` SET sin_publicar='S' where id_tarefa=65;
update `sei`.`protocolo_integrado` SET sin_publicar='S' where id_tarefa=82;
update `sei`.`protocolo_integrado` SET sin_publicar='S' where id_tarefa=101;
update `sei`.`protocolo_integrado` SET sin_publicar='S' where id_tarefa=102;
update `sei`.`protocolo_integrado` SET sin_publicar='S' where id_tarefa=103;
update `sei`.`protocolo_integrado` SET sin_publicar='S' where id_tarefa=104;
update `sei`.`protocolo_integrado` SET sin_publicar='S' where id_tarefa=105;
update `sei`.`protocolo_integrado` SET sin_publicar='S' where id_tarefa=106;
update `sei`.`protocolo_integrado` SET sin_publicar='S' where id_tarefa=107;
update `sei`.`protocolo_integrado` SET sin_publicar='S' where id_tarefa=112;

-- Cria��o de tabela para armazenar par�metros para publica��o de processos no Protocolo Integrado
CREATE TABLE  `sei`.`protocolo_integrado_parametros` (
-- id da tabela
id_protocolo_integrado_parametros bigint(20) NOT NULL AUTO_INCREMENT,
-- URL ao WebService de Publica��o
url_webservice varchar(255) NOT NULL,
-- Quantidade m�xima de tentativas de envio de pacotes ao Protocolo Integrado
quantidade_tentativas int(11) NOT NULL,
-- E-mail do administrador do m�dulo
email_administrador varchar(255) NOT NULL,
-- Data de �ltima vez que o script de publica��o rodou
dth_ultimo_processamento datetime DEFAULT NULL,login_webservice varchar(10) DEFAULT NULL,
-- Senha de acesso ao Webservice
senha_webservice varchar(20) DEFAULT NULL,
-- Flag para controle de publica��es rodando simultaneamente
sin_executando_publicacao char(1) NOT NULL DEFAULT 'N',
-- Flag que habilita ou n�o a publica��o de processos restritos no PI 
sin_publicacao_restritos char(1) NOT NULL DEFAULT 'S',
-- N�mero m�ximo de atividades a serem publicadas em cada execu��o 
num_atividades_carregar int(11) DEFAULT NULL,
-- Definindo chave prim�ria
 PRIMARY KEY (id_protocolo_integrado_parametros)) ENGINE=InnoDB;

-- Inserindo par�metros padr�es de publica��o do m�dulo
INSERT INTO `sei`.`protocolo_integrado_parametros` (id_protocolo_integrado_parametros,url_webservice,quantidade_tentativas,email_administrador,
login_webservice,senha_webservice,sin_executando_publicacao,sin_publicacao_restritos,num_atividades_carregar)
VALUES (1,'https://protocolointegrado.gov.br/ProtocoloWS/integradorService?wsdl',15,'','','','N','S',100000);

-- Definindo vers�o do M�dulo de Publica��o de Processos do SEI no Protocolo Integrado
insert into `sei`.`infra_parametro` (nome,valor) values('PI_VERSAO', '1.1.2');
