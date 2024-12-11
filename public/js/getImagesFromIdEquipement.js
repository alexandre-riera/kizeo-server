function getImagesFromIdEquipement(idEquipementAndIdRow,libelleEquipement,visite,raisonSociale,modeleNacelle,hauteurNacelle,ifExistDB,signatureTech,trigrammeTech,anomalies,idContact,idSociete,codeAgence,numeroEquipement,modeFonctionnement,repereSiteClient,miseEnService,numeroDeSerie,marque,hauteur,largeur,longueur,plaqueSignaletique,etat,derniereVisite,statutDeMaintenance,presenceCarnetEntretien,statutConformite){
    // Try get id_contact on equipment to prepare request save in DB
    // Populate MODAL fields from selected table row
    // if (idEquipementAndIdRow != null) {
    //     document.getElementById("modal_show_details_id").value = idEquipementAndIdRow;
    // }else{
    //     document.getElementById("modal_show_details_id").value = "ID non remonté";
    // }
    // document.getElementById("modal_show_details_libelle").value = libelleEquipement;
    // document.getElementById("modal_show_details_visite").value = visite;
    // document.getElementById("modal_show_details_raisonSociale").value = raisonSociale;
    // document.getElementById("modal_show_details_modeleNacelle").value = modeleNacelle;
    // document.getElementById("modal_show_details_hauteurNacelle").value = hauteurNacelle;
    // document.getElementById("modal_show_details_ifExistDB").value = ifExistDB;
    // document.getElementById("modal_show_details_signatureTech").value = signatureTech;
    // document.getElementById("modal_show_details_trigrammeTech").value = trigrammeTech;
    // document.getElementById("modal_show_details_anomalies").value = anomalies;
    // document.getElementById("modal_show_details_idContact").value = idContact;
    // document.getElementById("modal_show_details_idSociete").value = idSociete;
    // document.getElementById("modal_show_details_codeAgence").value = codeAgence;
    // document.getElementById("modal_show_details_trigramme").value = numeroEquipement;
    // document.getElementById("modal_show_details_modeFonctionnement").value = modeFonctionnement;
    // document.getElementById("modal_show_details_repereSiteClient").value = repereSiteClient;
    // document.getElementById("modal_show_details_miseEnService").value = miseEnService;
    // document.getElementById("modal_show_details_numeroDeSerie").value = numeroDeSerie;
    // document.getElementById("modal_show_details_marque").value = marque;
    // document.getElementById("modal_show_details_hauteur").value = hauteur;
    // document.getElementById("modal_show_details_largeur").value = largeur;
    // document.getElementById("modal_show_details_longueur").value = longueur;
    // document.getElementById("modal_show_details_plaqueSignaletique").value = plaqueSignaletique;
    // document.getElementById("modal_show_details_etat").value = etat;
    // var prochainStatut = document.getElementById("modal_show_details_prochain_statut").value;
    // var nom = document.getElementById("modal_show_details_nom").value;
    // var prenom = document.getElementById("modal_show_details_prenom").value;
    // document.getElementById("modal_show_details_derniereVisite").value = derniereVisite;
    // document.getElementById("modal_show_details_statut").value = statutDeMaintenance;
    // document.getElementById("modal_show_details_carnetEntretien").value = presenceCarnetEntretien;
    // document.getElementById("modal_show_details_statutConformite").value = statutConformite;

    console.log("IdEquipement: " +libelleEquipement);
    console.log("libelleEquipement: " +libelleEquipement);
    console.log("visite: " +visite);
    console.log("raisonSociale: " +raisonSociale);
    console.log("modeleNacelle: " +modeleNacelle);
    console.log("hauteurNacelle: " +hauteurNacelle);
    console.log("ifExistDB: " +ifExistDB);
    console.log("signatureTech: " +signatureTech);
    console.log("trigrammeTech: " +trigrammeTech);
    console.log("anomalies: " +anomalies);
    console.log("idContact: " +idContact);
    console.log("idSociete: " +idSociete);
    console.log("codeAgence: " +codeAgence);
    console.log("numeroEquipement: " +numeroEquipement);
    console.log("modeFonctionnement: " +modeFonctionnement);
    console.log("repereSiteClient: " +repereSiteClient);
    console.log("miseEnService: " +miseEnService);
    console.log("numeroDeSerie: " +numeroDeSerie);
    console.log("marque: " +marque);
    console.log("hauteur: " +hauteur);
    console.log("largeur: " +largeur);
    console.log("longueur: " +longueur);
    console.log("plaqueSignaletique: " +plaqueSignaletique);
    console.log("etat: " +etat);
    console.log("derniereVisite: " +derniereVisite);
    console.log("statutDeMaintenance: " +statutDeMaintenance);
    console.log("presenceCarnetEntretien: " +presenceCarnetEntretien);
    console.log("statutConformite: " +statutConformite);

}