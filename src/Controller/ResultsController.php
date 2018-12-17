<?php

namespace App\Controller;

use App\Entity\Results;
use App\Form\ResultsType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/results")
 */
class ResultsController extends AbstractController
{
    /**
     * @Route("/", name="results_index", methods={"GET"})
     */
    public function index(): Response
    {
        $results = $this->getDoctrine()
            ->getRepository(Results::class)
            ->findAll();

        return $this->render('results/index.html.twig', ['results' => $results]);
    }

    /**
     * @Route("/new", name="results_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $result = new Results();
        $form = $this->createForm(ResultsType::class, $result);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($result);
            $entityManager->flush();

            return $this->redirectToRoute('results_index');
        }

        return $this->render('results/new.html.twig', [
            'result' => $result,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="results_show", methods={"GET"})
     */
    public function show(Results $result): Response
    {
        return $this->render('results/show.html.twig', ['result' => $result]);
    }

    /**
     * @Route("/{id}/edit", name="results_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Results $result): Response
    {
        $form = $this->createForm(ResultsType::class, $result);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('results_index', ['id' => $result->getId()]);
        }

        return $this->render('results/edit.html.twig', [
            'result' => $result,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="results_delete", methods={"DELETE"})
     */
    public function delete(Request $request, Results $result): Response
    {
        if ($this->isCsrfTokenValid('delete'.$result->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($result);
            $entityManager->flush();
        }

        return $this->redirectToRoute('results_index');
    }
}
