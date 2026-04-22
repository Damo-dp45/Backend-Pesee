<?php

namespace App\Controller;

use App\Entity\Operation;
use App\Entity\User;
use App\Repository\OperationRepository;
use App\Repository\SiteRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/frontend', name: 'frontend.')]
#[IsGranted('ROLE_USER')]
final class FrontendController extends AbstractController
{
    #[Route('/operations', name: 'operations', methods: ['GET'])]
    public function getOperations(Request $request, OperationRepository $operationRepository): JsonResponse
    {
        /**
         * @var User
         */
        $user = $this->getUser();
        $codeentreprise = $user->getEntreprise()->getCodeentreprise();
        $prefix = substr($codeentreprise, 0, 3);

        $page = max(1, (int) $request->query->get('page', 1));
        $limit = min(100, max(1, (int) $request->query->get('limit', 20)));

        $criteres = [
            'code' => $request->query->get('code'),
            'mouvement' => $request->query->get('mouvement'),
            'client' => $request->query->get('client'),
            'fournisseur' => $request->query->get('fournisseur'),
            'destination' => $request->query->get('destination'),
            'provenance' => $request->query->get('provenance'),
            'produit' => $request->query->get('produit'),
            'transporteur' => $request->query->get('transporteur'),
            'vehicule' => $request->query->get('immatriculation')
        ];

        if(empty($criteres['code'])) { /*
            - On filtre uniquement les sites de l'entreprise connectée
        */
            $criteres['codeprefix'] = $prefix;
        }

        $dateDebut = $request->query->get('date_debut');
        $dateFin = $request->query->get('date_fin');
        $result = $operationRepository->getPaginatedOperations(
            array_filter($criteres),
            $dateDebut,
            $dateFin,
            $page,
            $limit
        );

        return $this->json([
            'data' => $result['data'],
            'pagination' => [
                'total' => $result['total'],
                'page' => $result['page'],
                'limit' => $result['limit'],
                'totalPages' => $result['totalPages']
            ],
        ], 200, ['Content-Type' => 'application/json']);
    }

    #[Route('/operations/stats', name: 'stats', methods: ['GET'])]
    public function getStats(Request $request, OperationRepository $operationRepo): JsonResponse
    {
        /**
         * @var User
         */
        $user = $this->getUser();
        $entreprise = $user->getEntreprise();

        $dateDebut = $request->query->get('date_debut');
        $dateFin = $request->query->get('date_fin');

        $parSite  = $operationRepo->getStatsByPeriode($entreprise->getCodeentreprise(), $dateDebut, $dateFin);
        $parJour  = $operationRepo->getStatsByJour($entreprise->getCodeentreprise(), $dateDebut, $dateFin);
        $dernieres = $operationRepo->getDernieresOperations($entreprise, 8);

        $totalNet  = array_sum(array_column($parSite, 'total_poidsnet'));
        $totalBrut = array_sum(array_column($parSite, 'total_poidsbrut'));
        $totalOps  = array_sum(array_column($parSite, 'nombre_operations'));

        return $this->json([
            'totaux' => [
                'poidsnet' => $totalNet,
                'poidsbrut' => $totalBrut,
                'nombre_operations' => $totalOps
            ],
            'par_site' => $parSite,
            'par_jour' => $parJour,
            'dernieres' => array_map(fn(Operation $op) => [
                'libellesite' => $op->getLibellesite(),
                'code' => $op->getCode(),
                'produit' => $op->getProduit(),
                'client' => $op->getClient(),
                'transporteur' => $op->getTransporteur(),
                'mouvement' => $op->getMouvement(),
                'poidsnet' => $op->getPoidsnet(),
                'poidsbrut' => $op->getPoidsbrut(),
                'immatriculation' => $op->getImmatriculation(),
                'date2' => $op->getDate2()?->format('d/m/Y'),
                'numticket' => $op->getNumticket()
            ], $dernieres),
        ], 200, ['Content-Type' => 'application/json']);
    }

    #[Route('/sites', name: 'sites', methods: ['GET'])]
    public function getSites(SiteRepository $siteRepository): JsonResponse
    {
        /**
         * @var User
         */
        $user = $this->getUser();
        $entreprise = $user->getEntreprise();
        $sites = $siteRepository->findBy(['entreprise' => $entreprise]);

        $data = array_map(fn($site) => [
            'id' => $site->getId(),
            'codesite' => $site->getCodesite(),
            'libellesite' => $site->getLibellesite()
        ], $sites);

        return $this->json($data, 200, ['Content-Type' => 'application/json']);
    }
}
