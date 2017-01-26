<?php
namespace AppBundle\Controller;

use AppBundle\Fight\Fighter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class FightController extends Controller
{
    /**
     * @Route("/", name="home")
     *
     * @return Response
     */
    public function startAction()
    {
        return $this->render('AppBundle:Fight:start.html.twig');
    }

    /**
     * @Route("/fighters", name="fighters")
     *
     * @return Response
     */
    public function selectFightersAction()
    {
        $fighters = Fighter::list();

        return $this->render('AppBundle:Fight:fighters.html.twig', [
            'fighters' => $fighters,
        ]);
    }
}
