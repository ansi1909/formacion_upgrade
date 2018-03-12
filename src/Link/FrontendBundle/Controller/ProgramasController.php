<?php

namespace Link\FrontendBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Yaml\Yaml;
use Link\ComunBundle\Entity\AdminSesion;
use Symfony\Component\HttpFoundation\Cookie;

class ProgramasController extends Controller
{

    public function indexAction()
    {

        $em = $this->getDoctrine()->getManager();
        $f = $this->get('funciones');
        $session = new Session();

        if (!$session->get('iniFront'))
        {
            return $this->redirectToRoute('_authExceptionEmpresa', array('mensaje' => $this->get('translator')->trans('Lo sentimos. Sesión expirada.')));
        }
        $f->setRequest($session->get('sesion_id'));

        if ($this->container->get('session')->isStarted())
        {
            $yml = Yaml::parse(file_get_contents($this->get('kernel')->getRootDir().'/config/parametros.yml'));
            $datos = $session;

            $programas_disponibles = array();
            
            // Convertimos los id de las paginas de la sesion en un nuevo array
            $paginas_ids = array();
            foreach ($session->get('paginas') as $pg) {
                $paginas_ids[] = $pg['id'];
            }

            // Buscamos los grupos disponibles para el usuario por los programas disponibles en la sesión
            $group_query = $em->createQuery('SELECT cg FROM LinkComunBundle:CertiGrupo cg 
                                            JOIN LinkComunBundle:CertiGrupoPagina cgp
                                            WHERE cg.empresa = :empresa
                                            AND  cgp.grupo = cg.id
                                            AND cgp.pagina IN (:pagina)
                                            ORDER BY cg.id ASC')
                            ->setParameters(array('empresa' => $session->get('empresa')['id'],
                                                  'pagina' => $paginas_ids));
            $grupos = $group_query->getResult();

            // Buscamos datos especificos de los programas de la sesion obteniendo el grupo al que pertenece cada programa
            $query_pages_by_group = $em->createQuery('SELECT cg.nombre as nombregrupo, cp.id as paginaid, cp.descripcion as descripcion, cp.nombre as nombrepagina, cp.foto as foto 
                                     FROM LinkComunBundle:CertiGrupo cg,
                                          LinkComunBundle:CertiGrupoPagina cgp,
                                          LinkComunBundle:CertiPagina cp
                                    WHERE cg.empresa = :empresa
                                    AND  cgp.grupo = cg.id
                                    AND cp.id IN (:pagina)
                                    AND cgp.pagina = cp.id
                                    ORDER BY cg.id ASC')
                            ->setParameters(array('empresa' => $session->get('empresa')['id'],
                                                  'pagina' => $paginas_ids));
            $pages_by_group = $query_pages_by_group->getResult();
            
            foreach ($pages_by_group as $pg) {

                // contruimos un array con los datos necesarios para el template y el grupo de cada programa
                $pag_obj = $this->getDoctrine()->getRepository('LinkComunBundle:CertiPagina')->find($pg['paginaid']);


                $datos_certi_pagina = $this->getDoctrine()->getRepository('LinkComunBundle:CertiPaginaEmpresa')->findOneBy(array('empresa' => $session->get('empresa')['id'],
                                                                                                                                 'pagina' => $pg['paginaid']));

                $datos_log = $this->getDoctrine()->getRepository('LinkComunBundle:CertiPaginaLog')->findOneBy(array('usuario' => $session->get('usuario')['id'],
                                                                                                                    'pagina' => $pg['paginaid']));
                if($datos_log){
                    if($datos_log->getEstatusPagina()->getId() == $yml['parameters']['estatus_pagina']['completada']){
                        if($datos_certi_pagina->getAcceso()){
                            // aprobado y con acceso de seguir viendo
                            $continuar = 2;
                        }else{
                            // aprobado y sin poder ver solo descargar notas y certificados
                            $continuar = 3;
                        }
                    }else{
                       // cursando actualemnete el progarama - continuar
                       $continuar = 1; 
                    }
                    
                }else{
                    // si registros - iniciar
                    $continuar = 0;
                }
                if($pg['foto']){
                    $imagen = $pg['foto'];
                }else{
                    $imagen = 'http://localhost/formacion2.0/web/front/assets/img/liderazgo.png';
                }
               
                $programas_disponibles[]= array('id'=>$pg['paginaid'],
                                                'nombre'=>$pg['nombrepagina'],
                                                'nombregrupo'=>$pg['nombregrupo'],
                                                'imagen'=>$imagen,
                                                'descripcion'=>$pag_obj->getDescripcion(),
                                                'fecha_vencimiento'=>$f->timeAgo($datos_certi_pagina->getFechaVencimiento()->format("Y/m/d")),
                                                'continuar'=>$continuar);
                
            }
        }
        else {
            return $this->redirectToRoute('_login');
        }

        return $this->render('LinkFrontendBundle:Programas:index.html.twig', array('grupos' => $grupos,
                                                                                 'programas_disponibles' => $programas_disponibles));

        $response->headers->setCookie(new Cookie('Peter', 'Griffina', time() + 36, '/'));

        return $response;        
    }

    

}