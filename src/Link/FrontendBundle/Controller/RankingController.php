<?php

namespace Link\FrontendBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Yaml\Yaml;
use Link\ComunBundle\Entity\AdminSesion;
use Symfony\Component\HttpFoundation\Cookie;
use Link\ComunBundle\Entity\CertiPaginaLog;

class RankingController extends Controller
{

    public function indexAction()
    {

        $em = $this->getDoctrine()->getManager();
        $f = $this->get('funciones');
        $session = new Session();
        $yml = Yaml::parse(file_get_contents($this->get('kernel')->getRootDir().'/config/parametros.yml'));

        if (!$session->get('iniFront') || $f->sesionBloqueda($session->get('sesion_id')))
        {
            return $this->redirectToRoute('_authExceptionEmpresa', array('tipo' => 'sesion'));
        }
        $f->setRequest($session->get('sesion_id'));

        $baseUrl = $this->get('kernel')->getRootDir();

        // Convertimos los id de las paginas de la sesion en un nuevo array
        $paginas_int_ids = array();
        foreach ($session->get('paginas') as $pg) {
            $paginas_int_ids[] = $pg['id'];
        }

        // Buscamos los programas con interaccion por parte del usuario de sus programas asignados
        $query_programas_iterac = $em->createQuery('SELECT cp
                                                    FROM LinkComunBundle:CertiPagina cp, 
                                                         LinkComunBundle:CertiPaginaLog cpl
                                                    WHERE cp.id IN (:pagina)
                                                    AND cpl.pagina = cp.id
                                                    AND cpl.usuario = :usuario
                                                    ORDER BY cp.id ASC')
                                      ->setParameters(array('usuario' => $session->get('usuario')['id'],
                                                            'pagina' => $paginas_int_ids));
        $paginas_sesion = $query_programas_iterac->getResult();

        // convertimos en un nuevo array las paginas con las que el usuario ha tenido interaccion
        $paginas_ids = array();
        foreach ($paginas_sesion as $pg) {
            $paginas_ids[] = $pg->getId();
        }

        // Buscamos el cuadro de honor solo de los programas en los que el usuario tine registros
        $ranking = array();
        $orden = 0;
        foreach ($paginas_ids as $pid) {
            $pagina_obj = $this->getDoctrine()->getRepository('LinkComunBundle:CertiPagina')->find($pid);
            $usuarios = array();
            $query_programas_iterac = $em->createQuery('SELECT cpl
                                                    FROM LinkComunBundle:CertiPaginaLog cpl
                                                    WHERE cpl.pagina = :pagina
                                                    ORDER BY cpl.puntos DESC')
                              ->setParameters(array('pagina' => $pid));
            $paginaslog = $query_programas_iterac->getResult();

            foreach ($paginaslog as $plog) {
                $orden = $orden + 1;
                if($plog->getUsuario()->getId() == $session->get('usuario')['id'] and $orden <= 10){
                    $class = 'me-i';
                    $topten = 1;
                    $meorder = '';
                    $shiney = '<div class="starShiney"></div>';
                }elseif($plog->getUsuario()->getId() == $session->get('usuario')['id'] and $orden > 10){
                    $class = '';
                    $meorder = '<tr>';
                    $meorder .= '<td>'.$orden.'</td>';
                    $meorder .= '<td><img class="up" src="'.$baseUrl.'/'.$plog->getUsuario()->getFoto().'" alt=""></td>';
                    $meorder .= '<td><span>'.$plog->getUsuario()->getNombre().' '.$plog->getUsuario()->getApellido().'</span></td>';
                    $meorder .= '<td>'.round($plog->getPorcentajeAvance()).'</td>';
                    $meorder .= '<td>';
                    $meorder .= '<div class="img-coin">';
                    $meorder .= '<div class="starShiney"></div>';
                    $meorder .= '<img src="'.$baseUrl.'/assets/img/coins.svg" alt="">';
                    $meorder .= '<span class="text-coin">'.$plog->getPuntos().'K</span>';
                    $meorder .= '</div>';
                    $meorder .= '</td>';
                    $meorder .= '</tr>';
                    $topten = 0;
                    $shiney = '';
                }else{
                    $class = '';
                    $topten = 0;
                    $meorder = '';
                    $shiney = '';
                }
                $usuarios[]= array('orden'=>$orden,
                                   'nombre'=>$plog->getUsuario()->getNombre(),
                                   'apellido'=>$plog->getUsuario()->getApellido(),
                                   'foto'=>$plog->getUsuario()->getFoto(),
                                   'calificacion'=>round($plog->getPorcentajeAvance()),
                                   'experiencia'=>$plog->getPuntos(),
                                   'class'=>$class,
                                   'meorder'=>$meorder,
                                   'shiney'=>$shiney,
                                   'topten'=>$topten);
                
            }

            $ranking[]= array('pagina'=>$pagina_obj->getNombre(),
                              'usuarios'=>$usuarios);

            
            $orden = 0;
            $usuarios = array();
        }

        return $this->render('LinkFrontendBundle:Ranking:index.html.twig', array('ranking' =>$ranking));


        $response->headers->setCookie(new Cookie('Peter', 'Griffina', time() + 36, '/'));

        return $response; 

    }

 
    public function obtenerPuntosUsuarioLogueado($indices){
        $em = $this->getDoctrine()->getManager();
        $session = new Session();

        #obtenemos los puntos que el usuario logueado recaudo en el programa 
        $query = $em->createQuery('SELECT SUM(cpl.puntos) 
                                                    FROM LinkComunBundle:CertiPaginaLog cpl
                                                    WHERE cpl.pagina IN (:paginas)
                                                    AND cpl.usuario = :usuario')
                                            ->setParameters(array('usuario' => $session->get('usuario')['id'],
                                                            'paginas' => $indices));
        $puntos_usuario_logueado = $query->getSingleScalarResult();
        
        if(!$puntos_usuario_logueado){
            $puntos_usuario_logueado = 0;
        }
                                                            
        

        return $puntos_usuario_logueado;

    }


    public function obtenerEstructuraPrograma($ppagina_id){
        #retorna la estructura del programa a consultar
        $em = $this->getDoctrine()->getManager();
        $session = new Session();
        $indices = array();


        $query = $em->getConnection()->prepare('SELECT
                                                fnobtener_estructura(:ppagina_id) as
                                                resultado;');
        $query->bindValue(':ppagina_id', $ppagina_id, \PDO::PARAM_INT);
        $query->execute();

        $rs = $query->fetchAll();
        $resultado = json_decode($rs[0]['resultado'],true);
        foreach($resultado as $clave  => $array ){
            if($clave == 'padre'){
                array_push($indices,$array['id']);
            }else{
                foreach($array as $hijo){
                    array_push($indices,$hijo['id']);
                }
            }
        }

        return $indices;
    }
    

    public function obtenerListadoLiga($ppagina_id, $liga_pts_min, $liga_pts_max, $estructura){
            $em = $this->getDoctrine()->getManager();
            $session = new Session();

            $query = $em->getConnection()->prepare('SELECT fnobtener_liga(:ppagina_id,:pempresa_id,:ppuntaje_min,:ppuntaje_max,:pestructura) as resultado;');
            $query->bindValue(':ppagina_id', $ppagina_id, \PDO::PARAM_INT);
            $query->bindValue(':pempresa_id', $session->get('empresa')['id'], \PDO::PARAM_INT);
            $query->bindValue(':ppuntaje_min', $liga_pts_min, \PDO::PARAM_INT);
            $query->bindValue(':ppuntaje_max', $liga_pts_max, \PDO::PARAM_INT);
            $query->bindValue(':pestructura', $estructura, \PDO::PARAM_STR);
            $query->execute();
            $rs = $query->fetchAll(); 
            $resultado = $rs[0]['resultado'];
            $resultado = substr($resultado, 0,-2);
            $resultado = $resultado.'}';
          
            $resultado = json_decode($resultado,true);
            return $resultado;
    }
    

    public function newAction(){
        #listar programas
       
        $em = $this->getDoctrine()->getManager();
        $session = new Session();
        $programas = $session->get('paginas');

        return $this->render('LinkFrontendBundle:Ranking:new.html.twig',array('programas'=>$programas));
        $response->headers->setCookie(new Cookie('Peter', 'Griffina', time() + 36, '/'));

        return $response; 
    }

    public function ajaxRankingAction(Request $request){
        $em = $this->getDoctrine()->getManager();
        $session = new Session();
        $uploads = $this->container->getParameter('folders')['uploads'];
        $baseUrl = $this->container->getParameter('base_url');
        $ppagina_id = $request->request->get('pagina_id');
        $pempresa_id = $session->get('empresa')['id'];
        $registros_pagina = 15;
        $usuario_id = $session->get('usuario')['id'];
        $response = array();
        $ligas_array = array();
        $liga_actual = array();

        $estructura = $this->obtenerEstructuraPrograma($ppagina_id);
        
        $puntos_usuario_logueado = $this->obtenerPuntosUsuarioLogueado($estructura);
        
        $programa = $em->getRepository('LinkComunBundle:CertiPagina')->findOneById($ppagina_id);
        
        if ($programa->getPuntuacion() ){
            $porcentaje_puntos_totales = round(($puntos_usuario_logueado * 100) / $programa->getPuntuacion());
            #obtener ligas
            $ligas = $em->createQuery('SELECT l FROM LinkComunBundle:AdminLigas l');
            $ligas = $ligas->getResult();

            foreach ($ligas as $liga) {
                $liga_pts_min = ($liga->getPorcentajemin() * $programa->getPuntuacion())/100; 
                $liga_pts_max = ($liga->getPorcentajemax() * $programa->getPuntuacion())/100;
                $liga_aux =[
                    "nombre"       =>  $liga->getNombre(),
                    "descripcion"  =>  $liga->getDescripcion(),
                    "puntos_min"   =>  round($liga_pts_min),
                    "puntos_max"   =>  round($liga_pts_max),
                    "imagen"       =>  $uploads.'recursos/ligas/'.$liga->getId().'/'.$liga->getImagen(),
                    "lograda"      =>  ($puntos_usuario_logueado >= $liga_pts_min )? 1 : 0
                ];
                $ligas_array[$liga->getId()] = $liga_aux;

                if($porcentaje_puntos_totales >= $liga->getPorcentajemin() && $porcentaje_puntos_totales <= $liga->getPorcentajemax()){
                    $actual_id = $liga->getId();
                    $liga_actual[$actual_id] = $liga_aux;
                }
            }



            #preparar estructura para pasar como argumento a la funcion BD
            $estructura = json_encode($estructura);
            $estructura = str_replace("[","{",$estructura);
            $estructura = str_replace("]","}",$estructura);

            #obtener proxima liga 
            $ligas_keys = array_keys($ligas_array);
            $proxima_liga = array_search($actual_id+1,$ligas_keys);
            $proxima_liga = ($proxima_liga)? $ligas_array[$ligas_keys[$proxima_liga]] : false;
            

            #obtener listado de usuarios que pertenecen a la liga
            $resultado = $this->obtenerListadoLiga($ppagina_id, $liga_actual[$actual_id]['puntos_min'], $liga_actual[$actual_id]['puntos_max'], $estructura);
            
            $total = count($resultado);
            $keys = array_keys($resultado);


            
            if( $total > 0 &&  $total <= $registros_pagina){
                #si la cantidad de participantes es igual o menor a la cantidad de registros por pagina
                $index_inicio = 0;
                $index_fin = $total - 1;
                $registros_pagina = $total;
            }elseif($total > $registros_pagina){
                #Si hay mas del limite de registro por pagina para la liga
                $logueadoIndex = array_search($usuario_id,$keys);
                if ($logueadoIndex >= 0 && $logueadoIndex <= 9){
                    #Si el usuario logueado esta en los primeros 10
                    $index_inicio = 0;
                    $index_fin    = $registros_pagina - 1;
                }
                elseif(($logueadoIndex > 10) && ($logueadoIndex < (count($keys)-1))){
                    #si el usuario logueado esta situado despues de los primeros diez pero no es ultimo
                    $index_inicio = $logueadoIndex;
                    $index_fin = $logueadoIndex;
                    $incluidos = 1;
                    $continue = true;
                    while($continue){
                        $continue = false;
                        if(($incluidos+1) <= $registros_pagina){
                            if(($index_inicio - 1) > 0){
                                $index_inicio = $index_inicio - 1;
                                $incluidos++;
                                $continue = true;
                            }
                        }

                        if(($incluidos+1) <= $registros_pagina){
                            if(($index_fin + 1) < count($keys)){
                                $index_fin = $index_fin + 1;
                                $incluidos++;
                                $continue = true;
                            }
                        }else{
                            $continue = false;
                        }
                    }
                }elseif($logueadoIndex == ($total-1)){
                        #Si el usuario logueado es ultimo
                        $index_inicio = $total - ($registros_pagina - 1);
                        $index_fin = $logueadoIndex;
                }

  
            }

                

            ##Preparar datos que se van a mostrar
            $keys = array_slice($keys,$index_inicio,$registros_pagina);
            foreach ($keys as $key ) {
                $response['.'.$key] = $resultado[$key];
            }
            $ok = 1;
        }else{
            $ok = 0;
        }
    
        $return = array(
                            'ok'=>$ok,
                            'list'=>$response,
                            'my_user'=>'.'.$usuario_id,
                            'total'=>$total,
                            'leagueName' => $liga_actual[$actual_id]['nombre'],
                            'leagues'   => $ligas_array,
                            'currentLeague' => $actual_id,
                            'puntosProximaLiga' => ($proxima_liga)? round($proxima_liga['puntos_min'] - $puntos_usuario_logueado ): 0 ,
                            'uploads' => $uploads,
                            'imgUser' => $baseUrl."/front/assets/img/user.svg",
                            'imgLeagueLocked' => $baseUrl."/front/assets/img/badge_locked.svg"
                        );
        $return = json_encode($return);
        return new Response($return, 200, array('Content-Type' => 'application/json'));
    }

    public function ajaxBotonRankingAction(Request $request){
        $em = $this->getDoctrine()->getManager();
        $ligas = $em->getRepository('LinkComunBundle:AdminLigas')->findAll();
        $return = array('ligas' => ( count($ligas) > 0 )? 1:0 );
        $return = json_encode($return);
        return new Response($return, 200, array('Content-Type' => 'application/json'));

    }

}