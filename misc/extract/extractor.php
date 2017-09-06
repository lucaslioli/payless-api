<?php
	/**
	 * Script responsável por extrair os dados da nota
	 * @param  int   $_POST contendo a chave de acesso da nota com 44 dígitos
	 * @return void         imprime conjunto com todos os dados dos produtos e do estabelecimento
	 *
	 *  Ex.1: https://www.sefaz.rs.gov.br/NFCE/NFCE-COM.aspx?chNFe=43161275315333012115655130000089011048579176&nVersao=100&tpAmb=1&dhEmi=323031362D31322D31345431333A34343A35362D30323A3030&vNF=84.36&vICMS=5.15&digVal=4D39595266537639537A6A594376632B4C577A72446476503450773D&cIdToken=000001&cHashQRCode=D8C1CBB2D61FADD666D030BEAA9E0DC007DD0519
	 * Key: 4316 1275 3153 3301 2115 6551 3000 0089 0110 4857 9176
	 * URL iframe: https://www.sefaz.rs.gov.br/ASP/AAE_ROOT/NFE/SAT-WEB-NFE-NFC_QRCODE_1.asp?chNFe=43161275315333012115655130000089011048579176
	 * 
	 * Ex.2: https://www.sefaz.rs.gov.br/NFCE/NFCE-COM.aspx?chNFe=43170495597571000242650190000478471125701799&nVersao=100&tpAmb=1&dhEmi=323031372d30342d31335430383a33353a32342d30333a3030&vNF=14.08&vICMS=0.00&digVal=446638794a792b5757716651312f53754d33527247414e38754e673d&cIdToken=000001&cHashQRCode=3BDA3C49A1AC0FB88A9021046C1AF51EAF5D43BF
	 * Key: 4317 0495 5975 7100 0242 6501 9000 0478 4711 2570 1799
	 * URL iframe: https://www.sefaz.rs.gov.br/ASP/AAE_ROOT/NFE/SAT-WEB-NFE-NFC_QRCODE_1.asp?chNFe=43170495597571000242650190000478471125701799
	 * 
	 * http://www.xpathtester.com/xpath
	 */
	
	$key = $_POST["key"];

	$link = "https://www.sefaz.rs.gov.br/ASP/AAE_ROOT/NFE/SAT-WEB-NFE-NFC_QRCODE_1.asp?chNFe=".$key;

	$conteudo = file_get_contents($link);
	$conteudo = trim(preg_replace('/\s+/', ' ', $conteudo));
	$conteudo = explode('<div id="nfce">', $conteudo);

	var_dump($conteudo);
?>