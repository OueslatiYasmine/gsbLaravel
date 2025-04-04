<?php
namespace App\MyApp;
use PDO;
use Illuminate\Support\Facades\Config;
class PdoGsb{
        private static string $serveur;
        private static string $bdd;
        private static mixed $user;
        private static mixed $mdp;
        private  $monPdo;

/**
 * crée l'instance de PDO qui sera sollicitée
 * pour toutes les méthodes de la classe
 */
	public function __construct()
  {

        self::$serveur='mysql:host=' . Config::get('database.connections.mysql.host');
        self::$bdd='dbname=' . Config::get('database.connections.mysql.database');
        self::$user=Config::get('database.connections.mysql.username') ;
        self::$mdp=Config::get('database.connections.mysql.password');
        $this->monPdo = new PDO(self::$serveur.';'.self::$bdd, self::$user, self::$mdp);
  		$this->monPdo->query("SET CHARACTER SET utf8");
	}
	public function _destruct()
  {
		$this->monPdo =null;
	}


   /**
     * Retourne les informations d'un visiteur
     * @param $login
     * @param $mdp
     * @return mixed l'id, le nom et le prénom sous la forme d'un tableau associatif
     */
	public function getInfosVisiteur($login, $mdp)
  {
		$req = "select visiteur.id as id, visiteur.nom as nom, visiteur.prenom as prenom from visiteur
        where visiteur.login='" . $login . "' and visiteur.mdp='" . $mdp ."'";
    	$rs = $this->monPdo->query($req);
		$ligne = $rs->fetch();
		return $ligne;
	 }


   // Made by Ts1 : 2c : Gestionnaire handler
   /**
     * Retourne les informations d'un visiteur
     * @param $login
     * @param $mdp
     * @return mixed l'id, le nom et le prénom sous la forme d'un tableau associatif
     */
     public function getInfosGestionnaire($login, $mdp)
     {
         // Modifed by Ts1 : 2c : Updated request 
         $req = "SELECT gestionnaire.id AS id, gestionnaire.nom AS nom, gestionnaire.prenom AS prenom
                 FROM gestionnaire
                 WHERE gestionnaire.login = :login AND gestionnaire.mdp = :mdp";

         $stmt = $this->monPdo->prepare($req);
         $stmt->bindParam(':login', $login);
         $stmt->bindParam(':mdp', $mdp);
         $stmt->execute();

         // Récupérer la ligne
         $ligne = $stmt->fetch();
         return $ligne;
     }

// Made by Ts1 : 2c : Visiteur list : Updated to use prepared Request
public function getVisiteur()
{
    $req = "select visiteur.nom as nom, visiteur.prenom as prenom, visiteur.id as id from visiteur";
    $stmt = $this->monPdo->prepare($req);
    $stmt->execute();
    $liste = $stmt->fetchAll(PDO::FETCH_ASSOC);

    return $liste;
}


    /**
     * Retourne sous forme d'un tableau associatif toutes les lignes de frais au forfait
     *  concernées par les deux arguments
     *
     * @param $idVisiteur
     * @param $mois * mois sous la forme aaaamm
     * @return array|false l'id, le libelle et la quantité sous la forme d'un tableau associatif
     */
	public function getLesFraisForfait($idVisiteur, $mois){
		$req = "select fraisforfait.id as idfrais, fraisforfait.libelle as libelle,
		lignefraisforfait.quantite as quantite from lignefraisforfait inner join fraisforfait
		on fraisforfait.id = lignefraisforfait.idfraisforfait
		where lignefraisforfait.idvisiteur ='$idVisiteur' and lignefraisforfait.mois='$mois'
		order by lignefraisforfait.idfraisforfait";
		$res = $this->monPdo->query($req);
		$lesLignes = $res->fetchAll();
		return $lesLignes;
	}

