<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use PdoGsb;

// Modified by Ts1 : 2c : Updated login logic 
class connexionController extends Controller
{
    function connecter()
    {
        // Si déjà connecté, rediriger vers le sommaire
        if (session()->has('visiteur') || session()->has('gestionnaire')) {
            return redirect()->route('chemin_sommaire');
        }
        return view('connexion')->with('erreurs',null);
    }

    public function valider(Request $request)
    {

    $login = $request->input('login');
    $mdp = $request->input('mdp');

    $visiteur = PdoGsb::getInfosVisiteur($login, $mdp);
    $gestionnaire = PdoGsb::getInfosGestionnaire($login, $mdp);


    $erreurs = [];


    if (!is_array($visiteur) && !is_array($gestionnaire))
    {
        $erreurs[] = "Login ou mot de passe incorrect(s)";
        return view('connexion')->with('erreurs', $erreurs);
    }


    if (is_array($gestionnaire))
    {
        session(['gestionnaire' => $gestionnaire]);
        return redirect()->route('chemin_sommaire');
    }

    if (is_array($visiteur))
    {
        session(['visiteur' => $visiteur]);
        return redirect()->route('chemin_sommaire');
    }

    return redirect()->back()->withErrors(['message' => 'Erreur inconnue.']);
    }
    function deconnecter()
    {
            session()->forget(['visiteur', 'gestionnaire']);
            return redirect()->route('chemin_connexion');
    }
}
