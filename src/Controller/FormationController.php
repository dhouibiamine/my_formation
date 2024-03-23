<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Formation;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\HttpFoundation\Request;
use App\Form\FormationType;

class FormationController extends AbstractController
{
    #[Route('/formation', name: 'app_formation')]
    public function index(): Response
    {
        return $this->render('formation/index.html.twig', [
            'id' => getId(),
        ]);
    }
      /**
     * @Route("/formation/{id}", name="formation_show")
     */
    public function show($id)
    
    {
        $formation = $this-> getDoctrine()->getRepository(Formation::class)
    ->find($id);
    

if (!$formation) {
    throw $this->createNotFoundException('Formation non trouvée');
}

        return $this->render('formation/show.html.twig', [
            'formation' => $formation,
        ]);
    }
    /**
 * @Route("/ajouter", name="ajouter")
 */
public function ajouter(Request $request): Response
{
    $formation = new Formation();
    
    $form = $this->createForm(FormationType::class, $formation);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($formation);
        $entityManager->flush();

        return $this->redirectToRoute('liste_formation'); 
    }

    return $this->render('formation/ajouter.html.twig', [
        'form' => $form->createView(),
    ]);
}
/**
     * @Route("/liste-formation", name="liste_formation")
     */
    public function listeFormation(): Response
    {
        
        $formations = $this->getDoctrine()->getRepository(Formation::class)->findAll();

        
        return $this->render('formation/liste_formation.html.twig', [
            'formations' => $formations,
        ]);
    }
       

    /**
     * @Route("/edit-formation/{id}", name="edit_formation")
     */
    public function edit(Request $request, $id): Response
    {
        $entityManager = $this->getDoctrine()->getManager();
        $formation = $entityManager->getRepository(Formation::class)->find($id);

        
        if (!$formation) {
            throw $this->createNotFoundException('Formation non trouvée');
        }

        
        $form = $this->createForm(FormationType::class, $formation);
        $form->handleRequest($request);

        
        if ($form->isSubmitted() && $form->isValid()) {
            
            $entityManager->flush();

            
            $this->addFlash('success', 'Formation mise à jour avec succès.');

            
            return $this->redirectToRoute('liste_formation');
        }

        
        return $this->render('formation/edit.html.twig', [
            'form' => $form->createView(),
            'formation' => $formation,
        ]);
    }
   
}
