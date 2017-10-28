<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Produto extends Model
{
    protected $fillable = [
    	'nfce_id', 
    	'codigo', 
    	'descricao', 
    	'valor', 
    	'un'
    ];}
