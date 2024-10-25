<?php

/*
 * Copyright (C) 20xx VMA Vincent Maury <vmaury@timgroup.fr>
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY
 */
require_once __DIR__.'/../../../main.inc.php'; //

class TgCronJob {
	
	/**
	 * sortie job
	 * @var string
	 */
	public $output = '';
	public $geodis_shipping_method_id;
	
	function updtExpedFromGeodis() {
		global $conf, $db, $user;
		require_once DOL_DOCUMENT_ROOT.'/expedition/class/expedition.class.php';
		$Exped = new Expedition($db);
		$dstart = date('Y-m-d', time() - (3600 * 24 * getDolGlobalInt('GEODIS_NBJ_DATE_SCAN', 15)));
		$dend = date('Y-m-d');
		$tbListGeodis = callGeodisZoomApi($dstart, $dend);
		if (empty($conf->cache['geodis_shipping_method_id'])) {
			//echo "select rowid from ".MAIN_DB_PREFIX."c_shipment_mode where code='GEODIS'";
			$resdg = $db->query("select rowid from ".MAIN_DB_PREFIX."c_shipment_mode where code='GEODIS'");
			$obdg = $db->fetch_object($resdg);
			$conf->cache['geodis_shipping_method_id'] = (int)$obdg->rowid;
			//echo 'geodis_shipping_method_id in cache '.$conf->cache['geodis_shipping_method_id'];
		}
		if (is_array($tbListGeodis) && empty($tbListGeodis['error'])) {
			 //print_r($tbListGeodis); die();
/* Array (
    [ok] => 1
    [codeErreur] => 
    [texteErreur] => 
    [contenu] => Array
        (
            [0] => Array
                (
                    [refUniExp] => 1358024769
                    [refUniEnl] => 
                    [codeSa] => 050087
                    [codeClient] => 001302
                    [codeProduit] => AFF
                    [codeOption] => 
                    [typePrestation] => AFF
                    [libellePrestation] => Affrètement France (AFF)
                    [noSuivi] => 1G8FHGWKZGYE
                    [noRecepisse] => 32895345
                    [reference1] => SH2403-3958-3947
                    [reference2] => 
                    [dateDepart] => 2024-03-06
                    [dateDepartFrs] => 06/03/2024
                    [nomExp] => LT SHOWERTEC
                    [adresse1Exp] => 13 RUE LEON WALRAS
                    [adresse2Exp] => .ZI ROMANET
                    [codePostalExp] => 87000
                    [villeExp] => LIMOGES
                    [codePaysExp] => FR
                    [libellePaysExp] => France
                    [dateLivraison] => 2024-03-08
                    [dateLivraisonFrs] => 08/03/2024
                    [libelleLivraison] => Livraison le 8 mars 2024
                    [nomDest] => VINCENT PETIT
                    [adresse1Dest] => ZAC DE LA CAPUCIERES
                    [adresse2Dest] => BOX A3
                    [codePostalDest] => 34550
                    [villeDest] => BESSAN
                    [codePaysDest] => FR
                    [libellePaysDest] => France
                    [refDest] => 
                    [poids] => 400
                    [nbColis] => 0
                    [nbPalettes] => 1
                    [codeSituation] => LIV
                    [codeJustification] => CFM
                    [libelleEtat] => Livrée
                    [libelleLongEtat] => Livrée
                    [dateEtat] => 2024-03-07
                    [dateEtatFrs] => 07/03/2024
                    [avecInstructionDonnee] => 
                    [avecAttenteInstruction] => 
                    [dateLimiteInstruction] => 
                    [dateLimiteInstructionFrs] => 
                    [delaiInstruction] => 
                    [uniteInstruction] => 
                    [avecMatiereDangereuse] => 
                    [nbErreursNotification] => 0
                    [urlImageEnlevementLivraison] => 
                    [listServicesLivraison] => Array
                        (
                        )

                    [statutServicesLivraison] => 
                    [libelleStatutServicesLivraison] => 
                    [urlPreuveService] => 
                    [loginDestinataire] => 05008732895345 / 34550
                    [urlSuiviDestinataire] => https://edesti.com/1G8FHGWKZGYE
                    [emissionEqc] => 
                    [emissionEqa] => 
                    [emissionPar] => 
                    [temperatureMin] => 
                    [temperatureMax] => 
                    [temperatureMed] => 
                    [nbExcursionsTemp] => 
                    [envoiRegroupe] => 
                    [refUniRegroupement] => 0
                    [noRecepRegroupement] => 
                    [envoiRegroupement] => 
                    [listEnvoisRegroupes] => Array
                        (
                        )

                    [swapAller] => 
                    [swapRetour] => 
                    [refUniExpSwap] => 0
                    [noRecepisseSwap] => 
                    [noSuiviSwap] => 
                    [dateDepartSwap] => 
                    [dateDepartSwapFrs] => 
                )
 */			
			if (is_array($tbListGeodis['contenu']) && count($tbListGeodis['contenu']) > 0) {
				$this->output .= "*** Période du $dstart au $dend : ".count($tbListGeodis['contenu'])." expéditions ramenées ***\n";
				$this->output .= var_export($tbListGeodis, true);
				foreach($tbListGeodis['contenu'] as $kex=>$geoExp) {
					//$refExpOk = updateFromGrex($geoExp); // que si grpement expeds activé
					$refExpOk =  false;
					if ($refExpOk) {
						continue; // on fait tout dans updateFromGrex
					} elseif (testRefExped($geoExp['reference1'])) {
						$refExpOk = $geoExp['reference1'];
					} elseif (testRefExped($geoExp['reference2'])) {
						$refExpOk = $geoExp['reference2'];
					} elseif ($geoExp['noSuivi'] == '1GQZ2NE8SU2A') { // !!!!! BOUCHON pour TEST
						$refExpOk = 'SH2410-4070';
					} 
					if ($refExpOk) {
						// parfois des ref d'exped complémentaires sont rajoutées dans la ref 2
						if ($refExpOk == $geoExp['reference1'] && strstr($geoExp['reference2'], '-')) {
							$refExpOk .= '-'.$geoExp['reference2'];
						}
						$tbRefsExp = explode('-', $refExpOk);
						for ($i = 1; $i < count($tbRefsExp); $i++) {
							if (trim($tbRefsExp[$i]) != '') {
								$refExp = $tbRefsExp[0].'-'.$tbRefsExp[$i];
								$this->output .=  "$kex => $refExp \n";
								$rfe = $Exped->fetch(null, $refExp);
								if ($rfe <= 0) {
									$this->output .=  "$refExp introuvable peuh \n";
								} else {
									$this->output .=  updateInfosTranspExped($Exped, $geoExp);
								} // fin si ref trouvée
							} // si n° de ref non vide
						}// fin bcle sur refs
					} else {
						$this->output .= "Aucune réf. d'exp valide trouvée dans indice $kex (refs {$geoExp['reference1']},{$geoExp['reference2']})\n";
					}
				}
			} else $this->output .= 'Aucune expédition Geodis ramenée par la requête';
			
			return 0;
		} else {
			$this->output .= ' erreurs sur appel API Geodis : '. var_export($tbListGeodis, true);
			return true;
		}
		
	}
}
/** test si une exped est de la forme SH2405-4566*****
 * 
 * @param type $refexp
 * @return bool
 */
