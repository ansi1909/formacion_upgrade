<?php

namespace Link\BackendBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Link\ComunBundle\Entity\CertiPrueba;
use Link\ComunBundle\Entity\CertiEstatusContenido;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
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
                               'pagina' => $p->getPagina()->getCategoria()->getNombre().': '.$p->getPagina()->getNombre(),
                               'preguntas' => $preguntas,
                               'status' => $p->getEstatusContenido()->getNombre(),
                               'modificacion' => $p->getFechaModificacion()->format('d/m/Y H:i a'),
                               'delete_disabled' => $f->linkEliminar($p->getId(), 'CertiPrueba'));

        }

        return $this->render('LinkBackendBundle:Evaluacion:index.html.twig', array('pruebas' => $pruebas));

    }

    public function editAction($prueba_id, Request $request){

        $session = new Session();
        $f = $this->get('funciones');
      
        if (!$session->get('ini'))
        {
            return $this->redirectToRoute('_loginAdmin');
        }
        else {
            if (!$f->accesoRoles($session->get('usuario')['roles'], $session->get('app_id')))
            {
                return $this->redirectToRoute('_authException');
            }
        }
        $f->setRequest($session->get('sesion_id'));

        $em = $this->getDoctrine()->getManager();
        
        if ($prueba_id) 
        {
            $prueba = $em->getRepository('LinkComunBundle:CertiPrueba')->find($prueba_id);
        }
        else {
            $prueba = new CertiPrueba();
            $prueba->setFechaCreacion(new \DateTime('now'));
        }

        $usuario = $em->getRepository('LinkComunBundle:AdminUsuario')->find($session->get('usuario')['id']);
        $prueba->setUsuario($usuario);
        $prueba->setFechaModificacion(new \DateTime('now'));

        $form = $this->createFormBuilder($prueba)
            ->setAction($this->generateUrl('_editEvaluacion', array('prueba_id' => $prueba_id)))
            ->setMethod('POST')
            ->add('nombre', TextType::class, array('label' => $this->get('translator')->trans('Nombre')))
            ->add('cantidadPreguntas', IntegerType::class, array('label' => $this->get('translator')->trans('Cantidad de preguntas de la evaluación')))
            ->add('cantidadMostrar', IntegerType::class, array('label' => $this->get('translator')->trans('Cantidad de preguntas a mostrar')))
            ->add('duracion', TimeType::class, array('label' => $this->get('translator')->trans('Tiempo de duración de la evaluación'),
                                                     'input' => 'datetime',
                                                     'widget' => 'single_text',
                                                     'html5' => true))
            ->add('estatusContenido', EntityType::class, array('class' => 'Link\\ComunBundle\\Entity\\CertiEstatusContenido',
                                                               'choice_label' => 'nombre',
                                                               'expanded' => false,
                                                               'label' => $this->get('translator')->trans('Estatus')))
            ->getForm();

        $form->handleRequest($request);
       
        if ($request->getMethod() == 'POST')
        {

            $pagina_id = $request->request->get('pagina_id');
            $pagina = $em->getRepository('LinkComunBundle:CertiPagina')->find($pagina_id);

            $prueba->setPagina($pagina);
            $em->persist($prueba);
            $em->flush();

            $query = $em->createQuery('SELECT COUNT(p.id) FROM LinkComunBundle:CertiPregunta p 
                                        WHERE p.prueba = :prueba_id')
                        ->setParameter('prueba_id', $prueba->getId());
            $hay_preguntas = $query->getSingleScalarResult();

            if ($prueba->getCantidadPreguntas() > 0 && !$hay_preguntas)
            {
                return $this->redirectToRoute('_editPregunta', array('prueba_id' => $prueba->getId(),
                                                                     'pregunta_id' => 0,
                                                                     'cantidad' => 1,
                                                                     'total' => $prueba->getCantidadPreguntas()));
            }
            else {
                return $this->redirectToRoute('_preguntas', array('prueba_id' => $prueba->getId()));
            }
            
        }

        // Páginas y sub-páginas
        $paginas = array();
        $tiene = 0;
        $str = '';

        $query = $em->createQuery("SELECT p FROM LinkComunBundle:CertiPagina p 
                                    WHERE p.pagina IS NULL
                                    ORDER BY p.id ASC");
        $pages = $query->getResult();

        // Páginas que ya tienen asociadas sus evaluaciones
        $paginas_asociadas = array();

        $query = $em->createQuery("SELECT p FROM LinkComunBundle:CertiPrueba p 
                                    WHERE p.id != :prueba_id")
                    ->setParameter('prueba_id', $prueba->getId() ? $prueba->getId() : 0);
        $tests = $query->getResult();

        foreach ($tests as $test)
        {
            $paginas_asociadas[] = $test->getPagina()->getId();
        }

        foreach ($pages as $page)
        {
            $tiene++;
            $check = in_array($page->getId(), $paginas_asociadas) ? ' <span class="fa fa-check"></span>' : '';
            $str .= '<li data-jstree=\'{ "icon": "fa fa-angle-double-right" }\' p_id="'.$page->getId().'" p_str="'.$page->getCategoria()->getNombre().': '.$page->getNombre().'">'.$page->getCategoria()->getNombre().': '.$page->getNombre().$check;
            $subPaginas = $f->subPaginas($page->getId(), $paginas_asociadas);
            if ($subPaginas['tiene'] > 0)
            {
                $str .= '<ul>';
                $str .= $subPaginas['str'];
                $str .= '</ul>';
            }
            $str .= '</li>';
        }

        $paginas = array('tiene' => $tiene,
                         'str' => $str);

        return $this->render('LinkBackendBundle:Evaluacion:edit.html.twig', array('form' => $form->createView(),
                                                                                  'prueba' => $prueba,
                                                                                  'paginas' => $paginas));

    }

}
