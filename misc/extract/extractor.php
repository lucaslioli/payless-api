<?php
	/**
	 * Script responsável por extrair os dados da nota
	 * @param  int  $_POST contendo a chave de acesso da nota com 44 dígitos
	 * @return void        imprime conjunto com todos os dados dos produtos e do estabelecimento
	 */
	
	$key = $_POST["key"];

	$link = "https://www.sefaz.rs.gov.br/ASP/AAE_ROOT/NFE/SAT-WEB-NFE-NFC_QRCODE_1.asp?chNFe=".$key;

	// Busca conteúdo do link
	$content = utf8_encode(file_get_contents($link));
	// Elimina os espapaços indesejados da string
	$content = trim(preg_replace('/\s+/', ' ', $content));
	// Seleciona apenas a parte onde o backgrund é da cor
	$content = explode("#FFFFEA", $content)[1];
	// Separa apenas a tabela que possui o conteúdo de interesse
	$content = strstr($content, "<table");
	$content = substr($content, 0, strpos($content, "Versão XSLT"));
	$content = substr($content, 0, strrpos($content, "<tr>"))."</table>";

	// Abre/cria arquivo para armazenar o conteúdo
	/*$file_name = "nfce_content.html";
	$file 	   = fopen($file_name, 'w+');
	
	// Escreve no arquivo e o fecha logo em seguida
	fwrite($file, $content);
	fclose($file)*/;

	// Inicializa um objeto DOM
	$dom = new DOMDocument;
	// Descarta os espaços em branco
	$dom->preserveWhiteSpace = false;   
	// Carrega o conteúdo como HTML
	$dom->loadHTML(utf8_decode($content));
	// Seleciona todos os elementos table
	$tables = $dom->getElementsByTagName('table');
	
	// TABELA 1 - Dados do ESTABELECIMENTO: Nome, CNPJ, Inscrição Estadual
	$cols = $tables->item(1)->getElementsByTagName('td');
	foreach ($cols as $col) {
		$class = $col->getAttribute("class");
		if($class == "NFCCabecalho_SubTitulo"){
			echo "<b>Estabelecimento: </b>".$col->nodeValue;

		}else if ($class == "NFCCabecalho_SubTitulo1") {
			// ex: " CNPJ: 00.000.000/0000-00 Inscrição Estadual: 0000000000"
			$data = explode(" ", $col->nodeValue);
			echo "<br><b>CNPJ: </b>".$data[2];
			echo "<br><b>Inscrição Estadual: </b>".$data[5];
		}
	}

	// TABELA 2 - Dados do ESTABELECIMENTO: Endereço
	$cols = $tables->item(2)->getElementsByTagName('td');
	foreach ($cols as $col) {
		$class = $col->getAttribute("class");
		if ($class == "NFCCabecalho_SubTitulo1") {
			echo "<br><b>Endereço: </b>".$col->nodeValue;
		}
	}

	# TABELA 3 - Cabeçalho sobre a nota (sem uso aparente)
	echo "<br/>";

	// TABELA 4 - DAdos da NFC-e: Número, Serie, Data, Chave e Protocolo
	$cols = $tables->item(4)->getElementsByTagName('td');
	$flag = NULL;
	foreach ($cols as $col) {
		if(stripos($col->nodeValue, "nfc-e") !== FALSE){
			// ex: " NFC-e nº: 0000 Série: 000 Data de Emissão: dd/mm/yyyy h:i:s"
			$data = explode(" ", $col->nodeValue);
			echo "<br/><b>NFC-e n°: </b>".$data[3];
			echo "<b> Série: </b>".$data[5];
			echo "<br/><b>Data de Emissão: </b>".$data[9];
			echo "<b> Hora: </b>".$data[10];
		
		}else if(stripos($col->nodeValue, "protocolo") !== FALSE){
			// ex: " Protocolo de Autorização 0000000000000000"
			$data = explode(": ", $col->nodeValue);
			echo "<br/><b>Protocolode Autorização: </b>".$data[1];
		
		}else if(stripos($col->nodeValue, "chave de acesso") !== FALSE){
			$flag = 1;
		
		}else if ($flag) {
			// ex: 0000 0000 0000 0000 0000 0000 0000 0000 0000 0000 0000
			echo "<br/><b>Chave de Acesso:</b> ".$col->nodeValue;
		}
	}

	# TABELA 5 - Consumidor geralmente não identificado (sem uso aparente)
	echo "<br/>";

	// TABELA 6 - Dados sobre os PRODUTOS
	$rows = $tables->item(6)->getElementsByTagName('tr');

	for ($i=1; $i < $rows->length; $i++) { 
		$cols = $rows->item($i)->getElementsByTagName('td');
		
		echo "<hr/>";
		echo "<b>Código: </b>".$cols->item(0)->nodeValue;
		echo "<br/><b>Descrição: </b>".$cols->item(1)->nodeValue;
		echo "<br/><b>Valor: </b>".$cols->item(4)->nodeValue;
		echo "<b> / </b>".$cols->item(3)->nodeValue;
	}

	# TABELA 7 - Valores totais da nota (sem uso aparente)
	return;
?>