function testRefExped($refexp) {
	$v = "/^(SH)(\d){4}\-(\d){4}/";
	return preg_match($v, $refexp);
	
}

/** met à jour les infos d'UNE expédition
 * 
 * @param Expedition $Exped
 * @param array $geoExp 
 * @return string
 */
function updateInfosTranspExped(Expedition $Exped, $geoExp) {
	global $conf;
	$ret = '';
	$updt = false;
	$ExpedOr = clone $Exped;
	$Exped->array_options['options_statutexped'] = $geoExp['libelleLongEtat'];
	if ($Exped->shipping_method_id != $conf->cache['geodis_shipping_method_id']) {
		$Exped->shipping_method_id = $conf->cache['geodis_shipping_method_id'];
		$updt = true;
	}
	if (getDolGlobalInt('GEODIS_CLOSE_EXPED_AFTER_DELIV')) {
		if ($geoExp['codeSituation'] == 'LIV' && $Exped->statut != $Exped::STATUS_CLOSED) {
			$Exped->setClosed();
			$updt = true;
		}
	}
	if (!empty($geoExp['noSuivi']) && $Exped->tracking_number != $geoExp['noSuivi']) {
		$updt = true;
		$Exped->tracking_number = $geoExp['noSuivi'];
	}
	if (!empty($geoExp['dateDepart'])) $Exped->array_options['options_dateexped'] = $geoExp['dateDepart'];
	
	$ExpedOr->array_options['options_dateexped'] = $ExpedOr->array_options['options_dateexped']; // pour les tests d'update ci-dessous .. sinon ils sont en timestamp
	foreach (['statutexped', 'dateexped'] as $kp) {
		if ($Exped->array_options['options_'.$kp] != $ExpedOr->array_options['options_'.$kp]) {
			$updt = true;
			$Exped->updateExtraField($kp);
		}
	}
	if ($updt) {
		$Exped->array_options['options_detailsexped'] = setDetailExp($geoExp);
		$Exped->updateExtraField('detailsexped');
		$Exped->update($user);
		$ret .= $Exped->ref." mise à jour\n";
	} else {
		$ret .= $Exped->ref." inchangée\n";
	}
	return $ret;
}

