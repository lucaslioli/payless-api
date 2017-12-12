<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use DOMDocument;
use DOMElement;
use Exeption;
use DB;

class Nfce extends Model
{
    protected $fillable = ['access_key'];
    
    /**
     * [get_content description]
     * @param  String $key Chave de acesso da NFC-e
     * @return String      Tabela com todas as informações da NFC-e
     */
    public static function get_nfce_content($key){

        $link = "https://www.sefaz.rs.gov.br/ASP/AAE_ROOT/NFE/SAT-WEB-NFE-NFC_QRCODE_1.asp?chNFe=".$key;

        // Busca conteúdo do link
        $content = utf8_encode(file_get_contents($link));
        // Verifica se o link é válido
        if(strpos($content, "#FFFFEA") === FALSE)
            return FALSE;
        // Elimina os espapaços indesejados da string
        $content = trim(preg_replace('/\s+/', ' ', $content));
        // Seleciona apenas a parte onde o background é da cor #FFFFEA
        $content = explode("#FFFFEA", $content)[1];
        // Separa apenas a tabela que possui o conteúdo de interesse
        $content = strstr($content, "<table");
        $content = substr($content, 0, strpos($content, "Versão XSLT"));
        $content = substr($content, 0, strrpos($content, "<tr>"))."</table>";
        
        return $content;

    }

    /**
     * Extrai os dados do Estabelecimento a partir de objeto DOMElement 
     * @param  DOMElement $table1 Objeto que contem a 1ª parte dos dados
     * @param  DOMElement $table2 Objeto que contem a 2ª parte dos dados
     * @return Array              Array com os dados do estabelecimento
     */
    public static function get_company_data(DOMElement  $table1, DOMElement  $table2){

        $content = array();
        // TABELA 1 - Dados do ESTABELECIMENTO: Nome, CNPJ, Inscrição Estadual
        $cols = $table1->getElementsByTagName('td');
        foreach ($cols as $col) {
            $class = $col->getAttribute("class");
            if($class == "NFCCabecalho_SubTitulo"){
                $content['nome'] = $col->nodeValue;

            }else if ($class == "NFCCabecalho_SubTitulo1") {
                // ex: " CNPJ: 00.000.000/0000-00 Inscrição Estadual: 0000000000"
                $data = explode(" ", $col->nodeValue);
                $content['cnpj'] = $data[2];
                $content['inscricao_estadual'] = $data[5];
            }
        }

        // TABELA 2 - Dados do ESTABELECIMENTO: Endereço
        $cols = $table2->getElementsByTagName('td');
        foreach ($cols as $col) {
            $class = $col->getAttribute("class");
            if ($class == "NFCCabecalho_SubTitulo1") {
                $content['endereco'] = $col->nodeValue;
            }
        }

        return $content;

    }

    /**
     * Extrai os dados da NFC-e a partir de objeto DOMElement
     * @param  DOMElement $table Objeto que contem a tabela com os dados
     * @return Array             Array com os dados da NFC-e
     */
    public static function get_nfce_data(DOMElement $table){

        $content = array();
        // TABELA 4 - DAdos da NFC-e: Número, Serie, Data, Chave e Protocolo
        $cols = $table->getElementsByTagName('td');
        $flag = NULL;
        foreach ($cols as $col) {
            if(stripos($col->nodeValue, "nfc-e") !== FALSE){
                // ex: " NFC-e nº: 0000 Série: 000 Data de Emissão: dd/mm/yyyy h:i:s"
                $data = explode(" ", $col->nodeValue);
                $content['numero'] = $data[3];
                $content['serie'] = $data[5];
                $content['data_emissao'] = $data[9];
                $content['hora_emissao'] = $data[10];
            
            }else if(stripos($col->nodeValue, "protocolo") !== FALSE){
                // ex: " Protocolo de Autorização 0000000000000000"
                $data = explode(": ", $col->nodeValue);
                $content['protocolo'] = $data[1];
            
            }else if(stripos($col->nodeValue, "chave de acesso") !== FALSE){
                $flag = 1;
            
            }else if ($flag) {
                // ex: 0000 0000 0000 0000 0000 0000 0000 0000 0000 0000 0000
                $content["chave_acesso"] = $col->nodeValue;
                $flag++;
            }
        }
        return $content;

    }

