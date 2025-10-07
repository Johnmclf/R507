<?php

namespace App\Controller;

use App\Entity\Contact;
use App\Repository\ContactRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

#[Route('/', name: 'main_')]
final class MainController extends AbstractController
{
    #[Route('/connexion', name: 'login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        if (!empty($this->getUser()))
            return $this->redirectToRoute('admin');

        return $this->render('@EasyAdmin/page/login.html.twig', [
            'error' => $authenticationUtils->getLastAuthenticationError(),
            'last_username' => $authenticationUtils->getLastUsername(),
            'csrf_token_intention' => 'authenticate',
            'target_path' => $this->generateUrl('admin'),
            'username_label' => 'Utilisateur',
            'password_label' => 'Mot de passe',
            'sign_in_label' => 'Connexion',

            'forgot_password_enabled' => false,
            'remember_me_enabled' => false,
        ]);
    }

    #[Route('/deconnexion', name: 'logout')]
    public function logout(): Response
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }

    #[Route('', name: 'index')]
    public function index(Request $request, EntityManagerInterface $em): Response
    {
        $contact = new Contact();

        $form = $this->createFormBuilder($contact)
            ->add('firstName', null, ['label' => 'Prénom'])
            ->add('name', null, ['label' => 'Nom'])
            ->add('message', null, ['label' => 'Message'])
            ->add('send', SubmitType::class, ['label' => 'Envoyer'])
            ->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $contact->setCreatedAt(new \DateTimeImmutable('now'));

            $em->persist($contact);
            $em->flush();

            return $this->redirectToRoute('main_index');
        }

        return $this->render('main/index.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/liste/{page}', name: 'list')]
    public function list(ContactRepository $repository, Request $request, ?int $page = 1, ?string $status = "all"): Response
    {
        $limit = 2;
        $status = $request->query->get('status', 'all');

        $contacts = $repository->findPaginatedByStatus($page, $limit, $status);
        $totalPages = ceil($repository->countByStatus($status) / $limit);

        return $this->render('main/list.html.twig', [
            'contacts' => $contacts,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'currentStatus' => $status,
        ]);
    }


}
