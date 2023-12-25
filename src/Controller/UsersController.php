<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\UserProfile;
use App\Form\UserType;
use App\Helpers\UserHelpers;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\MakerBundle\Validator;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasher;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Throwable;

class UsersController extends AbstractController
{

    public $helper;
    public function __construct()
    {
        $this->helper = new UserHelpers();
    }
    #[Route('/users', name: 'app_users')]
    public function index(UserRepository $userRepository): Response
    {
        // dd($userRepository->findAll());

        return $this->render('users/index.html.twig', [
            'users' => $userRepository->findAll(),
            'error' => null
        ]);
    }

    //  Create New User
    #[Route('/create', name: 'app_user_new', methods: ['GET', 'POST'])]
    public function new(): Response
    {

        $user = new User();
        return $this->render('users/new.html.twig', [
            'user' => $user,
            'error' => null,
        ]);
    }


    //  Create New User
    #[Route('/save', name: 'users_save', methods: ['GET', 'POST'])]
    public function save(
        Request $request,
        EntityManagerInterface $entityManager,
        ValidatorInterface $validator,
        UserPasswordHasherInterface $userPasswordHasher
    ): Response {
        try {
            // Validate The Request And Destruct Data
            $this->helper->saveUser($request, $entityManager, $validator, $userPasswordHasher);
            // 4 - Return Reponse Affter  Save The User And Redirect It To Inedx Page
            return $this->redirectToRoute('app_users', [], Response::HTTP_SEE_OTHER);
        } catch (\Throwable $th) {
            // Error message
            return $this->render('users/new.html.twig', [
                'error' => "Some Thing Went Wonrg \n" . $th->getMessage(),
            ]);
        }
    }

    #[Route('show/{id}', name: "app_user_show")]
    public function show(User $user)
    {
        return $this->render('users/show.html.twig', [
            'user' => $user,
        ]);
    }




    #[Route("/edit/{id}", name: "app_user_edit")]
    public function edit(
        User $user
    ): Response {
        try {
            return $this->render('users/edit.html.twig', [
                'user' => $user,
                'error' => null,
            ]);
        } catch (Throwable $th) {
            echo "Some Thing Went Wrong";
        }
    }

    #[Route('update/{id}', name: "users_update", methods: ['POST'])]
    public function update(
        Request $request,
        EntityManagerInterface $entityManager,
        ValidatorInterface $validator,
        UserPasswordHasherInterface $userPasswordHasher
    ) {
        try {
            //  Fetch User And Update The Database
            $this->helper->updateUser($request, $entityManager, $validator, $userPasswordHasher);
            //  Return Reponse Affter  Update The User And Redirect It To Inedx Page
            return $this->redirectToRoute('app_users', [], Response::HTTP_SEE_OTHER);
        } catch (\Throwable $th) {
            // If Some Thing Go Worng
            return $this->render('users/index.html.twig', [
                'error' => "Some Thing Went Wonrg \n" . $th->getMessage(),
                'users' => []
            ]);
        }
    }
    // delete Routed
    #[Route('delete/{id}', name: "users_delete", methods: ['POST'])]
    public function delete(
        Request $request,
        EntityManagerInterface $entityManager,
    ) {
        try {
            //  Fetch User And Update The Database
            $this->helper->deleteUser($request, $entityManager);
            //  Return Reponse Affter  Update The User And Redirect It To Inedx Page
            return $this->redirectToRoute('app_users', [], Response::HTTP_SEE_OTHER);
        } catch (\Throwable $th) {
            // If Some Thing Go Worng
            return $this->render('users/index.html.twig', [
                'error' => "Some Thing Went Wonrg \n" . $th->getMessage(),
                'users' => []
            ]);
        }
    }
    //  Get Parameters From  Request
    public function getParametersFromRequest($request)
    {
        return [
            $request->request->get('email'),
            $request->request->get('name'),
            $request->request->get('password'),
            $request->request->get('age'),
            $request->request->get('address'),
            // $request->request->get('email'), 
        ];
    }
}