    /**
     * Retourne tous les id de la table FraisForfait
     * @return array|false
     * return un tableau associatif
     */
	public function getLesIdFrais(){
		$req = "select fraisforfait.id as idfrais from fraisforfait order by fraisforfait.id";
		$res = $this->monPdo->query($req);
		$lesLignes = $res->fetchAll();
		return $lesLignes;
	}
/**
 * Met à jour la table ligneFraisForfait
 * Met à jour la table ligneFraisForfait pour un visiteur et
 * un mois donné en enregistrant les nouveaux montants
 *
 * @param $idVisiteur
 * @param $mois * mois sous la forme aaaamm
 * @param $lesFrais * lesFrais tableau associatif de clé idFrais et de valeur la quantité pour ce frais
 * @return void
*/
	public function majFraisForfait($idVisiteur, $mois, $lesFrais){
		$lesCles = array_keys($lesFrais);
		foreach($lesCles as $unIdFrais){
			$qte = $lesFrais[$unIdFrais];
			$req = "update lignefraisforfait set lignefraisforfait.quantite = $qte
			where lignefraisforfait.idvisiteur = '$idVisiteur' and lignefraisforfait.mois = '$mois'
			and lignefraisforfait.idfraisforfait = '$unIdFrais'";
			$this->monPdo->exec($req);
		}

	}

/**
 * @brief Teste si un visiteur possède une fiche de frais pour le mois passé en argument
 *
 * @param $idVisiteur
 * @param $mois  * mois sous la forme aaaamm
 * @return bool
*/
	public function estPremierFraisMois($idVisiteur,$mois)
	{
		$ok = false;
		$req = "select count(*) as nblignesfrais from fichefrais
		where fichefrais.mois = '$mois' and fichefrais.idvisiteur = '$idVisiteur'";
		$res = $this->monPdo->query($req);
		$laLigne = $res->fetch();
		if($laLigne['nblignesfrais'] == 0){
			$ok = true;
		}
		return $ok;
	}

    /**
     * Retourne le dernier mois en cours d'un visiteur
     *
     * @param $idVisiteur
     * @return mixed return le mois sous la forme aaaamm
     */
	public function dernierMoisSaisi($idVisiteur){
		$req = "select max(mois) as dernierMois from fichefrais where fichefrais.idvisiteur = '$idVisiteur'";
		$res = $this->monPdo->query($req);
		$laLigne = $res->fetch();
		$dernierMois = $laLigne['dernierMois'];
		return $dernierMois;
	}

    /**
     * Crée une nouvelle fiche de frais et les lignes de frais au forfait pour un visiteur et un mois donnés
     * récupère le dernier mois en cours de traitement, met à 'CL' son champs idEtat, crée une nouvelle fiche de frais
     * avec un idEtat à 'CR' et crée les lignes de frais forfait de quantités nulles
     * @param $idVisiteur
     * @param $mois * mois sous la forme aaaamm
     * @return void
     */
	public function creeNouvellesLignesFrais($idVisiteur,$mois){
		$dernierMois = $this->dernierMoisSaisi($idVisiteur);
		$laDerniereFiche = $this->getLesInfosFicheFrais($idVisiteur,$dernierMois);
		if($laDerniereFiche['idEtat']=='CR'){
				$this->majEtatFicheFrais($idVisiteur, $dernierMois,'CL');

		}
		$req = "insert into fichefrais(idvisiteur,mois,nbJustificatifs,montantValide,dateModif,idEtat)
		values('$idVisiteur','$mois',0,0,now(),'CR')";
		$this->monPdo->exec($req);
		$lesIdFrais = $this->getLesIdFrais();
		foreach($lesIdFrais as $uneLigneIdFrais){
			$unIdFrais = $uneLigneIdFrais['idfrais'];
			$req = "insert into lignefraisforfait(idvisiteur,mois,idFraisForfait,quantite)
			values('$idVisiteur','$mois','$unIdFrais',0)";
			$this->monPdo->exec($req);
		 }
	}


	/**
	 * Retourne les mois pour lesquels un visiteur a une fiche de frais
	 * @param $idVisiteur
	 * @return array retourne un tableau associatif de clé un mois -aaaamm- et de valeurs l'année et le mois correspondant
	 */
	public function getLesMoisDisponibles($idVisiteur) 
	{
		$req = "SELECT fichefrais.mois AS mois 
		FROM fichefrais 
		WHERE fichefrais.idvisiteur = :idVisiteur 
		ORDER BY fichefrais.mois desc";

		$stmt = $this->monPdo->prepare($req);
		$stmt->bindParam(':idVisiteur', $idVisiteur);
		$stmt->execute();

		$lesMois = array();
		while ($laLigne = $stmt->fetch()) {
		$mois = $laLigne['mois'];
		$numAnnee = substr($mois, 0, 4);
		$numMois = substr($mois, 4, 2);
		$lesMois["$mois"] = array(
		"mois" => "$mois",
		"numAnnee" => "$numAnnee",
		"numMois" => "$numMois"
		);
		}
		return $lesMois;
	}

