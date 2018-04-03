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

        if (!$session->get('iniFront'))
        {
            return $this->redirectToRoute('_authExceptionEmpresa', array('mensaje' => $this->get('translator')->trans('Lo sentimos. Sesión expirada.')));
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
                }elseif($plog->getUsuario()->getId() == $session->get('usuario')['id'] and $orden > 10){
                    $class = '';
                    $meorder = '<tr>';
                    $meorder .= '<td>'.$orden.'</td>';
                    $meorder .= '<td><img class="up" src="'.$baseUrl.'/'.$plog->getUsuario()->getFoto().'" alt=""></td>';
                    $meorder .= '<td><span>'.$plog->getUsuario()->getNombre().' '.$plog->getUsuario()->getApellido().'</span></td>';
                    $meorder .= '<td>'.round($plog->getPorcentajeAvance()).'</td>';
                    $meorder .= '<td>';
                    $meorder .= '<div class="img-coin">';
                    $meorder .= '<img src="'.$baseUrl.'/assets/img/coins.svg" alt="">';
                    $meorder .= '<span class="text-coin">'.$plog->getPuntos().'K</span>';
                    $meorder .= '</div>';
                    $meorder .= '</td>';
                    $meorder .= '</tr>';
                    $topten = 0;
                }else{
                    $class = '';
                    $topten = 0;
                    $meorder = '';
                }
                $usuarios[]= array('orden'=>$orden,
                                   'nombre'=>$plog->getUsuario()->getNombre(),
                                   'apellido'=>$plog->getUsuario()->getApellido(),
                                   'foto'=>$plog->getUsuario()->getFoto(),
                                   'calificacion'=>round($plog->getPorcentajeAvance()),
                                   'experiencia'=>$plog->getPuntos(),
                                   'class'=>$class,
                                   'meorder'=>$meorder,
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

}