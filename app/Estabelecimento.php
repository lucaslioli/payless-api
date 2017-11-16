<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use DB;

class Estabelecimento extends Model
{
    protected $fillable = [
    	'nome', 
    	'cnpj', 
    	'endereco'
	];

	public static function get_all(){
		$estabelecimentos = DB::table('estabelecimentos')
            ->select(DB::raw('estabelecimentos.*, (SELECT count(*) from notas where notas.estabelecimento_id = estabelecimentos.id) as qtde_notas, (SELECT count(*) from produtos join notas on notas.id = produtos.nfce_id where notas.estabelecimento_id = estabelecimentos.id) as qtde_produtos'))
            ->get();

        return $estabelecimentos;
	}

	public static function get_all_from($id){
		$estabelecimentos = DB::table('estabelecimentos')
			->select(DB::raw('estabelecimentos.*, (SELECT count(*) from notas where notas.estabelecimento_id = estabelecimentos.id) as qtde_notas, (SELECT count(*) from produtos join notas on notas.id = produtos.nfce_id where notas.estabelecimento_id = estabelecimentos.id) as qtde_produtos'))
			->where("id",$id)
            ->get();

        return $estabelecimentos;
	}
	
	public static function get_products($id){
		$produtos = DB::select(DB::raw("SELECT p.descricao, ROUND(p.valor, 2), n.data_emissao
			FROM produtos p 
				JOIN notas n ON p.nfce_id = n.id 
				JOIN estabelecimentos e ON n.estabelecimento_id = e.id
			WHERE e.id = '$id'"));

        return $produtos;
	}
}
