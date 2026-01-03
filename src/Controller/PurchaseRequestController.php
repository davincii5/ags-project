<?php

namespace App\Controller;

use App\Entity\PurchaseRequest;
use App\Form\PurchaseRequestType;
use App\Repository\PurchaseRequestRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/purchase/request')]
final class PurchaseRequestController extends AbstractController
{
    // ======================
    // LIST / INDEX
    // ======================
    #[Route(name: 'app_purchase_request_index', methods: ['GET'])]
    public function index(PurchaseRequestRepository $repo): Response
    {
        $user = $this->getUser();

        if ($this->isGranted('ROLE_ADMIN')) {
            // Admin voit toutes les requests
            $requests = $repo->findAll();
        } elseif ($this->isGranted('ROLE_MANAGER')) {
            // Manager voit uniquement ses propres demandes
            $requests = $repo->findBy(['requestedBy' => $user]);
        } else {
            // Magasinier (lecture seule) peut voir toutes les demandes
            $requests = $repo->findAll();
        }

        return $this->render('purchase_request/index.html.twig', [
            'purchase_requests' => $requests,
        ]);
    }

    // ======================
    // CREATE NEW
    // ======================
    #[Route('/new', name: 'app_purchase_request_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();

        // Seul un Manager peut créer une PurchaseRequest
        $this->denyAccessUnlessGranted('ROLE_MANAGER');

        $purchaseRequest = new PurchaseRequest();
        $purchaseRequest->setRequestedBy($user);

        $form = $this->createForm(PurchaseRequestType::class, $purchaseRequest);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($purchaseRequest);
            $entityManager->flush();

            return $this->redirectToRoute('app_purchase_request_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('purchase_request/new.html.twig', [
            'purchase_request' => $purchaseRequest,
            'form' => $form,
        ]);
    }

    // ======================
    // SHOW
    // ======================
    #[Route('/{id}', name: 'app_purchase_request_show', methods: ['GET'])]
    public function show(PurchaseRequest $purchaseRequest): Response
    {
        // Tout le monde peut voir
        return $this->render('purchase_request/show.html.twig', [
            'purchase_request' => $purchaseRequest,
        ]);
    }

    // ======================
    // EDIT
    // ======================
    #[Route('/{id}/edit', name: 'app_purchase_request_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, PurchaseRequest $purchaseRequest, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();

        // Seul Admin ou le Manager qui a créé la request peut modifier
        if (!$this->isGranted('ROLE_ADMIN') && $purchaseRequest->getRequestedBy() !== $user) {
            throw $this->createAccessDeniedException('Vous ne pouvez pas modifier cette demande.');
        }

        $form = $this->createForm(PurchaseRequestType::class, $purchaseRequest);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_purchase_request_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('purchase_request/edit.html.twig', [
            'purchase_request' => $purchaseRequest,
            'form' => $form,
        ]);
    }

    // ======================
    // DELETE
    // ======================
    #[Route('/{id}', name: 'app_purchase_request_delete', methods: ['POST'])]
    public function delete(Request $request, PurchaseRequest $purchaseRequest, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();

        // Seul Admin ou le Manager qui a créé la request peut supprimer
        if (!$this->isGranted('ROLE_ADMIN') && $purchaseRequest->getRequestedBy() !== $user) {
            throw $this->createAccessDeniedException('Vous ne pouvez pas supprimer cette demande.');
        }

        if ($this->isCsrfTokenValid('delete'.$purchaseRequest->getId(), $request->request->get('_token'))) {
            $entityManager->remove($purchaseRequest);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_purchase_request_index', [], Response::HTTP_SEE_OTHER);
    }
}