    /**
     * Retourne les informations d'une fiche de frais d'un visiteur pour un mois donné
     * @param $idVisiteur
     * @param $mois * mois sous la forme aaaamm
     * @return mixed return un tableau avec des champs de jointure entre une fiche de frais et la ligne d'état
     * return un tableau avec des champs de jointure entre une fiche de frais et la ligne d'état
     */
	public function getLesInfosFicheFrais($idVisiteur,$mois){
		$req = "select fichefrais.idEtat as idEtat, fichefrais.dateModif as dateModif, fichefrais.nbJustificatifs as nbJustificatifs,
			fichefrais.montantValide as montantValide, etat.libelle as libEtat from  fichefrais inner join etat on fichefrais.idEtat = etat.id
			where fichefrais.idvisiteur ='$idVisiteur' and fichefrais.mois = '$mois'";
		$res = $this->monPdo->query($req);
		$laLigne = $res->fetch();
		return $laLigne;
	}

    /**
     * Modifie l'état et la date de modification d'une fiche de frais
     * Modifie le champ idEtat et met la date de modif à aujourd'hui
     * @param $idVisiteur
     * @param $mois * mois sous la forme aaaamm
     * @param $etat
     * @return void
     */

	public function majEtatFicheFrais($idVisiteur,$mois,$etat){
		$req = "update ficheFrais set idEtat = '$etat', dateModif = now()
		where fichefrais.idvisiteur ='$idVisiteur' and fichefrais.mois = '$mois'";
		$this->monPdo->exec($req);
	}

	public function getVisiteurFicheFrais($idVisiteur)
	{
		$req = "select fichefrais.mois as mois from fichefrais where fichefrais.idvisiteur = '$idVisiteur'";
		$res = $this->monPdo->query($req);
		$laLigne = $res->fetch();
		return $laLigne;
	}

	public function getLeVisiteurSelectionne($idVisiteur)
	{
		$req = "SELECT visiteur.id, visiteur.nom, visiteur.prenom 
				FROM visiteur 
				WHERE visiteur.id = :idVisiteur";
				
		$stmt = $this->monPdo->prepare($req);
		$stmt->bindParam(':idVisiteur', $idVisiteur);
		$stmt->execute();
		
		return $stmt->fetch(PDO::FETCH_ASSOC);
	}

	public function getLesFichesFrais($idVisiteur)
	{
		$req = "SELECT fichefrais.idVisiteur, fichefrais.mois, fichefrais.nbJustificatifs, fichefrais.montantValide, fichefrais.dateModif, fichefrais.idEtat
				FROM fichefrais 
				WHERE fichefrais.idvisiteur = :idVisiteur";
				
		$stmt = $this->monPdo->prepare($req);
		$stmt->bindParam(':idVisiteur', $idVisiteur);
		$stmt->execute();
		
		return $stmt->fetchAll(PDO::FETCH_ASSOC);
	}

    public function getMoisAnneeDisponibles()
    {
        $req = "SELECT DISTINCT 
                SUBSTRING(mois, 1, 4) as annee,
                SUBSTRING(mois, 5, 2) as mois
                FROM fichefrais 
                ORDER BY annee DESC, mois DESC";
                
        $stmt = $this->monPdo->prepare($req);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getLesFichesFraisFiltre($idVisiteur, $mois = null, $annee = null)
    {
        $req = "SELECT fichefrais.idVisiteur, fichefrais.mois, fichefrais.nbJustificatifs, 
                fichefrais.montantValide, fichefrais.dateModif, fichefrais.idEtat
                FROM fichefrais 
                WHERE fichefrais.idvisiteur = :idVisiteur";
        
        if ($mois && $annee) {
            $req .= " AND fichefrais.mois = :periode";
        } else if ($annee) {
            $req .= " AND SUBSTRING(fichefrais.mois, 1, 4) = :annee";
        }
        
        $req .= " ORDER BY fichefrais.mois DESC";
                
        $stmt = $this->monPdo->prepare($req);
        $stmt->bindParam(':idVisiteur', $idVisiteur);
        
        if ($mois && $annee) {
            $periode = $annee . str_pad($mois, 2, '0', STR_PAD_LEFT);
            $stmt->bindParam(':periode', $periode);
        } else if ($annee) {
            $stmt->bindParam(':annee', $annee);
        }
        
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getLesEtats()
    {
        $req = "SELECT id, libelle FROM etat ORDER BY id";
        $stmt = $this->monPdo->prepare($req);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function updateEtatFicheFrais($idVisiteur, $mois, $nouvelEtat)
    {
        $req = "UPDATE fichefrais 
                SET idEtat = :etat,
                    dateModif = NOW()
                WHERE idVisiteur = :idVisiteur 
                AND mois = :mois";

        $stmt = $this->monPdo->prepare($req);
        $stmt->bindParam(':etat', $nouvelEtat);
        $stmt->bindParam(':idVisiteur', $idVisiteur);
        $stmt->bindParam(':mois', $mois);
        
        return $stmt->execute();
    }

}
