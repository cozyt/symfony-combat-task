<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Fighter;
use AppBundle\Form\FighterType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

class FighterController extends Controller
{
    /**
     * @Route("/fighter/create", name="fighter.create")
     */
    public function createAction(Request $request)
    {
        $fighter = new Fighter();

        $form = $this->createForm(FighterType::class, $fighter);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $fighter = $form->getData();

            $em = $this->getDoctrine()->getManager();
            $em->persist($fighter);
            $em->flush();

            return $this->redirectToRoute('fighter.created');
        }

        return $this->render('AppBundle:Fighter:create.html.twig', array(
            'form' => $form->createView(),
        ));
    }

    /**
     * @Route("/fighter/created", name="fighter.created")
     */
    public function created()
    {
        return "Fighter created";
    }

}
