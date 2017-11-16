<?php

namespace App\Http\Controllers;

use App\Emotion;
use Illuminate\Http\Request;

class EmotionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return Emotion::all(); //bad
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
     * @param  \App\Emotion  $emotion
     * @return \Illuminate\Http\Response
     */
    public function show(Emotion $emotion)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Emotion  $emotion
     * @return \Illuminate\Http\Response
     */
    public function edit(Emotion $emotion)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Emotion  $emotion
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Emotion $emotion)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Emotion  $emotion
     * @return \Illuminate\Http\Response
     */
    public function destroy(Emotion $emotion)
    {
        //
    }
}
