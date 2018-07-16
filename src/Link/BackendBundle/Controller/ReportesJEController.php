<?php

namespace Link\BackendBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Link\ComunBundle\Entity\AdminSesion;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\Yaml\Yaml;

class ReportesJEController extends Controller
{
    public function horasConexionAction($app_id, Request $request)
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
        $em = $this->getDoctrine()->getManager();

        $usuario = $this->getDoctrine()->getRepository('LinkComunBundle:AdminUsuario')->find($session->get('usuario')['id']);

        $empresas = array();
        if (!$usuario->getEmpresa())
        {
            $empresas = $this->getDoctrine()->getRepository('LinkComunBundle:AdminEmpresa')->findBy(array('activo' => true),
                                                                                                    array('nombre' => 'ASC'));
        }

        return $this->render('LinkBackendBundle:Reportes:horasConexion.html.twig', array('usuario' => $usuario,
                                                                                         'empresas' => $empresas));

    }

    public function ajaxHorasConexionAction(Request $request)
    {
        
        $em = $this->getDoctrine()->getManager();
        
        $empresa_id = $request->request->get('empresa_id');
        $desde = $request->request->get('desde');
        $hasta = $request->request->get('hasta');

        list($d, $m, $a) = explode("/", $desde);
        $desde = "$a-$m-$d";

        list($d, $m, $a) = explode("/", $hasta);
        $hasta = "$a-$m-$d";

        // ESTRUCTURA de $conexiones:
        // $conexiones[0][0] => Día/Hora
        // $conexiones[0][1] => Etiqueta 00:00
        // $conexiones[0][2] => Etiqueta 01:00
        // ...
        // $conexiones[0][24] => Etiqueta 23:00
        // $conexiones[0][25] => Etiqueta Total
        // $conexiones[1][0] => Etiqueta Domingo
        // $conexiones[1][1] => Domingo a las 00:00
        // $conexiones[1][2] => Domingo a las 01:00
        // ...
        // $conexiones[1][24] => Domingo a las 23:00
        // $conexiones[1][25] => Total Domingo
        // $conexiones[2][0] => Etiqueta Lunes
        // $conexiones[2][1] => Lunes a las 00:00
        // $conexiones[2][2] => Lunes a las 01:00
        // ...
        // $conexiones[2][24] => Lunes a las 23:00
        // $conexiones[2][25] => Total Lunes
        // ...
        // $conexiones[7][0] => Etiqueta Sábado
        // $conexiones[7][1] => Sábado a las 00:00
        // $conexiones[7][2] => Sábado a las 01:00
        // ...
        // $conexiones[7][24] => Sábado a las 23:00
        // $conexiones[7][25] => Total Sábado
        // $conexiones[8][0] => Etiqueta Total
        // $conexiones[8][1] => Total a las 00:00
        // $conexiones[8][2] => Total a las 01:00
        // ...
        // $conexiones[8][24] => Total a las 23:00
        // $conexiones[8][25] => Total de totales
        $conexiones[0][0] = $this->get('translator')->trans('Día/Hora');

        // Etiquetas de horas
        $c = 1;
        for ($h=0; $h<24; $h++)
        {
            $hora = $h<=9 ? '0'.$h : $h;
            $conexiones[0][$c] = $hora.':00';
            $c++;
        }

        $conexiones[0][25] = 'Total';

        for ($c=0; $c<=24; $c++)
        {
            if ($c==0)
            {
                for ($f=1; $f<=8; $f++)
                {
                    switch ($f)
                    {
                        case 1:
                            $conexiones[$f][$c] = $this->get('translator')->trans('Domingo');
                            break;
                        case 2:
                            $conexiones[$f][$c] = $this->get('translator')->trans('Lunes');
                            break;
                        case 3:
                            $conexiones[$f][$c] = $this->get('translator')->trans('Martes');
                            break;
                        case 4:
                            $conexiones[$f][$c] = $this->get('translator')->trans('Miércoles');
                            break;
                        case 5:
                            $conexiones[$f][$c] = $this->get('translator')->trans('Jueves');
                            break;
                        case 6:
                            $conexiones[$f][$c] = $this->get('translator')->trans('Viernes');
                            break;
                        case 7:
                            $conexiones[$f][$c] = $this->get('translator')->trans('Sábado');
                            break;
                        case 8:
                            $conexiones[$f][$c] = 'Total';
                            break;
                    }
                }
            }
            else {
                // Cálculos desde la función de BD
            }
        }
        
        $return = array('conexiones' => $conexiones);

        $return = json_encode($return);
        return new Response($return, 200, array('Content-Type' => 'application/json'));
        
    }

    
}