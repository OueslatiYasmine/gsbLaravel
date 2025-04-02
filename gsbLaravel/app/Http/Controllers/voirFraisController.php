<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\MyApp\PdoGsb;
use Illuminate\Support\Facades\Log;

class VoirFraisController extends Controller
{
    public function index(Request $request)
    {
        // Vérifier si l'utilisateur est connecté (visiteur ou gestionnaire)
        if (!$request->session()->has('visiteur') && !$request->session()->has('gestionnaire')) {
            return redirect()->route('chemin_connexion');
        }

        // Récupérer l'ID du visiteur sélectionné
        $idVisiteur = $request->input('lstVisiteur');
        
        if (empty($idVisiteur)) {
            // Si aucun visiteur n'est sélectionné, afficher la liste des visiteurs
            $pdo = new PdoGsb();
            $lesVisiteurs = $pdo->getVisiteur();
            return view('listeVisiteur', [
                'lesVisiteurs' => $lesVisiteurs,
                'visiteur' => $request->session()->get('visiteur'),
                'gestionnaire' => $request->session()->get('gestionnaire')
            ]);
        }
        
        // Récupérer tous les mois disponibles pour ce visiteur
        $pdo = new PdoGsb();
        $lesMois = $pdo->getLesMoisDisponibles($idVisiteur);
        
        // Si aucun mois n'existe, rediriger avec un message
        if (empty($lesMois)) {
            return back()->with('error', 'Aucune fiche de frais disponible pour ce visiteur');
        }
        
        // Prendre le premier mois disponible
        $moisActuel = key($lesMois);
        
        // Récupérer les données nécessaires
        $lesFraisForfait = $pdo->getLesFraisForfait($idVisiteur, $moisActuel);
        $lesInfosFicheFrais = $pdo->getLesInfosFicheFrais($idVisiteur, $moisActuel);
        
        return view('listeMois', [
            'lesMois' => $lesMois,
            'leMois' => $moisActuel,
            'numMois' => substr($moisActuel, 4, 2),
            'numAnnee' => substr($moisActuel, 0, 4),
            'libEtat' => $lesInfosFicheFrais['libEtat'],
            'dateModif' => $lesInfosFicheFrais['dateModif'],
            'montantValide' => $lesInfosFicheFrais['montantValide'],
            'lesFraisForfait' => $lesFraisForfait,
            'visiteur' => $request->session()->get('visiteur'),
            'gestionnaire' => $request->session()->get('gestionnaire'),
            'idVisiteur' => $idVisiteur
        ]);
    }

    public function store(Request $request)
    {
        // Vérifier si l'utilisateur est connecté (visiteur ou gestionnaire)
        if (!$request->session()->has('visiteur') && !$request->session()->has('gestionnaire')) {
            return redirect()->route('chemin_connexion');
        }

        // Récupérer l'ID du visiteur et le mois sélectionné
        $idVisiteur = $request->input('lstVisiteur');
        $mois = $request->input('lstMois');
        
        if (empty($idVisiteur) || empty($mois)) {
            return back()->with('error', 'Veuillez sélectionner un visiteur et un mois');
        }
        
        // Récupérer les données nécessaires
        $pdo = new PdoGsb();
        $lesMois = $pdo->getLesMoisDisponibles($idVisiteur);
        $lesFraisForfait = $pdo->getLesFraisForfait($idVisiteur, $mois);
        $lesInfosFicheFrais = $pdo->getLesInfosFicheFrais($idVisiteur, $mois);
        
        return view('listefrais', [
            'lesMois' => $lesMois,
            'leMois' => $mois,
            'numMois' => substr($mois, 4, 2),
            'numAnnee' => substr($mois, 0, 4),
            'libEtat' => $lesInfosFicheFrais['libEtat'],
            'dateModif' => $lesInfosFicheFrais['dateModif'],
            'montantValide' => $lesInfosFicheFrais['montantValide'],
            'lesFraisForfait' => $lesFraisForfait,
            'visiteur' => $request->session()->get('visiteur'),
            'gestionnaire' => $request->session()->get('gestionnaire'),
            'idVisiteur' => $idVisiteur
        ]);
    }
}