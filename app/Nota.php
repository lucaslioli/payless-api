<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Nota extends Model
{
    protected $fillable = [
    	'user_id', 
    	'estabelecimento_id', 
    	'serie', 
    	'data_emissao', 
    	'hora_emissao', 
    	'chave_acesso'
    ];
}
