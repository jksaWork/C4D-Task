<?php

namespace App\Helpers;

use App\Entity\User;
use App\Entity\UserProfile;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class UserHelpers
{
     public function saveUser(
          Request $request,
          EntityManagerInterface $entityManager,
          ValidatorInterface $validator,
          UserPasswordHasherInterface $userPasswordHasher
     ) {
          [$email, $name, $password, $age, $address] = $this->getParametersFromRequest($request);
          // 1 - Save The User And User Profile
          // 2 - Hashed  user Password Befor Save it To Databse

          $user = new User();
          $user->setPassword(
               $userPasswordHasher->hashPassword($user, $password)
          );
          $user->setEmail($email);
          $profile  =  new UserProfile();
          $profile->setName($name);
          $profile->setAddress($address);
          $profile->setAge($age);
          // 3 - Check If User Data Is Valid
          $errors = $validator->validate($user);
          if (count($errors) > 0) {
               $errorsString = (string) $errors;
               throw new  Exception($errorsString, 1);
          }
          // 4 Save User  & User Profile To Database 
          $user->setProfile($profile);
          $entityManager->persist($profile);
          $entityManager->persist($user);
          $entityManager->flush();
     }


     public function updateUser(
          Request $request,
          EntityManagerInterface $entityManager,
          ValidatorInterface $validator,
          UserPasswordHasherInterface $userPasswordHasher

     ) {
          // Praper The Data Befor Update The Database
          $id = $request->attributes->get("id");
          $user = $entityManager->getRepository(User::class)->find($id);
          [$email, $name, $password, $age, $address] = $this->getParametersFromRequest($request);
          // Check IF User Is Exist
          if (!$user) {
               throw new Exception("Not Fond  Error  ", 403);;
          }
          $profile = $entityManager->getRepository(UserProfile::class)->find($user->getId());
          // 1 - Update The User And User Profile
          // 2 - Hashed  user Password Befor Save it To Databse
          if ($password != null) {
               $user->setPassword(
                    $userPasswordHasher->hashPassword($user, $password)
               );
          }
          $user->setEmail($email);
          $profile->setName($name);
          $profile->setUserId($user);
          $profile->setAddress($address);
          $profile->setAge($age);
          // 3 - Check If User Data Is Valid
          $errors = $validator->validate($user);
          if (count($errors) > 0) {
               $errorsString = (string) $errors;
               throw new  Exception($errorsString, 1);
          }
          // Save All Data And Return To Users
          $entityManager->persist($user);
          $entityManager->persist($profile);
          $entityManager->flush();

          return true;
     }

     public function deleteUser(
          Request $request,
          EntityManagerInterface $entityManager,

     ) {
          $id = $request->attributes->get("id");
          $user = $entityManager->getRepository(User::class)->find($id);
          // 1 - Check if user Exist Or not
          if (!$user) {
               throw new Exception("Not Fond  Error  ", 403);;
          }
          // 2 - Remove The User Form Database

          $entityManager->remove(
               $user,
               $entityManager->getRepository(UserProfile::class)->find(1)
          );
          $entityManager->flush();
     }

     public function getParametersFromRequest($request)
     {
          return [
               $request->request->get('email'),
               $request->request->get('name'),
               $request->request->get('password'),
               $request->request->get('age'),
               $request->request->get('address'),
          ];
     }
}