/**
 * 
 * @param date $dateStart
 * @param date $dateEnd
 * @return array reponse Api
 */
function callGeodisZoomApi($dateStart = null, $dateEnd = null) {
	
	
	$login = getDolGlobalString('GEODIS_API_LOGIN');
	$secretKey = getDolGlobalString('GEODIS_API_KEY');

	$uri = getDolGlobalString('GEODIS_SERVICE_URI');
	$service = getDolGlobalString('GEODIS_API_ZOOM_PATH');
	$lang = 'fr';
	//  $body = array(
	//    'dateDepart' => '',
	//    'dateDepartDebut' => '2024-02-28',
	//    'dateDepartFin' => '2024-03-29',
	//    'noRecepisse' => '',
	//      'reference1' => '',
	//      'noSuivi' => '',
	//      'cabColis' => '',
	//      'codeSa' => '',
	//      'codeClient' => '',
	//      'codeProduit' => '',
	//      'typePrestation' => '',
	//      'dateLivraison' => '',
	//      'refDest' => '',
	//      'nomDest' => '',
	//      'codePostalDest' => '',
	//      'natureMarchandise' => '',
	//  );
	$body = array(
		'dateDepartDebut' => $dateStart,
		'dateDepartFin' => $dateEnd
	);
	$inlineBody = json_encode($body);
	$timestamp = (time() * 1000);
	$message = $secretKey . ';' . $login . ';' . $timestamp . ';' . $lang . ';' . $service . ';' . $inlineBody;
	$hash = hash('sha256', $message);
	$serviceRequestHeader = $login . ';' . $timestamp . ';' . $lang . ';' . $hash;
	$headers = array(
		'X-GEODIS-Service: ' . $serviceRequestHeader,
		'Content-Type: application/json; charset=utf-8',
		'Content-Length: ' . strlen($inlineBody),
	);

	//echo $inlineBody;
	//print_r($headers);

	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $uri . $service);
	curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
	curl_setopt($ch, CURLOPT_POST, 1); // add VMA
	curl_setopt($ch, CURLOPT_POSTFIELDS, $inlineBody);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT_MS, 5000);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_FAILONERROR, true);

	$rawResult = curl_exec($ch);
	if (curl_error($ch)) {
		$error_msg = curl_error($ch);
		$http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		$curl_errno = curl_errno($ch);
		return ['error' => 1, 'http_status' => $http_status, 'curl_errno' => $curl_errno, 'error_msg' => $error_msg];
	} else {
		return json_decode($rawResult, true);
	}
}

