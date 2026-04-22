<?php

namespace App\Controller;

use App\Entity\Entreprise;
use App\Entity\User;
use App\Repository\EntrepriseRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class RegistrationController extends AbstractController
{
    #[Route('/api/inscription', name: 'register', methods: ['POST'])]
    public function register(
        Request $request,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $hasher,
        ValidatorInterface $validator
    ): JsonResponse
    {
        $data = $request->toArray();

        $entreprise = new Entreprise();
        $entreprise->setCodeentreprise(strtoupper(trim($data['codeentreprise'] ?? '')));
        $entreprise->setNom(trim($data['nomentreprise'] ?? ''));
        $entreprise->setAdresse(trim($data['adresse'] ?? '') ?: null);
        $entreprise->setContact(trim($data['contact'] ?? '') ?: null);

        $user = new User();
        $user->setNom(trim($data['nom'] ?? ''));
        $user->setPrenom(trim($data['prenom'] ?? ''));
        $user->setEmail(trim($data['email'] ?? ''));
        $user->setRoles(['ROLE_ADMIN']);
        $user->setEntreprise($entreprise);
        $user->setPlainPassword($data['password'] ?? '');

        $entrepriseErrors = $validator->validate($entreprise);
        $userErrors = $validator->validate($user);

        if(count($entrepriseErrors) > 0 || count($userErrors) > 0) {
            $errors = [];

            foreach($entrepriseErrors as $violation) {
                $field = $violation->getPropertyPath();
                $key = $field === 'nom' ? 'nomentreprise' : $field;
                $errors[$key] = $violation->getMessage();
            }

            foreach($userErrors as $violation) {
                $field = $violation->getPropertyPath();
                $key = $field === 'plainPassword' ? 'password' : $field;
                $errors[$key] = $violation->getMessage();
            }

            return $this->json([
                'errors' => $errors
            ], 422);
        }

        $user->setPassword($hasher->hashPassword($user, $user->getPlainPassword()));
        $user->setPlainPassword('');

        $em->persist($entreprise);
        $em->persist($user);
        $em->flush();

        return $this->json([
            'message' => 'Compte créé avec succès.',
        ], 201);
    }

    #[Route('/api/inscription/utilisateurs', name: 'register.users', methods: ['POST'])]
    public function registerUsers(
        Request $request,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $hasher,
        ValidatorInterface $validator,
        EntrepriseRepository $entrepriseRepository
    ): JsonResponse
    {
        $data = $request->toArray();
        $codeentreprise = strtoupper(trim($data['codeentreprise'] ?? ''));
        $entreprise = $entrepriseRepository->findOneByCode($codeentreprise);

        if(!$entreprise) {
            return $this->json([
                'errors' => [
                    'codeentreprise' => 'Aucune entreprise trouvée avec ce code.',
                ]
            ], 422);
        }

        $user = new User();
        $user
            ->setNom(trim($data['nom'] ?? ''))
            ->setPrenom(trim($data['prenom'] ?? ''))
            ->setEmail(trim($data['email'] ?? ''))
            ->setRoles(['ROLE_USER'])
            ->setEntreprise($entreprise)
            ->setPlainPassword($data['password'] ?? '')
        ;
        $violations = $validator->validate($user);

        if(count($violations) > 0) {
            $errors = [];
            foreach ($violations as $violation) {
                $field = $violation->getPropertyPath();
                $key = $field === 'plainPassword' ? 'password' : $field;
                $errors[$key] = $violation->getMessage();
            }
            return $this->json(['errors' => $errors], 422);
        }

        $user->setPassword($hasher->hashPassword($user, $user->getPlainPassword()));
        $user->setPlainPassword('');

        $em->persist($user);
        $em->flush();

        return $this->json([
            'message' => 'Compte créé avec succès.'
        ], 201);
    }
}
