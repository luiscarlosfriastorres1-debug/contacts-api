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
        $data = $request->validate([
            'name'         => 'required|string|max:255',
            'phone_number' => 'required|string|max:30',
            'email'        => 'nullable|email',
        ]);
        if($request->user()->contacts()->where('phone_number', $data['phone_number'])->exists()) {
            return response()->json(['message' => 'El número de teléfono ya está registrado'], 422);
        }
        $contacto = $request->user()->contacts()->create($data);

        return response()->json(['message'=>'Contacto creado','contacto'=>$contacto], 201);
    }


    public function show(Request $request, $id){
        return $request->user()->contacts()->with('profile')->findOrFail($id);
    }


    public function update(Request $request, $id){
        $c = $request->user()->contacts()->findOrFail($id);
        $c->update($request->only(['name','phone_number','email']));
        return $c;
    }

    
    public function destroy(Request $request, $id){
        $request->user()->contacts()->findOrFail($id)->delete();
        return response()->json(['message'=>'Contacto eliminado']);
    }
    

}