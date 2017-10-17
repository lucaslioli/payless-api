<?php

namespace App\Http\Controllers;

use App\Nfce;
use Illuminate\Http\Request;

class NfceController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Nfce  $nfce
     * @return \Illuminate\Http\Response
     */
    public function show($key)
    {
        if(strlen($key)!=44)
            return 'Error 44';

        $data = Nfce::get_all_data($key);
        return response()->json($data);
        // return $data->toJson();
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Nfce  $nfce
     * @return \Illuminate\Http\Response
     */
    public function edit(Nfce $nfce)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Nfce  $nfce
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Nfce $nfce)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Nfce  $nfce
     * @return \Illuminate\Http\Response
     */
    public function destroy(Nfce $nfce)
    {
        //
    }
}
