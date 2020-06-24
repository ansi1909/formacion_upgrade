<?php

namespace Link\BackendBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Link\ComunBundle\Entity\CertiPrueba;
use Link\ComunBundle\Entity\CertiPregunta;
use Link\ComunBundle\Entity\CertiTipoPregunta;
use Link\ComunBundle\Entity\CertiTipoElemento;
use Link\ComunBundle\Entity\CertiEstatusContenido;
use Link\ComunBundle\Entity\CertiOpcion;
use Link\ComunBundle\Entity\CertiPreguntaOpcion;
use Link\ComunBundle\Entity\CertiPreguntaAsociacion;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Yaml\Yaml;

class EvaluacionController extends Controller
{
    public function indexAction($app_id)
    {

    	$session = new Session();
        $f = $this->get('funciones');

        if (!$session->get('ini') || $f->sesionBloqueda($session->get('sesion_id')))
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
        $pruebas = $f->obtenerPruebas();


        return $this->render('LinkBackendBundle:Evaluacion:index.html.twig', array('pruebas' => $pruebas));

    }

    public function ajaxObtenerPreguntasAction(Request $request){
        $f = $this->get('funciones');
        $em = $this->getDoctrine()->getManager();
        $html='';
        $yml = Yaml::parse(file_get_contents($this->get('kernel')->getRootDir().'/config/parametros.yml'));
        $prueba_id = $request->get('prueba_id');
        $preguntas = $em->getRepository('LinkComunBundle:CertiPregunta')->findBy(array('prueba'=>$prueba_id));
        $item = 1;
        $evaluacion = $em->getRepository('LinkComunBundle:CertiPrueba')->find($prueba_id);
        $titulo = explode("|",$evaluacion->getNombre());
        foreach ($preguntas as $pregunta) {
            $html .='<div class="col-sm-16 col-md-16 col-md-lg-16 modal-text  margin-1">&nbsp;'."$item) ".$pregunta->getEnunciado().'
            </div>';
            $item++;
        }
        $return = json_encode(['html' => $html,'evaluacion'=>$titulo[1]]);
        return new Response($return, 200, array('Content-Type' => 'application/json'));
    }

