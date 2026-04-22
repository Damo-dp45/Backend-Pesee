<?php

namespace App\Controller;

use App\Entity\Operation;
use App\Entity\Site;
use App\Repository\EntrepriseRepository;
use App\Repository\OperationRepository;
use App\Repository\SiteRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api', name: 'synchronisation.')]
#[IsGranted('ROLE_USER')]
final class SynchronisationController extends AbstractController
{
    #[Route('/synchronisation', name: 'index', methods: ['POST'])]
    public function index(
        Request $request,
        EntityManagerInterface $em,
        OperationRepository $operationRepository,
        SiteRepository $siteRepository,
        EntrepriseRepository $entrepriseRepository
    ): JsonResponse
    {
        $operations = $request->toArray();
        $data = [
            'code' => 2,
            'fail' => 0,
            'success' => 0,
            'operation' => [],
            'message' => 'Echec'
        ];
        $pesees = $operations['pesees'] ?? null;

        if(!$pesees) {
            $data['code'] = 3;
            $data['message'] = 'Pas de donnees';
            return $this->json($data, 200, ['Content-Type' => 'application/json']);
        }

        try {
            foreach($pesees as $donnees) {
                $mouvement = $donnees['libellemouvement'];
                $client = $donnees['libelleclient'];
                $destination = $donnees['libelledestination'];
                $provenance = $donnees['libelleprovenance'];
                $fournisseur = $donnees['libellefournisseur'];
                $transporteur = $donnees['libelletransporteur'];
                $produit = $donnees['libelleproduit'];
                $immatriculation = $donnees['immatriculation'];
                $remorque = $donnees['remorque'];
                $date1 = $donnees['datepesee1'];
                $date2 = $donnees['datepesee2'];
                $temps1 = $donnees['temps1'];
                $temps2 = $donnees['temps2'];
                $dsearch = $donnees['datesearch'];
                $poids1 = $donnees['poids1'];
                $poids2 = $donnees['poids2'];
                $poidsbrut = $donnees['poidsbrut'];
                $poidsnet = $donnees['poidsnet'];
                $peseur = $donnees['peseur'];
                $code = $donnees['code'];
                $id = $donnees['id'];
                $codepesee = $donnees['codepesee'];
                $numticket = $donnees['numticket'];
                $libellesite = $donnees['libellesite'];
                $codesecret = $code . '_' . $id . '_' . $codepesee;

                $site = $siteRepository->findOneByCode($code);
                if(!$site) {
                    $site = new Site();
                    $site->setCodesite($code);
                    $site->setLibellesite($libellesite);

                    $prefix = substr($code, 0, 3); /*
                        - On rattache l'entreprise au site via le préfixe
                    */
                    $entreprise = $entrepriseRepository->findOneByCodePrefix($prefix); /*
                        - Il est plus propre de chercher par le code de l'entreprise complet si le desktop renvoi un champ 'codeentreprise'
                    */
                    if($entreprise) {
                        $site->setEntreprise($entreprise);
                    }

                    $em->persist($site);
                    $em->flush();
                } else {
                    $site->setLibellesite($libellesite);
                    $em->flush();
                }

                /* Gestion de l'opération
                 */
                if(!$codesecret) {
                    $data['code'] = 3;
                    $data['message'] = 'Pas de donnees';
                    continue;
                }
                $result = $operationRepository->findOneByCodesecret($codesecret);
                $madatesearch = DateTime::createFromFormat('Y-m-d H:i:s', $dsearch);
                $madate1 = DateTime::createFromFormat('Y-m-d', $date1);
                $madate2 = DateTime::createFromFormat('Y-m-d', $date2);
                $matemps1 = DateTime::createFromFormat('H:i:s', $temps1);
                $matemps2 = DateTime::createFromFormat('H:i:s', $temps2);

                if(!$result) {
                    $result = new Operation();
                    $result->setCodesecret($codesecret);
                }
                $result
                    ->setMouvement($mouvement)
                    ->setClient($client)
                    ->setDestination($destination)
                    ->setProvenance($provenance)
                    ->setFournisseur($fournisseur)
                    ->setTransporteur($transporteur)
                    ->setProduit($produit)
                    ->setImmatriculation($immatriculation)
                    ->setRemorque($remorque)
                    ->setLibellesite($libellesite)
                    ->setPeseur($peseur)
                    ->setDate1($madate1)
                    ->setDate2($madate2)
                    ->setTemps1($matemps1)
                    ->setTemps2($matemps2)
                    ->setDatesearch($madatesearch)
                    ->setPoids1($poids1)
                    ->setPoids2($poids2)
                    ->setPoidsbrut($poidsbrut)
                    ->setPoidsnet($poidsnet)
                    ->setCodepesee($codepesee)
                    ->setNumticket($numticket)
                    ->setCode($code)
                    ->setCodesite($id)
                ; /*
                    - Les champs communs insert et update
                */
                $em->persist($result);
                $em->flush();

                $data['operation'][] = ['id' => $id]; // Ou.. 'array_push($data['operation'],['id' => $id])'
                $data['code'] = 1;
                $data['message'] = 'Operation effectuee avec succes';
                $data['success']++;
            }
        } catch(\Exception $e) {
            $data['code'] = 2;
            $data['message'] = 'Echec operation : ' . $e->getMessage();
            $data['fail']++;
        }

        return $this->json($data, 200, ['Content-Type' => 'application/json']);
    }

