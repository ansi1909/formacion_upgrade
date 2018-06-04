<?php

namespace Link\BackendBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Link\ComunBundle\Entity\AdminSesion;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\Yaml\Yaml;

class DefaultController extends Controller
{
    public function indexAction()
    {

        $em = $this->getDoctrine()->getManager();
        $session = new Session();
        $f = $this->get('funciones');

        if (!$session->get('ini') || $f->sesionBloqueda($session->get('sesion_id')))
      	{
        	return $this->redirectToRoute('_loginAdmin');
      	}
        $f->setRequest($session->get('sesion_id'));

      	if ($this->container->get('session')->isStarted())
      	{
        	
        	if($session->get('administrador') == 'true')
            {
                $empresas_db = $em->getRepository('LinkComunBundle:AdminEmpresa')->findAll();
                $empresasA=array();
                $empresasI=array();
                $empresas_a=0;
                $empresas_i=0;
                
                foreach($empresas_db as $empresa)
                {
                    
                    if($empresa->getActivo() == 'true') 
                    {
                        $tieneA = 0;
                        $paginasA= "";
                        $empresas_a=$empresas_a+1;

                        $query = $em->createQuery('SELECT COUNT(u.id) FROM LinkComunBundle:AdminUsuario u 
                                                   WHERE u.empresa = :empresa_id')
                                    ->setParameter('empresa_id', $empresa->getId());
                        $usuarios = $query->getSingleScalarResult();

                        $query = $em->createQuery('SELECT pe FROM LinkComunBundle:CertiPaginaEmpresa pe
                                                   JOIN pe.pagina p
                                                   WHERE pe.empresa = :empresa_id
                                                   AND p.pagina IS NULL')
                                    ->setParameter('empresa_id', $empresa->getId());
                        $paginas_db = $query->getResult();

                        foreach( $paginas_db as $pagina )
                        {
                            $tieneA++;
                            $paginasA .= '<li data-jstree=\'{ "icon": "fa fa-angle-double-right" }\' p_id="'.$pagina->getPagina()->getId().'" p_str="'.$pagina->getPagina()->getCategoria()->getNombre().': '.$pagina->getPagina()->getNombre().'">'.$pagina->getPagina()->getCategoria()->getNombre().': '.$pagina->getPagina()->getNombre();
                            $subpaginas = $f->subPaginasEmpresa($pagina->getPagina()->getId(), $empresa->getId());     
                            if ($subpaginas['tiene'] > 0)
                            {
                                $paginasA .= '<ul>';
                                $paginasA .= $subpaginas['return'];
                                $paginasA .= '</ul>';
                            }
                            $paginasA .= '</li>'; 
                        }

                        $empresasA[]=array('nombre'=> $empresa->getNombre(),
                                           'usuarios'=>$usuarios,
                                           'programas'=>$paginasA,
                                           'tiene'=>$tieneA);                        
                    }
                    else
                    {
                        $empresas_i=$empresas_i+1;
                        $tieneI=0;
                        $paginasI="";
                        $query = $em->createQuery('SELECT COUNT(u.id) FROM LinkComunBundle:AdminUsuario u 
                                                   WHERE u.empresa = :empresa_id')
                                    ->setParameter('empresa_id', $empresa->getId());
                        $usuarios = $query->getSingleScalarResult();

                        $query = $em->createQuery('SELECT pe FROM LinkComunBundle:CertiPaginaEmpresa pe
                                                   JOIN pe.pagina p
                                                   WHERE pe.empresa = :empresa_id
                                                   AND p.pagina IS NULL')
                                    ->setParameter('empresa_id', $empresa->getId());
                        $paginas_db = $query->getResult();

                        foreach( $paginas_db as $pagina )
                        {
                            $paginasI .= '<li data-jstree=\'{ "icon": "fa fa-angle-double-right" }\' p_id="'.$pagina->getPagina()->getId().'" p_str="'.$pagina->getPagina()->getCategoria()->getNombre().': '.$pagina->getPagina()->getNombre().'">'.$pagina->getPagina()->getCategoria()->getNombre().': '.$pagina->getPagina()->getNombre();
                            $subpaginas = $f->subPaginasEmpresa($pagina->getPagina()->getId(), $empresa->getId());     
                            if ($subpaginas['tiene'] > 0)
                            {
                                $paginasI .= '<ul>';
                                $paginasI .= $subpaginas['return'];
                                $paginasI .= '</ul>';
                            }
                            $paginasI .= '</li>';
                            $tieneI++;
                        }

                        $empresasI[]=array('nombre'=> $empresa->getNombre(),
                                           'usuarios'=>$usuarios,
                                           'programas'=>$paginasI,
                                           'tiene'=>$tieneI);
                    }
                }


                $response = $this->render('LinkBackendBundle:Default:index.html.twig', array('empresas'=>$empresas_a + $empresas_i,
                                                                                             'activas'=>$empresas_a,
                                                                                             'inactivas'=>$empresas_i,
                                                                                             'empresasA'=>$empresasA,
                                                                                             'empresasI'=>$empresasI));

                return $response;

            }
            else
            {

                $usuarioS = $em->getRepository('LinkComunBundle:AdminUsuario')->find($session->get('usuario')['id']);

                $query = $em->createQuery('SELECT pe FROM LinkComunBundle:CertiPaginaEmpresa pe
                                           JOIN pe.pagina p
                                           WHERE pe.empresa = :empresa_id
                                           AND p.pagina IS NULL')
                            ->setParameter('empresa_id', $usuarioS->getEmpresa()->getId());
                $paginas_db = $query->getResult();

                $query = $em->createQuery('SELECT u FROM LinkComunBundle:AdminUsuario u
                                           WHERE u.empresa = :empresa_id')
                            ->setParameter('empresa_id', $usuarioS->getEmpresa()->getId());
                $usuarios_db = $query->getResult();

                $usuariosA = 0;
                $usuariosI = 0;

                foreach ($usuarios_db as $usuario)
                {
                    if ($usuario->getActivo() == 'true') 
                    {
                        $usuariosA++;
                    }
                    else
                    {
                        $usuariosI++;
                    }
                }

                $paginas = array();
                
                foreach($paginas_db as $pagina)
                {
                    $usuariosT =0;
                    $usuariosCur =0;
                    $usuariosF =0;
                    $usuariosN =0;
            
                    $query = $em->createQuery('SELECT np FROM LinkComunBundle:CertiNivelPagina np
                                               WHERE np.paginaEmpresa = :pe_id')
                                ->setParameter('pe_id', $pagina->getId());
                    $nivel_pagina = $query->getResult();

                    foreach ($nivel_pagina as $np)
                    {
                        $nivel_id = $np->getNivel()->getId();
                        $pagina_id = $np->getPaginaEmpresa()->getPagina()->getId();

                        $query = $em->createQuery('SELECT COUNT(u.id) FROM LinkComunBundle:AdminUsuario u 
                                                   WHERE u.nivel = :nivel_id')
                                    ->setParameter('nivel_id', $nivel_id);
                        $usuarios = $query->getSingleScalarResult();
                      
                        $usuariosT = $usuariosT + $usuarios;
                      

                        $query = $em->createQuery('SELECT COUNT(u.id) FROM LinkComunBundle:CertiPaginaLog pl
                                                   JOIN pl.usuario u
                                                   WHERE u.nivel = :nivel_id
                                                   AND pl.pagina = :pagina_id
                                                   AND pl.estatusPagina IN ( 1 , 2 )')
                                    ->setParameters(array('nivel_id'=> $nivel_id,
                                                          'pagina_id'=> $pagina_id));

                        $cursando = $query->getSingleScalarResult();

                        $usuariosCur=$usuariosCur+$cursando;
                       

                        $query = $em->createQuery('SELECT COUNT(u.id) FROM LinkComunBundle:CertiPaginaLog pl
                                                   JOIN pl.usuario u
                                                   WHERE u.nivel = :nivel_id
                                                   AND pl.pagina = :pagina_id
                                                   AND pl.estatusPagina = :culminado')
                                    ->setParameters(array('nivel_id'=> $nivel_id,
                                                          'pagina_id'=> $pagina_id,
                                                          'culminado'=> 3));

                        $culminado = $query->getSingleScalarResult();

                        $usuariosF = $usuariosF + $culminado;
                       
                    }

                        $usuariosN = $usuariosT - ($usuariosCur + $usuariosF);

                    //return new Response (var_dump($usuariosCur));
                    
                    $paginas[] = array('pagina'=>$pagina->getPagina()->getNombre(),
                                       'usuariosT'=>$usuariosT,
                                       'usuariosCur'=>$usuariosCur,
                                       'usuariosF'=>$usuariosF,
                                       'usuariosN'=>$usuariosN);
                }

                $response = $this->render('LinkBackendBundle:Default:index.html.twig', array('activos'=> $usuariosA,
                                                                                             'inactivos'=> $usuariosI,
                                                                                             'total'=> $usuariosA + $usuariosI,
                                                                                             'paginas'=>$paginas));

                return $response;
            }

      	}
      	else {
      		return $this->redirectToRoute('_loginAdmin');
      	}

    }

    public function authExceptionAction()
    {
        return $this->render('LinkBackendBundle:Default:authException.html.twig');
    }

    protected function deleteFilesTutorial($tutorial_id)
    {
       $yml=Yaml::parse(file_get_contents($this->get('kernel')->getRootDir().'/config/parameters.yml')); 
       $directorio=$yml['parameters']['folders']['dir_uploads'].'recursos/tutoriales/'.$tutorial_id;
       $archivos=scandir($directorio);

       for ($i=2; $i <count($archivos); $i++) 
       { 
          
          unlink($directorio.'/'.$archivos[$i]);
       }

       rmdir($directorio);
       return true;
    }

    public function ajaxDeleteAction(Request $request)
    {

        $em = $this->getDoctrine()->getManager();

        $id = $request->request->get('id');
        $entity = $request->request->get('entity');

        $ok = 1;

        $object = $em->getRepository('LinkComunBundle:'.$entity)->find($id);
        $em->remove($object);
        $em->flush();

        if ($entity=='AdminTutorial') 
        {
            $this->deleteFilesTutorial($id);
        }

        $return = array('ok' => $ok);

        $return = json_encode($return);
        return new Response($return,200,array('Content-Type' => 'application/json'));

    }

    public function ajaxActiveAction(Request $request)
    {
        
        $em = $this->getDoctrine()->getManager();
        
        $id = $request->request->get('id');
        $entity = $request->request->get('entity');
        $checked = $request->request->get('checked');

        $object = $em->getRepository('LinkComunBundle:'.$entity)->find($id);
        $object->setActivo($checked ? true : false);
        $em->persist($object);
        $em->flush();
                    
        $return = array('id' => $object->getId());

        $return = json_encode($return);
        return new Response($return, 200, array('Content-Type' => 'application/json'));
        
    }

    public function ajaxOrderAction(Request $request)
    {
        
        $em = $this->getDoctrine()->getManager();
        
        $id = $request->request->get('id');
        $entity = $request->request->get('entity');
        $orden = $request->request->get('orden');

        $object = $em->getRepository('LinkComunBundle:'.$entity)->find($id);
        $object->setOrden($orden);
        $em->persist($object);
        $em->flush();
                    
        $return = array('id' => $object->getId());

        $return = json_encode($return);
        return new Response($return, 200, array('Content-Type' => 'application/json'));
        
    }

    public function loginAction(Request $request)
    {

        $f = $this->get('funciones');
        $error = '';
        $yml = Yaml::parse(file_get_contents($this->get('kernel')->getRootDir().'/config/parametros.yml'));

        $verificacion='';
        $em = $this->getDoctrine()->getManager();

        //validamos que exista una cookie
        if($_COOKIE && isset($_COOKIE["id_usuario"]))
        {
            $usuario = $em->getRepository('LinkComunBundle:AdminUsuario')->findOneBy(array('id' => $_COOKIE["id_usuario"],
                                                                                           'cookies' => $_COOKIE["marca_aleatoria_usuario"] ) );
            
            if($usuario)
            {
                $recordar_datos=1;
                $login = $usuario->getLogin();
                $clave = $usuario->getClave(); 
                $verificacion=1;
            }
            else {
                $error = $this->get('translator')->trans('La información almacenada en el navegador no es correcta, borre el historial.');
            }
        }
        else {
            if ($request->getMethod() == 'POST')
            {
                $recordar_datos = $request->request->get('recordar');
                $login = $request->request->get('usuario');
                $clave = $request->request->get('clave');
                $verificacion=1;
            }
        }

        if($verificacion)
        {
            $iniciarSesion = $f->iniciarSesionAdmin(array('recordar_datos' => $recordar_datos,'login' => $login,'clave' => $clave,'yml' => $yml['parameters'] ));

            if($iniciarSesion['exito']==true)
            {
                return $this->redirectToRoute('_inicioAdmin');
            }
            else {
                if($iniciarSesion['error']==true)
                {
                    $response = $this->render('LinkBackendBundle:Default:login.html.twig', array('error' => $iniciarSesion['error'] )); 
                    return $response;
                }
            }                    
        }
        else {
            $response = $this->render('LinkBackendBundle:Default:login.html.twig', array('error' => $error )); 
            return $response;
        }
            
    }

    public function ajaxQRAction(Request $request)
    {

        $nombre = $request->request->get('nombre');
        $contenido = $request->request->get('contenido');
        $size = $request->request->get('size');

        $nombre = $nombre.'.png';

        \PHPQRCode\QRcode::png($contenido, "../qr/".$nombre, 'H', $size, 4);

        $ruta ='<img src="/formacion2.0/qr/'.$nombre.'">';
        
        $return = array('ruta' =>$ruta);
        $return = json_encode($return);
        return new Response($return, 200, array('Content-Type' => 'application/json'));
    }

    public function reordenarAsignacionAction($empresa_id, Request $request)
    {

        $f = $this->get('funciones');
        $em = $this->getDoctrine()->getManager();
        $orden = 0;
        $paginas_ordenadas = array();

        $empresa = $this->getDoctrine()->getRepository('LinkComunBundle:AdminEmpresa')->find($empresa_id);

        $query = $em->createQuery('SELECT pe FROM LinkComunBundle:CertiPaginaEmpresa pe
                                   JOIN pe.pagina p
                                   WHERE pe.empresa = :empresa_id 
                                    AND p.pagina IS NULL 
                                    ORDER BY p.orden')
                    ->setParameter('empresa_id', $empresa_id);
        $pes = $query->getResult();

        foreach ($pes as $pe)
        {

            $orden++;
            $pe->setOrden($orden);
            $em->persist($pe);
            $em->flush();

            $paginas_ordenadas[] = array('orden' => $orden,
                                         'pagina_id' => $pe->getPagina()->getId(),
                                         'pagina_empresa_id' => $pe->getId(),
                                         'nombre' => $pe->getPagina()->getCategoria()->getNombre().' '.$pe->getPagina()->getNombre(),
                                         'subpaginas' => $f->reordenarSubAsignaciones($pe));

        }

        return new Response('<p><b>Páginas asignadas a la empresa '.$empresa->getNombre().':</b></p>'.var_dump($paginas_ordenadas));

    }

}
