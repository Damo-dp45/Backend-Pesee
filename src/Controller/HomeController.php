<?php

namespace App\Controller;

use App\Entity\Entreprise;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

final class HomeController extends AbstractController
{
    #[Route('/', name: 'home')]
    public function index(/* EntityManagerInterface $em, UserPasswordHasherInterface $hasher */): JsonResponse
    {
        /*
            $entreprise = new Entreprise();
            $entreprise
                ->setNom('Laaa')
                ->setAdresse('Laaoaoa')
                ->setCodeentreprise('ENT001')
                ->setContact('0544778855')
            ;
            $em->persist($entreprise);
            $user = new User();
            $user
                ->setEmail('adam@gmail.com')
                ->setNom('Bak')
                ->setPrenom('Adama')
                ->setPassword($hasher->hashPassword($user, 'adam'))
                ->setEntreprise($entreprise)
                ->setRoles(['ROLE_ADMIN'])
            ;
            $em->persist($user);
            $em->flush();
        */
        return $this->json([
            'message' => 'Welcome to your new controller!',
            'path' => 'src/Controller/HomeController.php',
        ]);
    }
}