    /* Les endpoints de référentiels
     */
    #[Route('/mouvement', name: 'app_mouvement', methods: ['POST'])]
    public function getMouvement(OperationRepository $operationRepository, Request $request)
    {
        $code = $request->toArray();
        $liste = $operationRepository->getListeMouvement($code['code']);
        return $this->json($liste, 200, ['Content-Type' => 'application/json']);
    }

    #[Route('/client', name: 'app_client', methods: ['POST'])]
    public function getClient(OperationRepository $operationRepository, Request $request)
    {
        $code = $request->toArray();
        $liste = $operationRepository->getListeClient($code['code']);
        return $this->json($liste, 200, ['Content-Type' => 'application/json']);
    }

    #[Route('/fournisseur', name: 'app_fournisseur', methods: ['POST'])]
    public function getFournisseur(OperationRepository $operationRepository, Request $request)
    {
        $code = $request->toArray();
        $liste = $operationRepository->getListeFournisseur($code['code']);
        return $this->json($liste, 200, ['Content-Type' => 'application/json']);
    }

    #[Route('/destination', name: 'app_destination', methods: ['POST'])]
    public function getDestination(OperationRepository $operationRepository, Request $request)
    {
        $code = $request->toArray();
        $liste = $operationRepository->getListeDestination($code['code']);
        return $this->json($liste, 200, ['Content-Type' => 'application/json']);
    }

    #[Route('/provenance', name: 'app_provenance', methods: ['POST'])]
    public function getProvenance(OperationRepository $operationRepository, Request $request)
    {
        $code = $request->toArray();
        $liste = $operationRepository->getListeProvenance($code['code']);
        return $this->json($liste, 200, ['Content-Type' => 'application/json']);
    }

    #[Route('/transporteur', name: 'app_transporteur', methods: ['POST'])]
    public function getTransporteur(OperationRepository $operationRepository, Request $request)
    {
        $code  = $request->toArray();
        $liste = $operationRepository->getListeTransporteur($code['code']);
        return $this->json($liste, 200, ['Content-Type' => 'application/json']);
    }

    #[Route('/vehicule', name: 'app_vehicule', methods: ['POST'])]
    public function getVehicule(OperationRepository $operationRepository, Request $request)
    {
        $code = $request->toArray();
        $liste = $operationRepository->getListeVehicule($code['code']);
        return $this->json($liste, 200, ['Content-Type' => 'application/json']);
    }

    #[Route('/produit', name: 'app_produit', methods: ['POST'])]
    public function getProduit(OperationRepository $operationRepository, Request $request)
    {
        $code = $request->toArray();
        $liste = $operationRepository->getListeProduit($code['code']);
        return $this->json($liste, 200, ['Content-Type' => 'application/json']);
    }

    #[Route('/site', name: 'app_site', methods: ['POST'])]
    public function getSite(SiteRepository $siteRepository, Request $request)
    {
        $code = $request->toArray();
        $strcode = substr($code['code'], 0, 3);
        $liste = $siteRepository->getListeSite($strcode);
        return $this->json($liste, 200, ['Content-Type' => 'application/json']);
    }

    #[Route('/lister', name: 'app_lister', methods: ['POST'])]
    public function getBilanListe(Request $request, OperationRepository $operationRepository)
    {
        $donnees = $request->toArray();
        $mouvement = $donnees['mouvement'];
        $client = $donnees['client'];
        $destination = $donnees['destination'];
        $provenance = $donnees['provenance'];
        $fournisseur = $donnees['fournisseur'];
        $transporteur = $donnees['transporteur'];
        $code = $donnees['code'];
        $produit = $donnees['produit'];
        $vehicule = $donnees['immatriculation'];
        $date1 = $donnees['datepesee1'];
        $date2 = $donnees['datepesee2'];

        $jsonData = [
            'msg' => 'OK',
            'total' => 0,
            'rows' => []
        ];

        try {
            $critere = ['deletedAt' => null]; // !!
            $limit   = 500;

            $dateDebut = $date1; /*
                if($dateDebut) {
                    $dateDebut = new DateTime(date("Y-m-d", strtotime(str_replace("/", "-", $dateDebut))));
                    unset($critere['etat']);
                }
            */
            $dateFin = $date2; /*
                if($dateFin) {
                    $dateFin = new DateTime(date("Y-m-d", strtotime(str_replace("/", "-", $dateFin))));
                }
            */

            if($mouvement) $critere['mouvement'] = $mouvement;
            if($code) $critere['code'] = $code;
            if($fournisseur) $critere['fournisseur'] = $fournisseur;
            if($client) $critere['client'] = $client;
            if($destination) $critere['destination'] = $destination;
            if($provenance) $critere['provenance'] = $provenance;
            if($produit) $critere['produit'] = $produit;
            if($transporteur) $critere['transporteur'] = $transporteur;
            if($vehicule) $critere['vehicule'] = $vehicule;

            $liste = $operationRepository->getAllBy($critere, $date1, $date2, $limit);
            $total = 0;
            foreach($liste as $operation) {
                $total += $operation->getPoidsnet();
            }
            $jsonData['rows']  = $liste;
            $jsonData['total'] = $total;
        } catch(\Exception $exc) {
            $jsonData['msg'] = $exc->getMessage();
        }

        return $this->json($jsonData, 200, ['Content-Type' => 'application/json']);
    }
}
