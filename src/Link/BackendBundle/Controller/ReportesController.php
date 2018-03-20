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

        return $this->render('LinkBackendBundle:Reportes:index.html.twig', array('empresas' => $empresas,
			                                                                                        'usuario_empresa' => $usuario_empresa,
			                                                                                        'usuario' => $usuario,
			                                                                                        'reporte'=>$r));	
    }

    public function ajaxProgramasEAction(Request $request)
    {
    	$em = $this->getDoctrine()->getManager();
        $empresa_id = $request->query->get('empresa_id');

        $query = $em->createQuery('SELECT pe,p FROM LinkComunBundle:CertiPaginaEmpresa pe
                                   JOIN pe.pagina p
                                   WHERE pe.empresa = :empresa_id
                                   AND p.pagina IS NULL')
                    ->setParameter('empresa_id', $empresa_id);
        $paginas = $query->getResult();

        $options = '<option value=""></option>';
        foreach ($paginas as $pagina)
        {
            $options .= '<option value="'.$pagina->getPagina()->getId().'">'.$pagina->getPagina()->getNombre().'</option>';
        }
        
        $return = array('options' => $options);
        
        $return = json_encode($return);
        return new Response($return, 200, array('Content-Type' => 'application/json'));
    }

    public function ajaxListadoParticipantesAction(Request $request)
    {

        $em = $this->getDoctrine()->getManager();
        $f = $this->get('funciones');
        $yml = Yaml::parse(file_get_contents($this->get('kernel')->getRootDir().'/config/parametros.yml'));

        $empresa_id = $request->query->get('empresa_id');
        $nivel_id = $request->query->get('nivel_id');
        $pagina_id = $request->query->get('pagina_id');
        $reporte = $request->query->get('reporte');

        // Llamada a la función de BD que duplica la página
        $query = $em->getConnection()->prepare('SELECT
                                                fnlistado_participantes(:re, :preporte, :pempresa_id, :pnivel_id, :ppagina_id) as
                                                resultado; fetch all from re;');
        $re = 're';
        $query->bindValue(':re', $re, \PDO::PARAM_STR);
        $query->bindValue(':preporte', $reporte, \PDO::PARAM_INT);
        $query->bindValue(':pempresa_id', $empresa_id, \PDO::PARAM_INT);
        $query->bindValue(':pnivel_id', $nivel_id, \PDO::PARAM_INT);
        $query->bindValue(':ppagina_id', $pagina_id, \PDO::PARAM_INT);
        $query->execute();
        $r = $query->fetchAll();
        
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
        
        foreach ($r as $ru)
        {
            $activo = $ru['activo'] ? $this->get('translator')->trans('Sí') : 'No';
            $html .= '<tr>
                        <td>'.$ru['nombre'].'</td>
                        <td>'.$ru['apellido'].'</td>
                        <td>'.$ru['login'].'</td>
                        <td>'.$ru['correo'].'</td>
                        <td>'.$activo.'</td>
                        <td>'.$ru['fecha_registro'].'</td>
                        <td>'.$ru['fecha_nacimiento'].'</td>
                        <td>'.$ru['pais'].'</td>
                        <td>'.$ru['nivel'].'</td>
                    </tr>';
        }

        $html .= '</tbody>
                </table>';
        
        $return = array('html' => $html);
 
        $return = json_encode($return);
        return new Response($return, 200, array('Content-Type' => 'application/json'));
    }

    public function ajaxParticipantesRAction(Request $request)
    {
    	$em = $this->getDoctrine()->getManager();
        $empresa_id = $request->query->get('empresa_id');
        $pagina_id = $request->query->get('pagina_id');
        $yml = Yaml::parse(file_get_contents($this->get('kernel')->getRootDir().'/config/parametros.yml'));

        $query = $em->createQuery('SELECT n FROM LinkComunBundle:AdminNivel n 
                                   WHERE n.empresa = :empresa_id')
                    ->setParameter('empresa_id', $empresa_id);
        $niveles = $query->getResult();

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

        foreach($niveles as $nivel)
        {
        	$nivel_pagina = $em->getRepository('LinkComunBundle:CertiNivelPagina')->findOneBy(array('paginaEmpresa' =>$pagina_id,
                                                                                                    'nivel' => $nivel->getId()));
        	if ($nivel_pagina) 
        	{
        		$query = $em->createQuery('SELECT ru FROM LinkComunBundle:AdminRolUsuario ru
                                           JOIN ru.usuario u
                                           WHERE u.empresa = :empresa_id
                                           AND ru.rol = :participante
                                           AND u.nivel = :nivel_id')
                            ->setParameters(array('empresa_id'=> $empresa_id,
                        						  'participante'=> $yml['parameters']['rol']['participante'],
                        						  'nivel_id'=> $nivel_pagina->getNivel()->getId()));
                $usuarios = $query->getResult();

                foreach($usuarios as $usuario)
                {
                	$html .= '<tr>
		                        <td>'.$usuario->getUsuario()->getNombre().'</td>
		                        <td>'.$usuario->getUsuario()->getApellido().'</td>
		                        <td>'.$usuario->getUsuario()->getLogin().'</td>
		                        <td>'.$usuario->getUsuario()->getCorreoPersonal().'</td>
		                        <td></td>
		                        <td></td>
		                        <td></td>
		                        <td>'.$usuario->getUsuario()->getPais()->getId().'</td>
		                        <td>'.$usuario->getUsuario()->getNivel()->getNombre().'</td>
		                    </tr>';
                }
        	}
        }

        $html .= '</tbody>
                </table>';

        $return = array('html' => $html);
 
        $return = json_encode($return);
        return new Response($return, 200, array('Content-Type' => 'application/json'));
    }
}