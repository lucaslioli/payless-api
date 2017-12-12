<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use DOMDocument;
use DOMElement;
use Exeption;
use DB;

class Nota extends Model
{
    protected $fillable = [
    	'user_id', 
    	'estabelecimento_id', 
    	'serie', 
    	'data_emissao', 
    	'hora_emissao', 
		'chave_acesso',
		'protocolo'
	];
	
	/**
	 * Igual a get_nfce_content() porém, extrai os dados mais completos
	 * @param  String $key Chave de acesso da NFC-e
	 * @return String      Tabela com todas as informações da NFC-e
	 */
	public static function get_nfce_content($key)
	{
		$link = "C:/Users/lucas/Desktop/Precify/exemplo-nota-tabs-1.html";

		// Busca conteúdo do link
		$content = utf8_encode(file_get_contents($link));
		// Verifica se o link é válido
		if(strpos($content, "chaveNFe") === FALSE)
			return FALSE;
		// Elimina os espapaços indesejados da string
		$content = trim(preg_replace('/\s+/', ' ', $content));
		// Seleciona apenas a parte do body que contem as abas (outra opção é '</script><body>')
		$content = explode("</b></li></ul>", $content)[1];
		// Separa apenas a parte que possui os dados, elemina os botões do final
		$content = substr($content, 0, strpos($content, "</body>"));

		return $content;
	}

	/**
	 * Igual a get_company_data() porém, extrai os dados mais completos
	 * @param  DOMElement $div Objeto que contem os dados
	 * @return Array           Array com os dados do estabelecimento
	 */
	public static function get_company_data(DOMElement  $div)
	{
		$content = array();
		// Dados do ESTABELECIMENTO: Nome, CNPJ, Inscrição Estadual
		$cols = $div->getElementsByTagName('td');
		foreach ($cols as $col) {
			$label = $col->getElementsByTagName('label')->item(0);

			if($label){
				$label->nodeValue = utf8_decode($label->nodeValue);

				if(stripos($label->nodeValue, "Razão Social") !== FALSE){
					$content['nome'] = $col->getElementsByTagName('span')->item(0)->nodeValue;
				
				}else if(stripos($label->nodeValue, "CNPJ") !== FALSE){
					$content['cnpj'] = $col->getElementsByTagName('span')->item(0)->nodeValue;
				
				}else if(stripos($label->nodeValue, "Endereço") !== FALSE){
					$content['endereco'] = $col->getElementsByTagName('span')->item(0)->nodeValue;
				
				}else if(stripos($label->nodeValue, "Bairro") !== FALSE){
					$content['bairro'] = $col->getElementsByTagName('span')->item(0)->nodeValue;
				
				}else if(stripos($label->nodeValue, "CEP") !== FALSE){
					$content['cep'] = $col->getElementsByTagName('span')->item(0)->nodeValue;
				
				}else if(stripos($label->nodeValue, "Município") !== FALSE && !isset($content['cidade'])){
					$cidade = $col->getElementsByTagName('span')->item(0)->nodeValue;
					$cidade = explode('- ', $cidade);
					if(isset($cidade[1])){
						$content['cidade'] = $cidade[1];
					}else{
						$content['cidade'] = $cidade;
					}
				
				}else if(stripos($label->nodeValue, "UF") !== FALSE){
					$content['uf'] = $col->getElementsByTagName('span')->item(0)->nodeValue;
				
				}else if(stripos($label->nodeValue, "Telefone") !== FALSE){
					$content['telefone'] = $col->getElementsByTagName('span')->item(0)->nodeValue;
				}
			}
		}

		return $content;
	}

	/**
	 * Igual a get_nfce_data() porém, extrai os dados mais completos
	 * @param  DOMElement $div Objeto que contem a tabela com os dados
	 * @return Array           Array com os dados da NFC-e
	 */
	public static function get_nfce_data(DOMElement $div)
	{
		$content = array();
		// Dados da NFC-e: Número, Serie, Data e Protocolo
		$cols = $div->getElementsByTagName('td');
		foreach ($cols as $col) {
			$label = $col->getElementsByTagName('label')->item(0);
			
			if($label){
				$label->nodeValue = utf8_decode($label->nodeValue);

				if(stripos($label->nodeValue, "Série") !== FALSE){
					$content['serie'] = $col->getElementsByTagName('span')->item(0)->nodeValue;
				
				}else if(stripos($label->nodeValue, "Data de Emissão") !== FALSE){
					// ex: " dd/mm/yyyy h:i:s"
					$data = explode(" ", $col->getElementsByTagName('span')->item(0)->nodeValue);
					$content['data_emissao'] = $data[0];
					$content['hora_emissao'] = substr($data[1], 0, strpos($data[1], "-"));
				
				}else if(stripos($label->nodeValue, "Protocolo") !== FALSE){
					$input = $div->getElementsByTagName('input')->item(0);
					$content['protocolo'] = $input->getAttribute('value');
				}
			}
		}
		return $content;
	}

