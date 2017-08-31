<?php
	echo "Busca do conteúdo!<br>";

	$iframe = file_get_contents("https://www.sefaz.rs.gov.br/NFCE/NFCE-COM.aspx?chNFe=43161275315333012115655130000089011048579176&nVersao=100&tpAmb=1&dhEmi=323031362D31322D31345431333A34343A35362D30323A3030&vNF=84.36&vICMS=5.15&digVal=4D39595266537639537A6A594376632B4C577A72446476503450773D&cIdToken=000001&cHashQRCode=D8C1CBB2D61FADD666D030BEAA9E0DC007DD0519");
	
	$divisao = explode('id="iframeConteudo"',$iframe);
	$divisao = explode('"> </iframe>', trim(preg_replace('/\s+/', ' ', $divisao[1])));

	$link_conteudo = substr($divisao[0],strpos($divisao[0],'https'));
	
	$conteudo_nota = file_get_contents($link_conteudo);
	echo $conteudo_nota;
?>
