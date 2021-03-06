<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use DB;

class Estabelecimento extends Model
{
    protected $fillable = [
    	'nome', 
    	'cnpj', 
		'endereco',
		'bairro',
		'cep',
		'cidade',
		'uf',
		'telefone'
	];

	public static function get_all()
	{
		$estabelecimentos = DB::select(DB::raw("SELECT DISTINCT e.id, CONCAT(e.endereco, ', ', e.bairro, ', ', e.cidade, ', ', e.uf) as endereco
			FROM estabelecimentos e 
				JOIN notas n on e.id = n.estabelecimento_id 
				JOIN produtos p on p.nfce_id = n.id
			WHERE SUBSTR(p.ncm, 1, 2) = '30'"));

        return $estabelecimentos;
	}

	public static function get_all_from($id)
	{
		$estabelecimentos = DB::table('estabelecimentos')
			->select(DB::raw('CONCAT(estabelecimentos.endereco, ", ", estabelecimentos.bairro, ", ", estabelecimentos.cidade, ", ", estabelecimentos.uf) as endereco, (SELECT count(*) from notas where notas.estabelecimento_id = estabelecimentos.id) as qtde_notas, (SELECT count(*) from produtos join notas on notas.id = produtos.nfce_id where notas.estabelecimento_id = estabelecimentos.id) as qtde_produtos'))
			->where("id",$id)
            ->get();

        return $estabelecimentos;
	}
	
	public static function get_products($id)
	{
		$produtos = DB::select(DB::raw("SELECT p.descricao, ROUND(p.valor, 2) as valor, DATE_FORMAT(n.data_emissao, '%d/%b/%Y') as data_emissao
			FROM produtos p 
				JOIN notas n ON p.nfce_id = n.id 
				JOIN estabelecimentos e ON n.estabelecimento_id = e.id
			WHERE e.id = '$id'"));

        return $produtos;
	}
}
