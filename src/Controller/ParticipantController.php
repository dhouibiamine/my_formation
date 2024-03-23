<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use App\Form\ParticipantType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use App\Entity\Participant;
use App\Entity\Formation;


class ParticipantController extends AbstractController
{
    #[Route('/participant', name: 'app_participant')]
    public function index(): Response
    {
        return $this->render('participant/index.html.twig', [
            'controller_name' => 'ParticipantController',
        ]);
    }
    
    
    
 /**
     * @Route("/add", name="add_participant")
     */
    public function ajout(Request $request  )
    { $participant = new Participant();
   
        $fb = $this->createFormBuilder($participant)
        ->add('nom')
            ->add('email')
            ->add('is_subscribe')
            ->add('fonction')
            ->add('Formation', EntityType::class, [
                'class' => Formation::class, 
                'choice_label' => 'titre',  ])
        
            ->add('Valider', submitType::class);
        $form = $fb->getForm();
         $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) { 
            $em = $this->getDoctrine()->getManager();
            $formation = $form->get('Formation')->getData();
            $participant->setFormation($formation);
            $em->persist($participant);
            $em->flush();
            return $this->redirectToRoute('liste_participant', ['formationId' => $participant->getFormation()->getId()]);

        }
        return $this->render('participant/addM.html.twig',
        ['form' => $form->createView()]
        );
    }
/**
 * @Route("/liste_participant/{formationId}", name="liste_participant")
 */
public function liste(Request $request, $formationId): Response {
    $form = $this->createFormBuilder()
        ->add("critere", TextType::class)
        ->add("valider", SubmitType::class)
        ->getForm();

    $form->handleRequest($request);

    $em = $this->getDoctrine()->getManager();
    $repo = $em->getRepository(Formation::class);

    
    $formation = $repo->find($formationId);

    
    if (!$formation) {
        throw $this->createNotFoundException('Formation non trouvée');
    }

    
    $lesparticipants = $formation->getParticipant();

    if ($form->isSubmitted()) {
        $data = $form->getData();
        $critere = $data['critere'];
        
        $lesparticipants = $repo->recherche($critere);
    }

    return $this->render('participant/liste_participant.html.twig', [
        'lesparticipants' => $lesparticipants,
        'form' => $form->createView(),
        'formation' => $formation,
    ]);
}



#[Route('/supp/{id}', name: 'can_delete')]
public function delete(Request $request, $id): Response
{
    $entityManager = $this->getDoctrine()->getManager();
    $participant = $entityManager->getRepository(Participant::class)->find($id);

    if (!$participant) {
        throw $this->createNotFoundException('No participant found for id ' . $id);
    }

    $entityManager->remove($participant);
    $entityManager->flush();

    
    return $this->redirectToRoute('liste_participant', ['id' => $participant->getFormation()->getId()]);
}

/**
 * @Route("/edit-participant/{id}", name="edit_participant")
 */

public function edit(Request $request, $id): Response
{
    $entityManager = $this->getDoctrine()->getManager();
    $participant = $entityManager->getRepository(Participant::class)->find($id);

    if (!$participant) {
        throw $this->createNotFoundException('Participant non trouvé pour l\'ID ' . $id);
    }

    $form = $this->createForm(ParticipantType::class, $participant);

    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        
        $entityManager->flush();

        
        $formationId = $participant->getFormation()->getId();

        
        $this->addFlash('success', 'Participant mis à jour avec succès.');

        
        return $this->redirectToRoute('liste_participant', ['formationId' => $formationId]);

    }

    return $this->render('participant/edit.html.twig', [
        'form' => $form->createView(),
        'participant' => $participant,
    ]);
}

}