	/**
	 * Extrai os dados dos Produtos a partir de objeto DOMElement
	 * @param  DOMElement $div Objeto que contem a tabela com todos os produtos
	 * @return Array           Array com os dados dos produtos
	 */
	public static function get_products_data(DOMElement $div)
	{
		$data = array();
		$tables = $div->getElementsByTagName('table');
		
		/**
		 * Cada produto na nota possui 2 tabelas principais (e internas) de dados dentro da div "aba_nft_3";
		 * Na aba de produtos, o cabeçalho seria a tabela 0, ao buscar pela lista de tabelas, por isso $i=1;
		 * A lista de tabelas retorna também as tabelas filhas, o que permitiria a replicação de dados;
		 * Cada uma das 2 tabelas principais possui a classe "toggle" ou "toggable";
		 * Essas classes são verificadas, assim é possível pegar todas as TDs internas com segurança;
		 */
		
		$c = 0;
		for ($i=1; $i < $tables->length; $i++) {
			$classe = $tables->item($i)->getAttribute("class");

			if(stripos($classe, "toggle") !== FALSE){
				$cols = $tables->item($i)->getElementsByTagName('td');
				
				foreach ($cols as $col) {
					if($col->getAttribute("class")=="fixo-prod-serv-descricao"){
						$data[$c]['descricao'] = $col->nodeValue;
					}else if ($col->getAttribute("class")=="fixo-prod-serv-uc"){
						$data[$c]['un'] = $col->nodeValue;
					}
				}

			}else if(stripos($classe, "toggable") !== FALSE){
				$cols = $tables->item($i)->getElementsByTagName('td');

				foreach ($cols as $col) {
					$label = $col->getElementsByTagName('label')->item(0);
					
					if($label){
						$label->nodeValue = utf8_decode($label->nodeValue);
		
						if(stripos($label->nodeValue, "Código do Produto") !== FALSE){
							$data[$c]['codigo'] = $col->getElementsByTagName('span')->item(0)->nodeValue;
						
						}else if(stripos($label->nodeValue, "Código NCM") !== FALSE){
							$data[$c]['ncm'] = $col->getElementsByTagName('span')->item(0)->nodeValue;
						
						}else if(stripos($label->nodeValue, "Código EAN Comercial") !== FALSE){
							$data[$c]['ean'] = $col->getElementsByTagName('span')->item(0)->nodeValue;
						
						}else if(stripos($label->nodeValue, "Valor unitário de comercialização") !== FALSE){
							$data[$c]['valor'] = $col->getElementsByTagName('span')->item(0)->nodeValue;
						}
					}
				}
				$c++;
			}
		}

		return $data;
	}

	/**
	 * Igual a get_all_data() porém, extrai os dados mais completos
	 * @param  int  $key       chave de acesso da nota com 44 dígitos
	 * @return void            retorna array com todos os dados ou mensagem de erro
	 */
	public static function get_all_data($key)
	{
		$content = self::get_nfce_content($key);

    	if($content == FALSE)
    		return 'Chave de Acesso inválida!';

		// Inicializa um objeto DOM
		$dom = new DOMDocument;
		// Descarta os espaços em branco
		$dom->preserveWhiteSpace = false;
		// Carrega o conteúdo como HTML (Utilizado o @ devido a erro interno da lib)
		@$dom->loadHTML(utf8_decode($content));

		// Seleciona todos os elementos table
		$elements = $dom->getElementsByTagName('div');
		
		$data = array();
		
		// Dados da NF-e - div aba_nft_0
		$data['nfce'] = self::get_nfce_data($elements->item(1));
		$data['nfce']['chave_acesso'] = $key;

		// Dados da Empresa - div aba_nft_1
		$data['estabelecimento'] = self::get_company_data($elements->item(3));

		// Dados da Empresa - div aba_nft_3
		$data['produtos'] = self::get_products_data($elements->item(17));
		
		return $data;
	}

