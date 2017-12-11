<?php

namespace Link\BackendBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Link\ComunBundle\Entity\CertiPagina;
use Link\ComunBundle\Entity\CertiCategoria;
use Link\ComunBundle\Entity\CertiEstatusCategoria;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;

class EvaluacionController extends Controller
{
    public function indexAction($app_id)
    {

    	$session = new Session();
        $f = $this->get('funciones');
        
        if (!$session->get('ini'))
        {
            return $this->redirectToRoute('_loginAdmin');
        }
        else {

        	$session->set('app_id', $app_id);
        	if (!$f->accesoRoles($session->get('usuario')['roles'], $session->get('app_id')))
        	{
        		return $this->redirectToRoute('_authException');
        	}
        }
        $f->setRequest($session->get('sesion_id'));

        $em = $this->getDoctrine()->getManager();
        $pruebas_bd = $em->getRepository('LinkComunBundle:CertiPrueba')->findAll();

        $pruebas = array();

        foreach ($pruebas_bd as $p)
        {

            $preguntas = array();

            $query = $em->createQuery("SELECT p FROM LinkComunBundle:CertiPregunta p 
                                        WHERE p.prueba = :prueba_id AND p.pregunta IS NULL
                                        ORDER BY p.id ASC")
                        ->setParameter('prueba_id', $p->getId());
            $preguntas_bd = $query->getResult();

            foreach ($preguntas_bd as $q)
            {
                $preguntas[] = $q->getEnunciado();
            }

            $pruebas[] = array('id' => $p->getId(),
                               'nombre' => $p->getNombre(),
                               'preguntas' => $preguntas,
                               'status' => $p->getEstatusContenido()->getNombre(),
                               'modificacion' => $p->getFechaModificacion()->format('d/m/Y H:i a'),
                               'usuario' => $p->getUsuario()->getNombre().' '.$p->getUsuario()->getApellido(),
                               'delete_disabled' => $f->linkEliminar($p->getId(), 'CertiPrueba'));

        }

        return $this->render('LinkBackendBundle:Evaluacion:index.html.twig', array('pruebas' => $pruebas));

    }

}