    /**
     * Extrai os dados dos Produtos a partir de objeto DOMElement
     * @param  DOMElement $table Objeto que contem a tabela com todos os produtos
     * @return Array             Array com os dados dos produtos
     */
    public static function get_products_data(DOMElement $table){

        $data = array();
        // TABELA 6 - Dados sobre os PRODUTOS
        $rows = $table->getElementsByTagName('tr');

        for ($i=1; $i < $rows->length; $i++) { 
            $cols = $rows->item($i)->getElementsByTagName('td');
            
            $produto['codigo'] = $cols->item(0)->nodeValue;
            $produto['descricao'] = $cols->item(1)->nodeValue;
            $produto['valor'] = $cols->item(4)->nodeValue;
            $produto['un'] = $cols->item(3)->nodeValue;

            array_push($data,$produto);
            
        }

        return $data;

    }

    /**
     * Script responsável por extrair os dados da nota
     * @param  int  $key         chave de acesso da nota com 44 dígitos
     * @param  int  $dont_insert Opcional. Se for 1, não irá gravar no banco. Default: 0
     * @return json              Retorna JSON com todos os dados ou mensagem de erro
     */
    public static function get_all_data($key, int $dont_insert = 0){

        $content = self::get_nfce_content($key);

        if($content == FALSE)
            return 'Chave de Acesso inválida!';

        // Inicializa um objeto DOM
        $dom = new DOMDocument;
        // Descarta os espaços em branco
        $dom->preserveWhiteSpace = false;   
        // Carrega o conteúdo como HTML
        $dom->loadHTML(utf8_decode($content));
        // Seleciona todos os elementos table
        $tables = $dom->getElementsByTagName('table');
        
        $data = array();

        // TABELA 1 e 2 - Dados do ESTABELECIMENTO: Nome, CNPJ, Inscrição Estadual e Endereço
        $data['estabelecimento'] = self::get_company_data($tables->item(1), $tables->item(2));

        # TABELA 3 - Cabeçalho sobre a nota (sem uso aparente)

        // TABELA 4 - DAdos da NFC-e: Número, Serie, Data, Chave e Protocolo
        $data['nfce'] = self::get_nfce_data($tables->item(4));

        # TABELA 5 - Consumidor geralmente não identificado (sem uso aparente)

        // TABELA 6 - Dados sobre os PRODUTOS
        $data['produtos'] = self::get_products_data($tables->item(6));

        # TABELA 7 - Valores totais da nota (sem uso aparente)
        
        if($dont_insert)
            return $data;
        
        try {
            Nfce::create(['access_key' => $key]);
            return $data;

        } catch (\Illuminate\Database\QueryException $e) {
            return "202";
            // return $e->getMessage();
        }

    }

    /**
     * Based in the key which already has been inserted, 
     * this function will get all the data from each NFC-e
     * and will register all them in the other tables
     * @return Void Cadastra dado no banco e imprime na tela casos de erro
     */
    public static function integrate_nfces(){
        $nfces = DB::table('nfces')->get();

        foreach ($nfces as $nfce) {
            DB::beginTransaction();

            $ERROS = 0;
            // Verifica se Nota existe, se sim, pula para a próxima
            $nota = DB::table('notas')->where('chave_acesso', $nfce->access_key)->first();
            if(!is_null($nota)){
                echo "<br/>Nota fiscal já cadastrada. ".$nota->chave_acesso;
                continue;
            }

            $data = self::get_all_data($nfce->access_key, true);

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
                        'endereco' => $data->estabelecimento->endereco
                    ]);
                } catch (\Illuminate\Database\QueryException $e) {
                    echo "<br/>Falha ao cadastrar estabelecimento ".$data->estabelecimento->nome;
                    $ERROS++;
                    continue;
                }
            }

            try {
                $nota = Nota::create([
                    'user_id' => 1,
                    'estabelecimento_id' => $estabelecimento->id,
                    'serie' => $data->nfce->serie,
                    'chave_acesso' => $nfce->access_key,
                    'data_emissao' => $data->nfce->data_emissao,
                    'hora_emissao' => $data->nfce->hora_emissao
                ]);
            } catch (\Illuminate\Database\QueryException $e) {
                echo "<br/>Falha ao cadastrar nota do ID: ".$nfce->id." e chave ".$nfce->access_key;
                $ERROS++;
                continue;
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
                            'un' => strtoupper($produto->un)
                        ]);
                    } catch (\Illuminate\Database\QueryException $e) {
                        echo "<br/>Falha ao cadastrar produto ".$produto->descricao." da nota ".$nfce->access_key;
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
                            echo "<br/>Falha ao atualizar produto ".$produto->descricao." da nota ".$nota->id." ERRO: ".$e;
                            $ERROS++;
                            break;
                        }
                    }
                }
            } // Fecha foreach de produtos
            if($ERROS){
                DB::rollback();
                echo "<br/>ERRO - ".$nfce->access_key."";
                return;
            }else{
                DB::commit();
                echo "Ok - ".$nfce->access_key."<br/>";
            }
        } // Fecha foreach de nfces
        return;
    }

}
