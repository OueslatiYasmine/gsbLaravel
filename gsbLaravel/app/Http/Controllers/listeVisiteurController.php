<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\MyApp\PdoGsb;

// Made by Ts1 : 2c : Last update - Send session to our view
class listeVisiteurController extends Controller
{
    private $monPdo;

    public function __construct() {
        $this->monPdo = new PdoGsb();
    }

    public function AfficherVisiteur(Request $request)
    {
        $gestionnaire = $request->session()->get('gestionnaire');
        $lesVisiteurs = $this->monPdo->getVisiteur();
        $periodes = $this->monPdo->getMoisAnneeDisponibles();
        $etats = $this->monPdo->getLesEtats();
        
        return view('ListeVisiteur', [
            'liste' => $lesVisiteurs,
            'gestionnaire' => $gestionnaire,
            'periodes' => $periodes,
            'etats' => $etats
        ]);
    }

    public function traiterSelection(Request $request)
    {
        // Récupération des paramètres
        $idVisiteur = $request->input('lstVisiteurs');
        $mois = $request->input('lstMois');
        $annee = $request->input('lstAnnees');
        
        // Initialisation des variables
        $visiteurSelectionne = null;
        $fichesFrais = [];
        
        // Ne récupérer les informations que si un visiteur est sélectionné
        if (!empty($idVisiteur)) {
            $visiteurSelectionne = $this->monPdo->getLeVisiteurSelectionne($idVisiteur);
            $fichesFrais = $this->monPdo->getLesFichesFraisFiltre($idVisiteur, $mois, $annee);
        }
        
        // Récupération des listes pour les selects
        $lesVisiteurs = $this->monPdo->getVisiteur();
        $periodes = $this->monPdo->getMoisAnneeDisponibles();
        $etats = $this->monPdo->getLesEtats();
        
        // Retour à la vue avec les données
        return view('ListeVisiteur', [
            'liste' => $lesVisiteurs,
            'visiteurSelectionne' => $visiteurSelectionne,
            'fichesFrais' => $fichesFrais,
            'gestionnaire' => $request->session()->get('gestionnaire'),
            'periodes' => $periodes,
            'moisSelectionne' => $mois,
            'anneeSelectionnee' => $annee,
            'etats' => $etats
        ]);
    }

    public function updateEtat(Request $request)
    {
        $idVisiteur = $request->input('idVisiteur');
        $mois = $request->input('mois');
        $nouvelEtat = $request->input('nouvelEtat');

        if ($this->monPdo->updateEtatFicheFrais($idVisiteur, $mois, $nouvelEtat)) {
            return redirect()
                ->route('chemin_visiteur')
                ->with('success', 'État de la fiche mis à jour avec succès');
        }

        return redirect()
            ->route('chemin_visiteur')
            ->with('error', 'Erreur lors de la mise à jour de l\'état de la fiche');
    }

    public function exporterXML(Request $request, $idVisiteur, $mois)
    {
        // Récupération des données
        $visiteur = $this->monPdo->getLeVisiteurSelectionne($idVisiteur);
        $ficheFrais = $this->monPdo->getLesFichesFraisFiltre($idVisiteur, substr($mois, 4, 2), substr($mois, 0, 4));
        
        if (empty($ficheFrais)) {
            return redirect()->back()->with('error', 'Aucune fiche de frais trouvée');
        }

        // Création du document XML
        $xml = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><ficheFrais></ficheFrais>');
        
        // Ajout des informations du visiteur
        $xmlVisiteur = $xml->addChild('visiteur');
        $xmlVisiteur->addChild('id', $visiteur['id']);
        $xmlVisiteur->addChild('nom', $visiteur['nom']);
        $xmlVisiteur->addChild('prenom', $visiteur['prenom']);
        
        // Ajout des informations de la fiche
        foreach ($ficheFrais as $fiche) {
            $xmlFiche = $xml->addChild('fiche');
            $xmlFiche->addChild('mois', $fiche['mois']);
            $xmlFiche->addChild('nbJustificatifs', $fiche['nbJustificatifs']);
            $xmlFiche->addChild('montantValide', $fiche['montantValide']);
            $xmlFiche->addChild('dateModif', $fiche['dateModif']);
            $xmlFiche->addChild('etat', $fiche['idEtat']);
        }
        
        // Génération du nom de fichier
        $filename = 'fiche_frais_' . $idVisiteur . '_' . $mois . '.xml';
        
        // Création de la réponse avec le fichier XML
        $response = response($xml->asXML(), 200);
        $response->header('Content-Type', 'application/xml');
        $response->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
        
        return $response;
    }
}
