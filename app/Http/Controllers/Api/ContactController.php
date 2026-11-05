<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    public function index(Request $request){
        return $request->user()->contacts()->with('profile')->get();
    }
    public function store(Request $request){
        return $request->user()->contacts()->create($request->validate([
            'nombre'=>'required','telefono'=>'nullable','email'=>'nullable|email'
        ]));
    }
    public function show(Request $request, $id){
        return $request->user()->contacts()->with('profile')->findOrFail($id);
    }
    public function update(Request $request, $id){
        $c = $request->user()->contacts()->findOrFail($id);
        $c->update($request->only(['nombre','telefono','email']));
        return $c;
    }
    public function destroy(Request $request, $id){
        $request->user()->contacts()->findOrFail($id)->delete();
        return response()->json(['message'=>'Contacto eliminado']);
    }
}