/** met à jour les déyails de l'exp à partir des infos geodis
 * 
 * @param array $geoExp infos exped issues de geodis
 * @return string
 */
function setDetailExp($geoExp) {
	$ret = 'Mode exped. : '.$geoExp['libellePrestation']."\n";
	$ret .= 'N° récipisssé : '.$geoExp['noRecepisse']."\n";
	$ret .= 'Date livraison : '.$geoExp['dateLivraisonFrs']."\n";
	$ret .= 'Détail livraison : '.$geoExp['libelleLivraison']."\n";
	$ret .= 'Adresse livraison : '."\n"
														.$geoExp['nomDest']."\n"
														.$geoExp['adresse1Dest']."\n"
														.$geoExp['adresse2Dest']."\n"
														.$geoExp['codePostalDest']." "
														.$geoExp['villeDest']." "
														.$geoExp['libellePaysDest']."\n";
	$ret .= 'Poids : '.$geoExp['poids']."\n";
	$ret .= 'Nbre colis : '.$geoExp['nbColis']."\n";
	$ret .= 'Nbre palettes : '.$geoExp['nbPalettes']."\n";
	$ret .= 'Etat : '.$geoExp['libelleLongEtat']."\n";
	return $ret;
}


/** test si une ref est de la forme GREX2407-1234 *****
 * utilisable que si le module groupement d'expédition est installé
 * et update le GREX si nécéssaire ainsi que toutes les expédition liées
 * 
 * @param array $geoExp lableau des infos ramenées depuis Geodis
 * @return bool|array des RefExp
 */
function updateFromGrex($geoExp) {
	global $db, $user;
	$refgrex = $geoExp['reference1'];
	$v = "/^(GREX)(\d){4}\-(\d){4}/";
	if (preg_match($v, $refgrex)) {
		require_once __DIR__.'/regrpexped.class.php';
		$RegrpExp = new Regrpexped($db);
		//$refgrex = reset(explode('-', trim($refgrex)));
		$refgrex = trim($refgrex);
		if ($RegrpExp->fetch(null, $refgrex) > 0) {
			// mise à jour - éventuelle - de certains champs du regrpt exped en fonction des infos geodis
			$RegrpExpOr = clone $RegrpExp;
			$RegrpExp->exped_status = $geoExp['libelleLongEtat'];
			if (!empty($geoExp['noSuivi'])) {
				$RegrpExp->tracking_number = '<a href="'.$geoExp['urlSuiviDestinataire'].'" target="_blank">'.$geoExp['noSuivi'].'</a>';
			}
			if (!empty($geoExp['dateDepart'])) $RegrpExp->exped_date = $geoExp['dateDepart'];
			$updt = false;
			//??? $RegrpExpOr->exped_date = $RegrpExpOr->exped_date; // pour les tests d'update ci-dessous .. sinon ils sont en timestamp
			foreach (['exped_status', 'tracking_number', 'exped_date'] as $kp) {
				if ($RegrpExp->$kp != $RegrpExpOr->$kp) {
					$updt = true;
				}
			}
			if ($updt) {
				$RegrpExp->exped_details = setDetailExp($geoExp);
				$RegrpExp->update($user);
			}
			
			// maintenant on va chercher les expéditions liées
			$sql = "select e.rowid, e.ref from ".MAIN_DB_PREFIX."expedition e, ".MAIN_DB_PREFIX."expedition_extrafields ex "
					. " where e.rowid=ex.fk_object and ex.regrpexped = ".(int)$RegrpExp->id;
			$tbExps = getCompRass($sql);
			if (is_array($tbExps) && count($tbExps) >0) {
				require_once DOL_DOCUMENT_ROOT.'/expedition/class/expedition.class.php';
				$Exped = new Expedition($db);
				foreach ($tbExps as $tex) {
					$Exped->fetch($tex['rowid']);
					updateInfosTranspExped($Exped, $geoExp);
				}
				return true;
			} else return false;
		} else return false;
	}
	return false;
}