    public function editAction($prueba_id, Request $request){

        $session = new Session();
        $f = $this->get('funciones');

        if (!$session->get('ini') || $f->sesionBloqueda($session->get('sesion_id')))
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
        $pagina_evaluada = 0;
        $pagina_id = '';
        $pagina_str = '';

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
                                                     'html5' => false))
            ->add('estatusContenido', EntityType::class, array('class' => 'Link\\ComunBundle\\Entity\\CertiEstatusContenido',
                                                               'choice_label' => 'nombre',
                                                               'expanded' => false,
                                                               'label' => $this->get('translator')->trans('Estatus')))
            ->getForm();

        $form->handleRequest($request);

        if ($request->getMethod() == 'POST')
        {

            $pagina_id = $request->request->get('pagina_id');
            $pagina_str = $request->request->get('pagina_str');

            // Verificamos si la página asociada ya había sido seleccionada
            $qb = $em->createQueryBuilder();
            $qb->select('COUNT(p.id)')
               ->from('LinkComunBundle:CertiPrueba', 'p')
               ->where('p.pagina = :pagina_id');
            $parametros['pagina_id'] = $pagina_id;

            if ($prueba_id)
            {
                $qb->andWhere('p.id != :prueba_id');
                $parametros['prueba_id'] = $prueba_id;
            }

            $qb->setParameters($parametros);
            $query = $qb->getQuery();
            $pagina_evaluada = $query->getSingleScalarResult();

            $pagina = $em->getRepository('LinkComunBundle:CertiPagina')->find($pagina_id);
            $prueba->setPagina($pagina);

            if (!$pagina_evaluada)
            {

                $em->persist($prueba);
                $em->flush();

                $query = $em->createQuery('SELECT COUNT(p.id) FROM LinkComunBundle:CertiPregunta p
                                            WHERE p.prueba = :prueba_id')
                            ->setParameter('prueba_id', $prueba->getId());
                $hay_preguntas = $query->getSingleScalarResult();

                if (!$hay_preguntas)
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
                $str .= $subPaginas['return'];
                $str .= '</ul>';
            }
            $str .= '</li>';
        }

        $paginas = array('tiene' => $tiene,
                         'str' => $str);

        return $this->render('LinkBackendBundle:Evaluacion:edit.html.twig', array('form' => $form->createView(),
                                                                                  'prueba' => $prueba,
                                                                                  'paginas' => $paginas,
                                                                                  'pagina_evaluada' => $pagina_evaluada,
                                                                                  'pagina_id' => $pagina_id,
                                                                                  'pagina_str' => $pagina_str));

    }

    public function editPreguntaAction($prueba_id, $pregunta_id, $cantidad, $total, Request $request){

        $session = new Session();
        $f = $this->get('funciones');

        if (!$session->get('ini') || $f->sesionBloqueda($session->get('sesion_id')))
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

        $prueba = $em->getRepository('LinkComunBundle:CertiPrueba')->find($prueba_id);

        if ($pregunta_id)
        {
            $pregunta = $em->getRepository('LinkComunBundle:CertiPregunta')->find($pregunta_id);
        }
        else {

            $pregunta = new CertiPregunta();
            $pregunta->setFechaCreacion(new \DateTime('now'));

            // Establecer el orden, último por defecto
            $query = $em->createQuery("SELECT p FROM LinkComunBundle:CertiPregunta p
                                        WHERE p.pregunta IS NULL AND p.prueba = :prueba_id
                                        ORDER BY p.orden DESC")
                        ->setParameter('prueba_id', $prueba->getId());
            $preguntas = $query->getResult();

            if ($preguntas)
            {
                $orden = $preguntas[0]->getOrden()+1;
            }
            else {
                $orden = 1;
            }

            $pregunta->setOrden($orden);

        }

        $pregunta->setPrueba($prueba);
        $usuario = $em->getRepository('LinkComunBundle:AdminUsuario')->find($session->get('usuario')['id']);
        $pregunta->setUsuario($usuario);
        $pregunta->setFechaModificacion(new \DateTime('now'));

        $form = $this->createFormBuilder($pregunta)
            ->setAction($this->generateUrl('_editPregunta', array('prueba_id' => $prueba_id,
                                                                  'pregunta_id' => $pregunta_id,
                                                                  'cantidad' => $cantidad,
                                                                  'total' => $total)))
            ->setMethod('POST')
            ->add('enunciado', TextType::class, array('label' => $this->get('translator')->trans('Enunciado')))
            ->add('imagen', HiddenType::class, array('required' => false))
            ->add('tipoPregunta', EntityType::class, array('class' => 'Link\\ComunBundle\\Entity\\CertiTipoPregunta',
                                                           'choice_label' => 'nombre',
                                                           'expanded' => false,
                                                           'label' => $this->get('translator')->trans('Tipo de pregunta'),
                                                           'placeholder' => ''))
            ->add('tipoElemento', EntityType::class, array('class' => 'Link\\ComunBundle\\Entity\\CertiTipoElemento',
                                                           'choice_label' => 'nombre',
                                                           'expanded' => false,
                                                           'label' => $this->get('translator')->trans('Tipo de elemento'),
                                                           'placeholder' => ''))
            ->add('estatusContenido', EntityType::class, array('class' => 'Link\\ComunBundle\\Entity\\CertiEstatusContenido',
                                                               'choice_label' => 'nombre',
                                                               'expanded' => false,
                                                               'label' => $this->get('translator')->trans('Estatus')))
            ->add('valor', NumberType::class, array('label' => $this->get('translator')->trans('Valor de la pregunta')))
            ->getForm();

        $form->handleRequest($request);

        if ($request->getMethod() == 'POST')
        {

            $em->persist($pregunta);
            $em->flush();

            return $this->redirectToRoute('_opciones', array('pregunta_id' => $pregunta->getId(),
                                                             'cantidad' => $cantidad,
                                                             'total' => $prueba->getCantidadPreguntas()));

        }

        return $this->render('LinkBackendBundle:Evaluacion:editPregunta.html.twig', array('form' => $form->createView(),
                                                                                          'pregunta' => $pregunta,
                                                                                          'cantidad' => $cantidad,
                                                                                          'total' => $total));

    }

    public function opcionesAction($pregunta_id, $cantidad, $total)
    {

        $session = new Session();
        $f = $this->get('funciones');

        if (!$session->get('ini') || $f->sesionBloqueda($session->get('sesion_id')))
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
        $values = Yaml::parse(file_get_contents($this->get('kernel')->getRootDir().'/config/parametros.yml'));
        $pregunta_asociacion = $values['parameters']['tipo_pregunta']['asociacion'];
        $pregunta_simple = $values['parameters']['tipo_pregunta']['simple'];
        $es_asociacion = 0;
        $opciones = array();

        $pregunta = $em->getRepository('LinkComunBundle:CertiPregunta')->find($pregunta_id);
        $es_simple = $pregunta->getTipoPregunta()->getId() == $pregunta_simple ? 1 : 0;

        if ($pregunta->getTipoPregunta()->getId() != $pregunta_asociacion)
        {

            $query = $em->createQuery("SELECT po, o FROM LinkComunBundle:CertiPreguntaOpcion po
                                        JOIN po.opcion o
                                        WHERE po.pregunta = :pregunta_id AND o.prueba = :prueba_id
                                        ORDER BY o.id ASC")
                        ->setParameters(array('pregunta_id' => $pregunta_id,
                                              'prueba_id' => $pregunta->getPrueba()->getId()));
            $opciones_bd = $query->getResult();

            foreach ($opciones_bd as $po)
            {
                $query = $em->createQuery('SELECT COUNT(r.id) FROM LinkComunBundle:CertiRespuesta r
                                            WHERE r.opcion = :opcion_id')
                            ->setParameter('opcion_id', $po->getOpcion()->getId());
                $hay_respuesta = $query->getSingleScalarResult();
                $delete_disabled = $hay_respuesta ? 'disabled' : '';
                $opciones[] = array('id' => $po->getId(),
                                    'descripcion' => $po->getOpcion()->getDescripcion(),
                                    'imagen' => $po->getOpcion()->getImagen(),
                                    'correcta' => $po->getCorrecta(),
                                    'delete_disabled' => $delete_disabled);
            }

        }
        else {

            $es_asociacion = 1;
            $opciones = array();

            $asociacion = $em->getRepository('LinkComunBundle:CertiPreguntaAsociacion')->findOneByPregunta($pregunta_id);

            if ($asociacion)
            {

                $preguntas_asociadas = explode(",", $asociacion->getPreguntas());

                $query = $em->createQuery("SELECT po, o, p FROM LinkComunBundle:CertiPreguntaOpcion po
                                            JOIN po.opcion o JOIN po.pregunta p
                                            WHERE po.pregunta IN (:preguntas_id)
                                            AND o.prueba = :prueba_id
                                            AND p.pregunta = :pregunta_id
                                            ORDER BY po.id ASC")
                            ->setParameters(array('preguntas_id' => $preguntas_asociadas,
                                                  'prueba_id' => $pregunta->getPrueba()->getId(),
                                                  'pregunta_id' => $pregunta_id));
                $opciones_bd = $query->getResult();

                foreach ($opciones_bd as $po)
                {
                    $query = $em->createQuery('SELECT COUNT(r.id) FROM LinkComunBundle:CertiRespuesta r
                                                WHERE r.opcion = :opcion_id')
                                ->setParameter('opcion_id', $po->getOpcion()->getId());
                    $hay_respuesta_opcion = $query->getSingleScalarResult();
                    $query = $em->createQuery('SELECT COUNT(r.id) FROM LinkComunBundle:CertiRespuesta r
                                                WHERE r.pregunta = :pregunta_id')
                                ->setParameter('pregunta_id', $po->getPregunta()->getId());
                    $hay_respuesta_pregunta = $query->getSingleScalarResult();
                    $delete_disabled = $hay_respuesta_opcion || $hay_respuesta_pregunta ? 'disabled' : '';
                    $opciones[] = array('id' => $po->getId(),
                                        'pregunta' => $po->getPregunta()->getEnunciado(),
                                        'imagen_pregunta' => $po->getPregunta()->getImagen(),
                                        'opcion' => $po->getOpcion()->getDescripcion(),
                                        'imagen_opcion' => $po->getOpcion()->getImagen(),
                                        'delete_disabled' => $delete_disabled);
                }

            }

        }

        return $this->render('LinkBackendBundle:Evaluacion:opciones.html.twig', array('opciones' => $opciones,
                                                                                      'pregunta' => $pregunta,
                                                                                      'es_asociacion' => $es_asociacion,
                                                                                      'es_simple' => $es_simple,
                                                                                      'cantidad' => $cantidad+1,
                                                                                      'total' => $total));

    }

    public function ajaxEditOpcionAction(Request $request)
    {

        $em = $this->getDoctrine()->getManager();
        $pregunta_opcion_id = $request->query->get('pregunta_opcion_id');
        $uploads = $this->container->getParameter('folders')['uploads'];

        $pregunta_opcion = $this->getDoctrine()->getRepository('LinkComunBundle:CertiPreguntaOpcion')->find($pregunta_opcion_id);

        $return = array('descripcion' => $pregunta_opcion->getOpcion()->getDescripcion(),
                        'imagen' => $pregunta_opcion->getOpcion()->getImagen(),
                        'url_imagen' => $pregunta_opcion->getOpcion()->getImagen() ? $uploads.$pregunta_opcion->getOpcion()->getImagen() : '',
                        'enunciado' => $pregunta_opcion->getPregunta()->getEnunciado(),
                        'imagen_enunciado' => $pregunta_opcion->getPregunta()->getImagen(),
                        'url_imagen_enunciado' => $pregunta_opcion->getPregunta()->getImagen() ? $uploads.$pregunta_opcion->getPregunta()->getImagen() : '',
                        'correcta' => $pregunta_opcion->getCorrecta());

        $return = json_encode($return);
        return new Response($return, 200, array('Content-Type' => 'application/json'));

    }

    public function ajaxCorrectaAction(Request $request)
    {

        $em = $this->getDoctrine()->getManager();

        $pregunta_opcion_id = $request->request->get('pregunta_opcion_id');
        $checked = $request->request->get('checked');

        $pregunta_opcion = $em->getRepository('LinkComunBundle:CertiPreguntaOpcion')->find($pregunta_opcion_id);

        // Si se marca SI, primero se deben colocar las demás en false
        if ($checked)
        {
            $pos = $em->getRepository('LinkComunBundle:CertiPreguntaOpcion')->findByPregunta($pregunta_opcion->getPregunta()->getId());
            foreach ($pos as $po)
            {
                $po->setCorrecta(false);
                $em->persist($po);
                $em->flush();
            }
        }

        $pregunta_opcion->setCorrecta($checked ? true : false);
        $em->persist($pregunta_opcion);
        $em->flush();

        $return = array('id' => $pregunta_opcion->getId());

        $return = json_encode($return);
        return new Response($return, 200, array('Content-Type' => 'application/json'));

    }

    public function ajaxUpdateOpcionAction(Request $request)
    {

        $session = new Session();
        $em = $this->getDoctrine()->getManager();
        $f = $this->get('funciones');
        $values = Yaml::parse(file_get_contents($this->get('kernel')->getRootDir().'/config/parametros.yml'));
        $tipo_elemento_imagen = $values['parameters']['tipo_elemento']['imagen'];
        $uploads = $this->container->getParameter('folders')['uploads'];
        $pregunta_simple = $values['parameters']['tipo_pregunta']['simple'];

        $pregunta_opcion_id = $request->request->get('pregunta_opcion_id');
        $prueba_id = $request->request->get('prueba_id');
        $pregunta_id = $request->request->get('pregunta_id');
        $descripcion = $request->request->get('descripcion');
        $imagen = $request->request->get('imagen');
        $enunciado = $request->request->get('enunciado');
        $imagen_enunciado = $request->request->get('imagen_enunciado');
        $es_asociacion = $request->request->get('es_asociacion');
        $correcta = $request->request->get('correcta');

        $pregunta_padre = $em->getRepository('LinkComunBundle:CertiPregunta')->find($pregunta_id);
        $usuario = $em->getRepository('LinkComunBundle:AdminUsuario')->find($session->get('usuario')['id']);
        $checked = '';

        if ($pregunta_opcion_id)
        {
            $pregunta_opcion = $em->getRepository('LinkComunBundle:CertiPreguntaOpcion')->find($pregunta_opcion_id);
            $opcion = $pregunta_opcion->getOpcion();
            if ($es_asociacion)
            {
                $pregunta = $pregunta_opcion->getPregunta();
            }
        }
        else {
            $opcion = new CertiOpcion();
            $opcion->setFechaCreacion(new \DateTime('now'));
            $pregunta_opcion = new CertiPreguntaOpcion();
            if ($es_asociacion)
            {
                $pregunta = new CertiPregunta();
                $pregunta->setFechaCreacion(new \DateTime('now'));
            }
        }

        // Opcion
        $opcion->setDescripcion($descripcion);
        $opcion->setImagen($pregunta_padre->getTipoElemento()->getId() == $tipo_elemento_imagen ? $imagen : null);
        $opcion->setPrueba($pregunta_padre->getPrueba());
        $opcion->setUsuario($usuario);
        $opcion->setFechaModificacion(new \DateTime('now'));
        $em->persist($opcion);
        $em->flush();

        // Pregunta (Solo si es de asociación)
        if ($es_asociacion)
        {

            $pregunta->setEnunciado($enunciado);
            $pregunta->setImagen($pregunta_padre->getTipoElemento()->getId() == $tipo_elemento_imagen ? $imagen_enunciado : null);
            $pregunta->setPrueba($pregunta_padre->getPrueba());
            $pregunta->setTipoPregunta($pregunta_padre->getTipoPregunta());
            $pregunta->setTipoElemento($pregunta_padre->getTipoElemento());
            $pregunta->setUsuario($usuario);
            $pregunta->setEstatusContenido($pregunta_padre->getEstatusContenido());
            $pregunta->setValor($pregunta_padre->getValor());
            $pregunta->setPregunta($pregunta_padre);
            $pregunta->setFechaModificacion(new \DateTime('now'));
            $em->persist($pregunta);
            $em->flush();

            $pregunta_asociacion = $em->getRepository('LinkComunBundle:CertiPreguntaAsociacion')->findOneByPregunta($pregunta_id);
            if (!$pregunta_asociacion)
            {
                $pregunta_asociacion = new CertiPreguntaAsociacion();
                $preguntas_str = '';
                $opciones_str = '';
            }
            else {
                $preguntas_str = $pregunta_asociacion->getPreguntas();
                $opciones_str = $pregunta_asociacion->getOpciones();
            }
            $preguntas_arr = $preguntas_str != '' ? explode(",", $preguntas_str) : array();
            $opciones_arr = $opciones_str != '' ? explode(",", $opciones_str) : array();
            if (!in_array($pregunta->getId(), $preguntas_arr))
            {
                $preguntas_arr[] = $pregunta->getId();
            }
            if (!in_array($opcion->getId(), $opciones_arr))
            {
                $opciones_arr[] = $opcion->getId();
            }
            $preguntas_str = implode(",", $preguntas_arr);
            $opciones_str = implode(",", $opciones_arr);
            $pregunta_asociacion->setPregunta($pregunta_padre);
            $pregunta_asociacion->setPreguntas($preguntas_str);
            $pregunta_asociacion->setOpciones($opciones_str);
            $em->persist($pregunta_asociacion);
            $em->flush();

        }

        // PreguntaOpcion
        $pregunta_opcion->setPregunta($es_asociacion ? $pregunta : $pregunta_padre);
        $pregunta_opcion->setOpcion($opcion);
        if ($correcta && $pregunta_padre->getTipoPregunta()->getId() == $pregunta_simple)
        {
            $pos = $em->getRepository('LinkComunBundle:CertiPreguntaOpcion')->findByPregunta($pregunta_padre->getId());
            foreach ($pos as $po)
            {
                $po->setCorrecta(false);
                $em->persist($po);
                $em->flush();
            }
        }
        $pregunta_opcion->setCorrecta(!$es_asociacion ? $correcta ? true : false : true);
        $em->persist($pregunta_opcion);
        $em->flush();

        // HTML
        $html = $pregunta_opcion_id ? '' : '<tr id="tr-'.$pregunta_opcion->getId().'">';
        if (!$es_asociacion)
        {

            $query = $em->createQuery('SELECT COUNT(r.id) FROM LinkComunBundle:CertiRespuesta r
                                        WHERE r.opcion = :opcion_id')
                        ->setParameter('opcion_id', $pregunta_opcion->getOpcion()->getId());
            $hay_respuesta = $query->getSingleScalarResult();
            if ($hay_respuesta)
            {
                $delete_disabled = 'disabled';
                $class_delete = '';
            }
            else {
                $delete_disabled = '';
                $class_delete = 'delete';
            }

            $checked = $pregunta_opcion->getCorrecta() ? ' checked' : '';

            $html .= '<td>'.$pregunta_opcion->getOpcion()->getDescripcion().'</td>';
            if ($pregunta_padre->getTipoElemento()->getId() == $tipo_elemento_imagen)
            {
                $img = $pregunta_opcion->getOpcion()->getImagen() ? '<img src="'.$uploads.$pregunta_opcion->getOpcion()->getImagen().'" alt="" class="img__opc">' : '';
                $html .= '<td>'.$img.'</td>';
            }
            $html .= '<td class="center">
                        <div class="can-toggle demo-rebrand-2 small">
                            <input id="f'.$pregunta_opcion->getId().'" class="cb_activo" type="checkbox"'.$checked.'>
                            <label for="f'.$pregunta_opcion->getId().'">
                                <div class="can-toggle__switch" data-checked="'.$this->get('translator')->trans('Si').'" data-unchecked="No"></div>
                            </label>
                        </div>
                    </td>
                    <td class="center">
                        <a href="#" title="'.$this->get('translator')->trans('Editar').'" class="btn btn-link btn-sm edit" data-toggle="modal" data-target="#formModal" data="'.$pregunta_opcion->getId().'"><span class="fa fa-pencil"></span></a>
                        <a href="#" title="'.$this->get('translator')->trans('Eliminar').'" class="btn btn-link btn-sm '.$class_delete.' '.$delete_disabled.'" data="'.$pregunta_opcion->getId().'"><span class="fa fa-trash"></span></a>
                    </td>';

        }
        else {

            $query = $em->createQuery('SELECT COUNT(r.id) FROM LinkComunBundle:CertiRespuesta r
                                        WHERE r.opcion = :opcion_id')
                        ->setParameter('opcion_id', $pregunta_opcion->getOpcion()->getId());
            $hay_respuesta_opcion = $query->getSingleScalarResult();
            $query = $em->createQuery('SELECT COUNT(r.id) FROM LinkComunBundle:CertiRespuesta r
                                        WHERE r.pregunta = :pregunta_id')
                        ->setParameter('pregunta_id', $pregunta_opcion->getPregunta()->getId());
            $hay_respuesta_pregunta = $query->getSingleScalarResult();
            if ($hay_respuesta_opcion || $hay_respuesta_pregunta)
            {
                $delete_disabled = 'disabled';
                $class_delete = '';
            }
            else {
                $delete_disabled = '';
                $class_delete = 'delete';
            }

            if ($pregunta_padre->getTipoElemento()->getId() == $tipo_elemento_imagen)
            {
                $img_pregunta = $pregunta_opcion->getPregunta()->getImagen() ? '<img src="'.$uploads.$pregunta_opcion->getPregunta()->getImagen().'" alt="" class="img__opc">' : '';
                $img_opcion = $pregunta_opcion->getOpcion()->getImagen() ? '<img src="'.$uploads.$pregunta_opcion->getOpcion()->getImagen().'" alt="" class="img__opc">' : '';
            }
            else {
                $img_pregunta = '';
                $img_opcion = '';
            }

            $html .= '<td>
                            <div class="row">
                                <div class="col-md-4 col-sm-4 col-lg-4 col-xl-4 offset-xl-1 offset-lg-1 offset-md-1 offset-sm-1">'.$pregunta_opcion->getPregunta()->getEnunciado().'</div>
                                <div class="col-md-4 col-sm-4 col-lg-4 col-xl-4">'.$img_pregunta.'</div>
                            </div>
                        </td>
                        <td>
                            <div class="row">
                                <div class="col-md-4 col-sm-4 col-lg-4 col-xl-4 offset-xl-1 offset-lg-1 offset-md-1 offset-sm-1">'.$pregunta_opcion->getOpcion()->getDescripcion().'</div>
                                <div class="col-md-4 col-sm-4 col-lg-4 col-xl-4">'.$img_opcion.'</div>
                            </div>
                        </td>
                        <td class="center">
                            <a href="#" title="'.$this->get('translator')->trans('Editar').'" class="btn btn-link btn-sm edit" data-toggle="modal" data-target="#formModal" data="'.$pregunta_opcion->getId().'"><span class="fa fa-pencil"></span></a>
                            <a href="#" title="'.$this->get('translator')->trans('Eliminar').'" class="btn btn-link btn-sm '.$class_delete.' '.$delete_disabled.'" data="'.$pregunta_opcion->getId().'"><span class="fa fa-trash"></span></a>
                        </td>';

        }

        $html .= $pregunta_opcion_id ? '' : '</tr>';

        $return = array('html' => $html,
                        'checked' => $checked,
                        'id' => $pregunta_opcion->getId());

        $return = json_encode($return);
        return new Response($return, 200, array('Content-Type' => 'application/json'));

    }

    public function ajaxDeleteOpcionAction(Request $request)
    {

        $em = $this->getDoctrine()->getManager();

        $pregunta_opcion_id = $request->request->get('id');
        $entity = $request->request->get('entity');

        $ok = 1;
        $values = Yaml::parse(file_get_contents($this->get('kernel')->getRootDir().'/config/parametros.yml'));
        $pregunta_asociacion_id = $values['parameters']['tipo_pregunta']['asociacion'];

        $pregunta_opcion = $em->getRepository('LinkComunBundle:'.$entity)->find($pregunta_opcion_id);
        $opcion = $pregunta_opcion->getOpcion();
        $pregunta = $pregunta_opcion->getPregunta();

        if ($pregunta->getTipoPregunta()->getId() == $pregunta_asociacion_id)
        {

            // PreguntaAsociacion
            $pregunta_asociacion = $em->getRepository('LinkComunBundle:CertiPreguntaAsociacion')->findOneByPregunta($pregunta->getPregunta()->getId());
            $preguntas_str = $pregunta_asociacion->getPreguntas();
            $opciones_str = $pregunta_asociacion->getOpciones();
            $preguntas_arr = explode(",", $preguntas_str);
            $opciones_arr = explode(",", $opciones_str);
            $preguntas_arr_new = array();
            $opciones_arr_new = array();
            foreach ($preguntas_arr as $p_arr)
            {
                if ($p_arr != $pregunta->getId())
                {
                    $preguntas_arr_new[] = $p_arr;
                }
            }
            foreach ($opciones_arr as $o_arr)
            {
                if ($o_arr != $opcion->getId())
                {
                    $opciones_arr_new[] = $o_arr;
                }
            }
            if (!count($preguntas_arr_new) && !count($opciones_arr_new))
            {
                $em->remove($pregunta_asociacion);
                $em->flush();
            }
            else {
                $preguntas_str = implode(",", $preguntas_arr_new);
                $opciones_str = implode(",", $opciones_arr_new);
                $pregunta_asociacion->setPreguntas($preguntas_str);
                $pregunta_asociacion->setOpciones($opciones_str);
                $em->persist($pregunta_asociacion);
                $em->flush();
            }

        }

        // PreguntaOpcion
        $em->remove($pregunta_opcion);
        $em->flush();

        // Pregunta
        if ($pregunta->getTipoPregunta()->getId() == $pregunta_asociacion_id)
        {
            $em->remove($pregunta);
            $em->flush();
        }

        // Opcion
        $em->remove($opcion);
        $em->flush();

        $return = array('ok' => $ok);

        $return = json_encode($return);
        return new Response($return,200,array('Content-Type' => 'application/json'));

    }

    public function preguntasAction($prueba_id)
    {

        $session = new Session();
        $f = $this->get('funciones');

        if (!$session->get('ini') || $f->sesionBloqueda($session->get('sesion_id')))
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

        $values = Yaml::parse(file_get_contents($this->get('kernel')->getRootDir().'/config/parametros.yml'));
        $pregunta_asociacion = $values['parameters']['tipo_pregunta']['asociacion'];

        $em = $this->getDoctrine()->getManager();
        $prueba = $em->getRepository('LinkComunBundle:CertiPrueba')->find($prueba_id);

        $query = $em->createQuery("SELECT p FROM LinkComunBundle:CertiPregunta p
                                    WHERE p.prueba = :prueba_id AND p.pregunta IS NULL
                                    ORDER BY p.orden ASC")
                    ->setParameter('prueba_id', $prueba_id);
        $preguntas_bd = $query->getResult();

        $preguntas = array();

        foreach ($preguntas_bd as $p)
        {

            $opciones = array();
            $correcta = 0;

            if ($p->getTipoPregunta()->getid() != $pregunta_asociacion)
            {
                $query = $em->createQuery("SELECT po, o FROM LinkComunBundle:CertiPreguntaOpcion po
                                            JOIN po.opcion o
                                            WHERE po.pregunta = :pregunta_id AND o.prueba = :prueba_id
                                            ORDER BY o.id ASC")
                            ->setParameters(array('pregunta_id' => $p->getId(),
                                                  'prueba_id' => $prueba->getId()));
                $pos = $query->getResult();
                foreach ($pos as $po)
                {
                    $correcta_str = $po->getCorrecta() ? ' (Correcta)' : '';
                    $opciones[] = $po->getOpcion()->getDescripcion().$correcta_str;
                    if ($po->getCorrecta())
                    {
                        $correcta++;
                    }
                }
            }
            else {

                $correcta++;
                $asociacion = $em->getRepository('LinkComunBundle:CertiPreguntaAsociacion')->findOneByPregunta($p->getId());

                if ($asociacion)
                {

                    $preguntas_asociadas = explode(",", $asociacion->getPreguntas());

                    $query = $em->createQuery("SELECT po, o, p FROM LinkComunBundle:CertiPreguntaOpcion po
                                                JOIN po.opcion o JOIN po.pregunta p
                                                WHERE po.pregunta IN (:preguntas_id)
                                                AND o.prueba = :prueba_id
                                                AND p.pregunta = :pregunta_id
                                                ORDER BY po.id ASC")
                                ->setParameters(array('preguntas_id' => $preguntas_asociadas,
                                                      'prueba_id' => $prueba->getId(),
                                                      'pregunta_id' => $p->getId()));
                    $pos = $query->getResult();

                    foreach ($pos as $po)
                    {
                        $opciones[] = $po->getPregunta()->getEnunciado().' --> '.$po->getOpcion()->getDescripcion();
                    }

                }

            }

            $preguntas[] = array('id' => $p->getId(),
                                 'enunciado' => $p->getEnunciado(),
                                 'tipo' => $p->getTipoPregunta()->getNombre(),
                                 'opciones' => $opciones,
                                 'status' => $p->getEstatusContenido()->getNombre(),
                                 'modificacion' => $p->getFechaModificacion()->format('d/m/Y H:i a'),
                                 'orden' => $p->getOrden(),
                                 'correcta' => $correcta,
                                 'delete_disabled' => $f->linkEliminar($p->getId(), 'CertiPregunta'));

        }

        return $this->render('LinkBackendBundle:Evaluacion:preguntas.html.twig', array('prueba' => $prueba,
                                                                                       'preguntas' => $preguntas));

    }

}
