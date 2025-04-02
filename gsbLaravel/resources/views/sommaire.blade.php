@extends ('modeles/visiteur')
    @section('menu')
            <!-- Division pour le sommaire -->
        <div id="menuGauche">
            <div id="infosUtil">

             </div>
               <ul id="menuList"> <!-- Modification made by Ts1 : 2c !-->
                 @if (isset($visiteur))
                 <li >
                     <strong>Bonjour {{ $visiteur['nom'] . ' ' . $visiteur['prenom'] }}</strong>
                 </li>
                 @elseif(isset($gestionnaire))
                 <li >
                     <strong>Bonjour {{ $gestionnaire['nom'] . ' ' . $gestionnaire['prenom'] }}</strong>
                 </li>
                 @endif
                 @if (isset($visiteur))
                  <li class="smenu">
                     <a href="{{ route('chemin_gestionFrais')}}" title="Saisie fiche de frais ">Saisie fiche de frais</a>
                  </li>
                  <li class="smenu">
                    <a href="{{ route('chemin_selectionMois') }}" title="Consultation de mes fiches de frais">Mes fiches de frais</a>
                  </li>
               <li class="smenu">
                  <a href="{{ route('chemin_deconnexion') }}" title="Se déconnecter">Déconnexion</a>
               </li>
               @elseif(isset($gestionnaire))
               <li class="smenu">
                  <a href="{{ route('chemin_deconnexion') }}" title="Se déconnecter">Déconnexion</a>
               </li>
               <li class = "smenu">
                  <a href="{{ route('chemin_visiteur') }}" title="Voir Visiteur">Voir Les Frais</a>
               </li>
               @endif
               </ul>
            </div>
    @endsection
