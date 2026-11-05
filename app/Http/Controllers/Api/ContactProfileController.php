<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ContactProfileController extends Controller
{
    public function show(Request $request, $id){
        return $request->user()->contacts()->findOrFail($id)->profile;
    }
    public function store(Request $request, $id){
        $contact = $request->user()->contacts()->findOrFail($id);
        return $contact->profile()->updateOrCreate(
            ['contact_id'=>$id],
            $request->validate(['c1'=>'required','c2'=>'required','c3'=>'required'])
        );
    }
}