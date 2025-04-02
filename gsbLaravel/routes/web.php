<?php

use Illuminate\Support\Facades\Route;
// Chemin des contrôleurs
use App\Http\Controllers\connexionController;
use App\Http\Controllers\etatFraisController;
use App\Http\Controllers\gererFraisController;
use App\Http\Controllers\listeVisiteurController;
use App\Http\Controllers\VisiteurController;
use App\Http\Controllers\VoirFraisController;

// Création des groupes de routes
Route::controller(connexionController::class)->group(function ()
{
    Route::get('/', 'connecter')->name('chemin_connexion');
    Route::post('/', 'valider')->name('chemin_valider');
    Route::get('/deconnexion', 'deconnecter')->name('chemin_deconnexion');
});

// Route pour le sommaire
Route::get('/sommaire', function () {
    if (!session()->has('visiteur') && !session()->has('gestionnaire')) {
        return redirect()->route('chemin_connexion');
    }
    return view('sommaire')->with([
        'visiteur' => session('visiteur'),
        'gestionnaire' => session('gestionnaire')
    ]);
})->name('chemin_sommaire');

Route::controller(etatFraisController::class)->group(function ()
{
    Route::get('/selectionMois', 'selectionnerMois')->name('chemin_selectionMois');
    Route::post('/listeFrais', 'voirFrais')->name('chemin_voir_frais_mois');
});

Route::controller(gererFraisController::class)->group(function ()
{
    Route::get('/gererFrais', 'saisirFrais')->name('chemin_gestionFrais');
    Route::post('/sauvegarderFrais', 'sauvegarderFrais')->name('chemin_sauvegardeFrais');
});

Route::controller(listeVisiteurController::class)->group(function ()
{
    // Route GET pour afficher la page
    Route::get('/listeDesVisiteurs', 'AfficherVisiteur')->name('chemin_liste_visiteurs');
    // Route POST pour traiter la sélection
    Route::post('/listeDesVisiteurs', 'traiterSelection')->name('chemin_visiteur');
    Route::post('/updateEtat', [App\Http\Controllers\listeVisiteurController::class, 'updateEtat'])->name('update_etat');
    Route::get('/exportXML/{idVisiteur}/{mois}', [App\Http\Controllers\listeVisiteurController::class, 'exporterXML'])->name('export_xml');
});

Route::controller(VoirFraisController::class)->group(function ()
{
    // Route GET pour afficher le formulaire
    Route::get('/voirFrais', 'index')->name('chemin_voir_frais');
    // Route POST pour traiter le formulaire
    Route::post('/voirFrais', 'store')->name('chemin_listeFrais');
});
