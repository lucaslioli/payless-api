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
    	'un'
	];

	public static function get_home_data(){
		$produto_result = DB::table('produtos')
			->distinct()
			->select('descricao')
            ->get();
        return $produto_result;
	}

	public static function get_product_data($desc){
		$menor_preco = DB::table('produtos')->select('id')->where('descricao', $desc)->orderBy('valor')->first();

		$produto_result = DB::select(DB::raw("
			SELECT 
				p.id,
				p.descricao, 
				p.un,
				(SELECT min(valor) from produtos p2 where p2.descricao = p.descricao) as menor_valor, 
				(SELECT max(valor) from produtos p2 where p2.descricao = p.descricao) as maior_valor,
				e.endereco,
				n.data_emissao,
				n.hora_emissao
			FROM produtos p 
				JOIN notas n ON p.nfce_id = n.id
				JOIN estabelecimentos e ON n.estabelecimento_id = e.id
			WHERE p.id = '$menor_preco->id'"));

        return $produto_result;
	}
}
