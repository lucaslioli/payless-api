<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use DB;

class Produto extends Model
{
    protected $fillable = [
    	'nfce_id', 
    	'codigo', 
    	'descricao', 
    	'valor', 
		'un',
		'ean',
		'ncm'
	];

	public static function get_home_data()
	{
		$produto_result = DB::select(DB::raw("SELECT p.id, p.descricao, ROUND(p.valor, 2) as valor, 
				DATE_FORMAT(n.data_emissao, '%d/%b/%Y') as data_emissao, n.estabelecimento_id
			FROM produtos p JOIN notas n ON p.nfce_id = n.id
			WHERE SUBSTR(p.ncm, 1, 2) = '30'"));

        return $produto_result;
	}

	public static function get_product_data($desc)
	{
		// $menor_preco = DB::table('produtos')->select('id')->where('descricao', $desc)->orderBy('valor')->first();

		$produto_result = DB::select(DB::raw("SELECT p.id, p.descricao, p.un, p.valor, 
				e.endereco, n.data_emissao, n.hora_emissao, n.estabelecimento_id
			FROM produtos p JOIN notas n ON p.nfce_id = n.id JOIN estabelecimentos e ON n.estabelecimento_id = e.id
			WHERE p.id = '$desc'"));

		// VERSÃO COM MAIOR E MENOR PREÇO
		// $produto_result = DB::select(DB::raw("SELECT p.id, p.descricao, p.un,
		// 		(SELECT min(valor) from produtos p2 where p2.descricao = p.descricao) as menor_valor, 
		// 		(SELECT max(valor) from produtos p2 where p2.descricao = p.descricao) as maior_valor,
		// 		e.endereco, n.data_emissao, n.hora_emissao 
		// 	FROM produtos p JOIN notas n ON p.nfce_id = n.id JOIN estabelecimentos e ON n.estabelecimento_id = e.id
		// 	WHERE p.id = '$menor_preco->id'"));

        return $produto_result;
	}
}
