<?php

namespace Link\BackendBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Link\ComunBundle\Entity\AdminSesion;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\Yaml\Yaml;

class ReportesController extends Controller
{
    public function indexAction($app_id,$r)
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

        $usuario_empresa = 0;
        $empresas = array();
        $usuario = $this->getDoctrine()->getRepository('LinkComunBundle:AdminUsuario')->find($session->get('usuario')['id']); 

        if ($usuario->getEmpresa()) {
            $usuario_empresa = 1; 
        }
        else {
            $empresas = $this->getDoctrine()->getRepository('LinkComunBundle:AdminEmpresa')->findAll();
        } 

        if ($r == 1) 
        {
        	return $this->render('LinkBackendBundle:Reportes:participantesEmpresa.html.twig', array('empresas' => $empresas,
                                                                                        'usuario_empresa' => $usuario_empresa,
                                                                                        'usuario' => $usuario));	
        }
        else
        {
        	return $this->render('LinkBackendBundle:Reportes:participantesRegistrados.html.twig', array('empresas' => $empresas,
                                                                                        'usuario_empresa' => $usuario_empresa,
                                                                                        'usuario' => $usuario));
        }
        
    }

    public function ajaxParticipantesEAction(Request $request)
    {

        $em = $this->getDoctrine()->getManager();
        $empresa_id = $request->query->get('empresa_id');
        $nivel_id = $request->query->get('nivel_id');
        $f = $this->get('funciones');
        $yml = Yaml::parse(file_get_contents($this->get('kernel')->getRootDir().'/config/parametros.yml'));

        $qb = $em->createQueryBuilder();
        $qb->select('ru, u')
           ->from('LinkComunBundle:AdminRolUsuario', 'ru')
           ->leftJoin('ru.usuario', 'u')
           ->andWhere('u.empresa = :empresa_id')
           ->andWhere('ru.rol = :participante');
        $parametros['empresa_id'] = $empresa_id;
        $parametros['participante'] = $yml['parameters']['rol']['participante'];

        if ($nivel_id)
        {
            $qb->andWhere('u.nivel = :nivel_id');
            $parametros['nivel_id'] = $nivel_id;
        }

        $qb->setParameters($parametros);
        $query = $qb->getQuery();
        $rus = $query->getResult();
        
        $html = '<table class="table" id="dt">
                    <thead class="sty__title">
                        <tr>
                            <th class="hd__title">'.$this->get('translator')->trans('Nombre').'</th>
                            <th class="hd__title">'.$this->get('translator')->trans('Apellido').'</th>
                            <th class="hd__title">'.$this->get('translator')->trans('Login').'</th>
                            <th class="hd__title">'.$this->get('translator')->trans('Correo').'</th>
                            <th class="hd__title">'.$this->get('translator')->trans('Activo').'</th>
                            <th class="hd__title">'.$this->get('translator')->trans('Fecha de registro').'</th>
                            <th class="hd__title">'.$this->get('translator')->trans('Fecha de nacimiento').'</th>
                            <th class="hd__title">'.$this->get('translator')->trans('País').'</th>
                            <th class="hd__title">'.$this->get('translator')->trans('Nivel').'</th>
                        </tr>
                    </thead>
                    <tbody>';
        
        foreach ($rus as $ru)
        {
            $delete_disabled = $f->linkEliminar($ru->getUsuario()->getId(), 'AdminUsuario');
            $delete = $delete_disabled == '' ? 'delete' : '';
            $activo = $ru->getUsuario()->getActivo() == 'true' ? 'Si' : 'No';
            $html .= '<tr>
                        <td>'.$ru->getUsuario()->getNombre().'</td>
                        <td>'.$ru->getUsuario()->getApellido().'</td>
                        <td>'.$ru->getUsuario()->getLogin().'</td>
                        <td>'.$ru->getUsuario()->getCorreoPersonal().'</td>
                        <td>'.$activo.'</td>
                        <td></td>
                        <td></td>
                        <td>'.$ru->getUsuario()->getPais()->getId().'</td>
                        <td>'.$ru->getUsuario()->getNivel()->getNombre().'</td>
                    </tr>';
        }

        $html .= '</tbody>
                </table>';
        
        $return = array('html' => $html);
 
        $return = json_encode($return);
        return new Response($return, 200, array('Content-Type' => 'application/json'));
    }

    /*public function ajaxParticipantesEAction(Request $request)
    {

    }*/
}