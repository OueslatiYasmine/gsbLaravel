<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VisiteurController extends Controller
{
    public function traiterSelection(Request $request)
    {
        // Récupérer l'ID du visiteur sélectionné
        $visiteurId = $request->input('lstVisiteurs');
        
        // Récupérer les informations du visiteur depuis la base de données
        $visiteur = DB::table('visiteur')->where('id', $visiteurId)->first();
        
        // Rediriger vers une vue avec les informations du visiteur
        return view('detail_visiteur', ['visiteur' => $visiteur]);
    }
} 