	/**
	 * Creates entries on database for 'estabelecimento', 'nota' and 'produto' 
	 * extracted from the provided nfce if they don't already exist
	 * Existing entries for a specific product on a specified establishment 
	 * are updated with it's new price
	 * @return String	http response status code that indicates the success or failure of the operation
	 */
	public static function store_nfce($key)
	{
		DB::beginTransaction();

		$ERROS = 0;
		// Verifica se Nota existe, se sim, pula para a próxima
		$nota = DB::table('notas')->where('chave_acesso', $key)->first();
		if(!is_null($nota)){
			// echo "<br/>Nota fiscal já cadastrada. ".$nota->chave_acesso;
			return "400";
		}

		$data = self::get_all_data($key);
		
		if($data == "400")
			return "400";

		$data = json_decode(json_encode ($data), FALSE);

		$data->nfce->data_emissao = preg_replace('#(\d{2})/(\d{2})/(\d{4})#', '$3-$2-$1', $data->nfce->data_emissao);

		// Verifica se Estabelecimento existe, se não, cadastra
		$estabelecimento = DB::table('estabelecimentos')->where([
			['cnpj', $data->estabelecimento->cnpj], 
			['endereco', $data->estabelecimento->endereco]])->first();

		if(is_null($estabelecimento)){
			try {
				$estabelecimento = Estabelecimento::create([
					'nome' => $data->estabelecimento->nome,
					'cnpj' => $data->estabelecimento->cnpj,
					'endereco' => $data->estabelecimento->endereco,
					'bairro' => $data->estabelecimento->bairro,
					'cep' => $data->estabelecimento->cep,
					'cidade' => $data->estabelecimento->cidade,
					'uf' => $data->estabelecimento->uf,
					'telefone' => $data->estabelecimento->telefone
				]);
			} catch (\Illuminate\Database\QueryException $e) {
				// echo "<br/>Falha ao cadastrar estabelecimento ".$data->estabelecimento->nome;
				$ERROS++;
			}
		}
		
		try {
			$nota = Nota::create([
				'user_id' => 1,
				'estabelecimento_id' => $estabelecimento->id,
				'serie' => $data->nfce->serie,
				'chave_acesso' => $key,
				'data_emissao' => $data->nfce->data_emissao,
				'hora_emissao' => $data->nfce->hora_emissao
			]);
		} catch (\Illuminate\Database\QueryException $e) {
			// echo "<br/>Falha ao cadastrar nota";
			$ERROS++;
		}

		foreach ($data->produtos as $produto) {
			$produto_result = DB::table('produtos')
				->join('notas', 'produtos.nfce_id', '=', 'notas.id')
				->where([
					['produtos.codigo', $produto->codigo], 
					['notas.estabelecimento_id', $estabelecimento->id]])
				->first();

			$produto->valor = floatval(str_replace(",", ".", $produto->valor));

			if(is_null($produto_result)){
				try {
					$produto = Produto::create([
						'nfce_id' => $nota->id,
						'codigo' => $produto->codigo,
						'descricao' => strtoupper($produto->descricao),
						'valor' => $produto->valor,
						'un' => strtoupper($produto->un),
						'ean' => $produto->ean,
						'ncm' => $produto->ncm
					]);
				} catch (\Illuminate\Database\QueryException $e) {
					// echo "<br/>Falha ao cadastrar produto ".$produto->descricao." da nota ".$key;
					$ERROS++;
					break;
				}
			}else{
				if($produto_result->data_emissao > $nota->data_emissao){
					try{
						$produto_result = DB::table('produtos')
							->where('id', $produto_result->id)
							->update(['valor' => $produto->valor]);
					} catch (\Illuminate\Database\QueryException $e) {
						// echo "<br/>Falha ao atualizar produto ".$produto->descricao." da nota ".$nota->id." ERRO: ".$e;
						$ERROS++;
						break;
					}
				}
			}
		} // Fecha foreach de produtos

		if($ERROS){
			DB::rollback();
			// echo "<br/>ERRO - ".$key."";
			return "400";
		}else{
			DB::commit();
			// echo "Ok - ".$key."<br/>";
			return "200";
		}
	}
}
