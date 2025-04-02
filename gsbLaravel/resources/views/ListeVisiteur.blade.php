@extends('modeles.visiteur')
<!-- Made by Ts1 : 2c : Rewrite of summary-->
@section('menu')
    <div id="menuGauche">
        <div id="infosUtil">
            @if(isset($gestionnaire))
                <strong>Bonjour {{ $gestionnaire['nom'] . ' ' . $gestionnaire['prenom'] }}</strong>
            @endif
        </div>
        <ul id="menuList">
            @if(isset($gestionnaire))
                <li class="smenu">
                    <a href="{{ route('chemin_deconnexion') }}" title="Se déconnecter">Déconnexion</a>
                </li>
            @endif
        </ul>
    </div>
@endsection

@section('contenu1')
<div id="contenu">
    <h2>Liste des Visiteurs</h2>
    <h3>Sélectionnez un visiteur : </h3>

    <form action="{{ route('chemin_visiteur') }}" method="post">
        @csrf
        <div class="corpsForm">
            <fieldset>
                <legend>Sélection du visiteur et de la période</legend>
                <p>
                    <label for="lstVisiteurs">Visiteurs : </label>
                    <select id="lstVisiteurs" name="lstVisiteurs" onchange="togglePeriodSelects(); this.form.submit();">
                        <option value="">Sélectionnez un visiteur</option>
                        @foreach($liste as $visiteur)
                            <option value="{{ $visiteur['id'] }}" 
                                @if(isset($visiteurSelectionne) && $visiteurSelectionne && $visiteurSelectionne['id'] == $visiteur['id']) 
                                    selected 
                                @endif>
                                {{ $visiteur['nom'] }} {{ $visiteur['prenom'] }}
                            </option>
                        @endforeach
                    </select>
                </p>

                @if(isset($periodes) && count($periodes) > 0)
                    <p>
                        <label for="lstAnnees">Année : </label>
                        <select name="lstAnnees" id="lstAnnees" onchange="this.form.submit()" 
                                @if(!isset($visiteurSelectionne) || !$visiteurSelectionne) disabled @endif>
                            <option value="">Toutes les années</option>
                            @php
                                $annees = collect($periodes)->pluck('annee')->unique()->sort()->reverse();
                            @endphp
                            @foreach($annees as $annee)
                                <option value="{{ $annee }}"
                                    @if(isset($anneeSelectionnee) && $anneeSelectionnee == $annee)
                                        selected
                                    @endif>
                                    {{ $annee }}
                                </option>
                            @endforeach
                        </select>
                    </p>

                    <p>
                        <label for="lstMois">Mois : </label>
                        <select name="lstMois" id="lstMois" onchange="this.form.submit()"
                                @if(!isset($visiteurSelectionne) || !$visiteurSelectionne) disabled @endif>
                            <option value="">Tous les mois</option>
                            @php
                                $moisNoms = [
                                    '01' => 'Janvier', '02' => 'Février', '03' => 'Mars',
                                    '04' => 'Avril', '05' => 'Mai', '06' => 'Juin',
                                    '07' => 'Juillet', '08' => 'Août', '09' => 'Septembre',
                                    '10' => 'Octobre', '11' => 'Novembre', '12' => 'Décembre'
                                ];
                            @endphp
                            @foreach($moisNoms as $num => $nom)
                                <option value="{{ $num }}"
                                    @if(isset($moisSelectionne) && $moisSelectionne == $num)
                                        selected
                                    @endif>
                                    {{ $nom }}
                                </option>
                            @endforeach
                        </select>
                    </p>
                @endif
            </fieldset>
        </div>
    </form>

    @if(isset($visiteurSelectionne) && $visiteurSelectionne && isset($fichesFrais))
        <div class="encadre">
            <h3>Fiches de frais de {{ $visiteurSelectionne['nom'] }} {{ $visiteurSelectionne['prenom'] }}</h3>
            @if(count($fichesFrais) > 0)
                <table class="listeLegere">
                    <thead>
                        <tr>
                            <th>Mois</th>
                            <th>Nombre de justificatifs</th>
                            <th>Montant validé</th>
                            <th>Date de modification</th>
                            <th>État</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($fichesFrais as $fiche)
                            <tr>
                                <td>{{ substr($fiche['mois'], 4, 2) }}/{{ substr($fiche['mois'], 0, 4) }}</td>
                                <td>{{ $fiche['nbJustificatifs'] }}</td>
                                <td>{{ $fiche['montantValide'] }}€</td>
                                <td>{{ date('d/m/Y', strtotime($fiche['dateModif'])) }}</td>
                                <td>
                                    <form action="{{ route('update_etat') }}" method="post" class="form-inline">
                                        @csrf
                                        <input type="hidden" name="idVisiteur" value="{{ $fiche['idVisiteur'] }}">
                                        <input type="hidden" name="mois" value="{{ $fiche['mois'] }}">
                                        <select name="nouvelEtat" onchange="this.form.submit()" class="form-control">
                                            @foreach($etats as $etat)
                                                <option value="{{ $etat['id'] }}" 
                                                    @if($fiche['idEtat'] == $etat['id']) selected @endif>
                                                    {{ $etat['libelle'] }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </form>
                                </td>
                                <td>
                                    <a href="{{ route('export_xml', ['idVisiteur' => $fiche['idVisiteur'], 'mois' => $fiche['mois']]) }}" 
                                       class="btn-export" title="Exporter en XML">
                                        Exporter XML
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <p>Aucune fiche de frais trouvée pour ce visiteur sur la période sélectionnée.</p>
            @endif
        </div>
    @endif
</div>

<script>
function togglePeriodSelects() {
    var visiteurSelect = document.getElementById('lstVisiteurs');
    var anneeSelect = document.getElementById('lstAnnees');
    var moisSelect = document.getElementById('lstMois');
    
    if (visiteurSelect.value === '') {
        anneeSelect.disabled = true;
        moisSelect.disabled = true;
        anneeSelect.value = '';
        moisSelect.value = '';
    } else {
        anneeSelect.disabled = false;
        moisSelect.disabled = false;
    }
}

// Exécuter au chargement de la page
document.addEventListener('DOMContentLoaded', function() {
    togglePeriodSelects();
});
</script>

<style>
.alert {
    padding: 15px;
    margin-bottom: 20px;
    border: 1px solid transparent;
    border-radius: 4px;
}

.alert-success {
    color: #155724;
    background-color: #d4edda;
    border-color: #c3e6cb;
}

.alert-danger {
    color: #721c24;
    background-color: #f8d7da;
    border-color: #f5c6cb;
}

.form-inline {
    display: inline-block;
}

.form-control {
    padding: 5px;
    border: 1px solid #ccc;
    border-radius: 4px;
}

.btn-export {
    display: inline-block;
    padding: 5px 10px;
    background-color: #28a745;
    color: white;
    text-decoration: none;
    border-radius: 4px;
}

.btn-export:hover {
    background-color: #218838;
    color: white;
    text-decoration: none;
}

fieldset {
    border: 1px solid #ccc;
    padding: 15px;
    margin-bottom: 20px;
    border-radius: 4px;
}

legend {
    padding: 0 10px;
    font-weight: bold;
}

select {
    padding: 5px;
    border: 1px solid #ccc;
    border-radius: 4px;
    margin: 5px 0;
}

select:disabled {
    background-color: #e9ecef;
    cursor: not-allowed;
}

label {
    display: inline-block;
    width: 100px;
    margin-right: 10px;
}
</style>
@endsection
