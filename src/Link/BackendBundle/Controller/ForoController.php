<?php

namespace Link\BackendBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityRepository;
use Link\ComunBundle\Entity\CertiForo;
use Link\ComunBundle\Entity\CertiPagina;
use Link\ComunBundle\Entity\CertiPaginaEmpresa;
use Link\ComunBundle\Entity\AdminUsuario;
use Symfony\Component\Yaml\Yaml;

class ForoController extends Controller
{

   public function indexAction($app_id, Request $request)
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

        $yml = Yaml::parse(file_get_contents($this->get('kernel')->getRootDir().'/config/parametros.yml'));
        $em = $this->getDoctrine()->getManager();

        $roles=$session->get('usuario')['roles'];
        $answer_delete= (in_array($yml['parameters']['rol']['tutor'],$roles) || in_array($yml['parameters']['rol']['empresa'],$roles)) ? 1 : 0; //verifica si el usuario posee el rol tutor virtual(3) o empresa(5)
        

        $usuario_empresa = 0;
        $empresas = array();
        $paginas = array();
        $comentarios = array();
        $usuario = $this->getDoctrine()->getRepository('LinkComunBundle:AdminUsuario')->find($session->get('usuario')['id']); 

        if ($usuario->getEmpresa()) 
        {
            
            $usuario_empresa = 1;
            
            $query = $em->createQuery("SELECT p FROM LinkComunBundle:CertiPagina p
                                       JOIN LinkComunBundle:CertiPaginaEmpresa e
                                       WHERE e.activo = :activo
                                       AND e.colaborativo = :activo
                                       AND e.pagina = p.id
                                       AND p.pagina IS NULL
                                       AND e.empresa = :empresa_id
                                       ORDER BY p.nombre ASC")
                        ->setParameters(array('empresa_id' => $usuario->getEmpresa()->getId(),
                                              'activo' => true));
            $paginas_bd = $query->getResult();
            foreach ($paginas_bd as $pagina_bd)
            {
                
                $query = $em->createQuery("SELECT COUNT(f.id) FROM LinkComunBundle:CertiForo f
                                           JOIN LinkComunBundle:CertiPaginaEmpresa e
                                           WHERE e.activo = :activo
                                           AND e.pagina = f.pagina
                                           AND e.empresa = f.empresa
                                           AND f.foro IS NULL
                                           AND f.empresa = :empresa_id
                                           AND f.pagina = :pagina_id")
                             ->setParameters(array('empresa_id' => $usuario->getEmpresa()->getId(),
                                                   'pagina_id' => $pagina_bd->getId(),
                                                   'activo' => true));
                $cantidad_temas = $query->getSingleScalarResult();

                $paginas[] = array('id' => $pagina_bd->getId(),
                                   'nombre' => $pagina_bd->getNombre(),
                                   'cantidad_temas' => $cantidad_temas);

            }

            $query2 = $em->createQuery("SELECT m FROM LinkComunBundle:CertiForo m
                                       JOIN LinkComunBundle:CertiPaginaEmpresa e
                                       WHERE e.activo = :activo
                                       AND e.pagina = m.pagina
                                       AND e.empresa = m.empresa
                                       AND m.foro IS NULL
                                       AND m.empresa = :empresa_id
                                       ORDER BY m.id ASC")
                        ->setParameters(array('empresa_id' => $usuario->getEmpresa()->getId(),
                                              'activo' => true));
            $coments = $query2->getResult();

        }
        else {
            $query = $em->createQuery("SELECT e FROM LinkComunBundle:AdminEmpresa e
                                       WHERE e.activo = 'true'
                                       ORDER BY e.nombre ASC");
            $empresas = $query->getResult();

            $query2 = $em->createQuery("SELECT m FROM LinkComunBundle:CertiForo m
                                       WHERE m.foro IS NULL
                                       ORDER BY m.id ASC");
            $coments = $query2->getResult();
        }

        foreach ($coments as $coment)
        {

            $query = $em->createQuery("SELECT COUNT(fa.id) FROM LinkComunBundle:CertiForoArchivo fa
                                       WHERE fa.foro = :foro_id")
                     ->setParameter('foro_id', $coment->getId());
            $archivos = $query->getSingleScalarResult();

            $comentarios[] = array('id' => $coment->getId(),
                                   'asunto' => $coment->getTema(),
                                   'mensaje' => $coment->getMensaje(),
                                   'usuarioId' => $coment->getUsuario()->getId(),
                                   'nombreUsuario' => $coment->getUsuario()->getNombre(),
                                   'apellidoUsuario' => $coment->getUsuario()->getApellido(),
                                   'fecharegistro' => $coment->getFechaRegistro()->format("d/m/Y"),
                                   'delete_disabled' => $f->linkEliminar($coment->getId(),'CertiForo'),
                                   'answer_delete' => $answer_delete,
                                   'archivos' => $archivos
                                 );

        }

        return $this->render('LinkBackendBundle:Foro:index.html.twig', array('empresas' => $empresas,
                                                                             'paginas' => $paginas,
                                                                             'usuario_empresa' => $usuario_empresa,
                                                                             'comentarios' => $comentarios,
                                                                             'usuario' => $usuario));

    }

    public function ajaxPaginasForoAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $empresa_id = $request->query->get('empresa_id');

        $query = $em->createQuery("SELECT p FROM LinkComunBundle:CertiPagina p
                                       JOIN LinkComunBundle:CertiPaginaEmpresa e
                                       WHERE e.activo = :activo
                                       AND e.colaborativo = :activo
                                       AND e.pagina = p.id
                                       AND p.pagina IS NULL
                                       AND e.empresa = :empresa_id
                                       ORDER BY p.nombre ASC")
                    ->setParameters(array('empresa_id' => $empresa_id,
                                          'activo' => true));
        $paginas_bd = $query->getResult();
        
        $options = '<option value="0">'.$this->get('translator')->trans('TODOS LOS TEMAS').'</option>';
        foreach ($paginas_bd as $pagina_bd)
        {
            
            $query = $em->createQuery("SELECT COUNT(f.id) FROM LinkComunBundle:CertiForo f
                                       JOIN LinkComunBundle:CertiPaginaEmpresa e
                                       WHERE e.activo = :activo
                                       AND e.pagina = f.pagina
                                       AND e.empresa = f.empresa
                                       AND f.foro IS NULL
                                       AND f.empresa = :empresa_id
                                       AND f.pagina = :pagina_id")
                         ->setParameters(array('empresa_id' => $empresa_id,
                                               'pagina_id' => $pagina_bd->getId(),
                                               'activo' => true));
            $cantidad_temas = $query->getSingleScalarResult();

            $options .= '<option value="'.$pagina_bd->getId().'">'.$pagina_bd->getNombre().' ('.$cantidad_temas.')</option>';

        }
        
        $return = array('options' => $options);
        
        $return = json_encode($return);
        return new Response($return, 200, array('Content-Type' => 'application/json'));
        
    }

    public function ajaxComentariosForoAction(Request $request)
    {
        
        $session = new Session();
        $em = $this->getDoctrine()->getManager();
        $f = $this->get('funciones');
        $empresa_id = $request->query->get('empresa_id');
        $pagina_id = $request->query->get('pagina_id');
        $usuario_id = $request->query->get('usuario_id');
        $html = '';

        $yml = Yaml::parse(file_get_contents($this->get('kernel')->getRootDir().'/config/parametros.yml'));

        $roles=$session->get('usuario')['roles'];
        $answer_delete= (in_array($yml['parameters']['rol']['tutor'],$roles) || in_array($yml['parameters']['rol']['empresa'],$roles)) ? 1 : 0; //verifica si el usuario posee el rol tutor virtual(3) o empresa(5)

        if($pagina_id == 0){
            $query2 = $em->createQuery("SELECT m FROM LinkComunBundle:CertiForo m
                                       JOIN LinkComunBundle:CertiPaginaEmpresa e
                                       WHERE e.activo = :activo 
                                       AND e.pagina = m.pagina
                                       AND e.empresa = m.empresa
                                       AND m.foro IS NULL
                                       AND m.empresa = :empresa_id
                                       ORDER BY m.id ASC")
                         ->setParameters(array('empresa_id' => $empresa_id,
                                               'activo' => true));
        }
        else {
            $query2 = $em->createQuery("SELECT m FROM LinkComunBundle:CertiForo m
                                       JOIN LinkComunBundle:CertiPaginaEmpresa e
                                       WHERE e.activo = :activo
                                       AND e.pagina = m.pagina
                                       AND e.empresa = m.empresa
                                       AND m.foro IS NULL
                                       AND m.empresa = :empresa_id
                                       AND m.pagina = :pagina_id
                                       ORDER BY m.id ASC")
                         ->setParameters(array('empresa_id' => $empresa_id,
                                               'pagina_id' => $pagina_id,
                                               'activo' => true));
        }
        
        $comentarios = $query2->getResult();

        $html .= '<table class="table" id="dt">
                        <thead class="sty__title">
                            <tr>
                                <th>'.$this->get('translator')->trans('Tema').'</th>
                                <th>'.$this->get('translator')->trans('Participante').'</th>
                                <th>'.$this->get('translator')->trans('Fecha').'</th>
                                <th>'.$this->get('translator')->trans('Acciones').'</th>
                            </tr>
                        </thead>
                        <tbody>';

        foreach ($comentarios as $coment)
        {

            $delete_disabled = $f->linkEliminar($coment->getId(), 'CertiForo');
            $delete = $delete_disabled=='' ? 'delete' : '';

            $query = $em->createQuery("SELECT COUNT(fa.id) FROM LinkComunBundle:CertiForoArchivo fa
                                       WHERE fa.foro = :foro_id")
                     ->setParameter('foro_id', $coment->getId());
            $archivos = $query->getSingleScalarResult();
            
            $html .= '<tr>
                        <td class="respuesta'.$coment->getId().'">'.$coment->getTema().'</td>
                        <td>'.$coment->getUsuario()->getNombre().' '.$coment->getUsuario()->getApellido().'</td>
                        <td>'.$coment->getFechaRegistro()->format("d/m/Y").'</td>
                        <td class="center">';
                            if($coment->getUsuario()->getId() == $usuario_id){
                                $html .= '<a href="#" title="'.$this->get('translator')->trans("Editar").'" class="btn btn-link btn-sm edit" data-toggle="modal" data-target="#formModal" data="'.$coment->getId().'"><span class="fa fa-pencil"></span></a>';
                            }
                            if ($answer_delete == 1)
                            {
                                $html .= '<a href="#" title="'.$this->get('translator')->trans("Responder").'" class="btn btn-link btn-sm add" data-toggle="modal" data-target="#formModal" data="'.$coment->getId().'"><span class="fa fa-plus"></span></a>';
                            }
                            $html .= '<a href="#" title="'.$this->get('translator')->trans("Ver").'" class="btn btn-link btn-sm see" data="'.$coment->getId().'"><span class="fa fa-eye"></span></a>';
                            $html .= '<a href="#" title="'.$this->get('translator')->trans("Eliminar").'" class="btn btn-link btn-sm '.$delete.' '.$delete_disabled.'" data="'.$coment->getId().'"><span class="fa fa-trash"></span></a>
                        </td>
                    </tr>';
        }
        $html .= '</tbody>
                </table>';

        $return = array('html' => $html);


        $return = json_encode($return);
        return new Response($return, 200, array('Content-Type' => 'application/json'));
        
    }

    public function ajaxRespuestasComentariosForoAction(Request $request)
    {
        
        $em = $this->getDoctrine()->getManager();
        $f = $this->get('funciones');
        $foro_id = $request->query->get('foro_id');
        $usuario_id = $request->query->get('usuario_id');
        $html = '';
        $panel = '';

        $foro = $this->getDoctrine()->getRepository('LinkComunBundle:CertiForo')->find($foro_id);

        $query2 = $em->createQuery("SELECT m FROM LinkComunBundle:CertiForo m
                                   WHERE m.foro = :foro_id
                                   ORDER BY m.id ASC")
                     ->setParameters(array('foro_id' => $foro_id));
        
        $comentarios = $query2->getResult();

        $html .= '<table class="table" id="dtSub">
                        <thead class="sty__title">
                            <tr>
                                <th>'.$this->get('translator')->trans('Mensaje').'</th>
                                <th>'.$this->get('translator')->trans('Participante').'</th>
                                <th>'.$this->get('translator')->trans('Fecha').'</th>
                                <th>'.$this->get('translator')->trans('Acciones').'</th>
                            </tr>
                        </thead>
                        <tbody>';

        $query = $em->createQuery("SELECT COUNT(fa.id) FROM LinkComunBundle:CertiForoArchivo fa
                                       WHERE fa.foro = :foro_id")
                     ->setParameter('foro_id', $foro_id);
        $archivos = $query->getResult();              

        foreach ($comentarios as $coment)
        {

            $delete_disabled = $f->linkEliminar($coment->getId(), 'CertiForo');
            $delete = $delete_disabled=='' ? 'delete' : '';

            $query = $em->createQuery("SELECT COUNT(fa.id) FROM LinkComunBundle:CertiForoArchivo fa
                                       WHERE fa.foro = :foro_id
                                       AND fa.usuario= :usuario_id")
                     ->setParameters(array('foro_id'=>$foro_id,'usuario_id'=>$coment->getUsuario()->getId()));
            $archivos = $query->getSingleScalarResult();   

            $html .= '<tr>
                        <td class="respuesta'.$coment->getId().'">'.$coment->getMensaje().'</td>
                        <td>'.$coment->getUsuario()->getNombre().' '.$coment->getUsuario()->getApellido().'</td>
                        <td>'.$coment->getFechaRegistro()->format("d/m/Y").'</td>
                        <td class="center">';
                          if($coment->getUsuario()->getId() == $usuario_id){
                             $html .= '<a href="#" title="'.$this->get('translator')->trans("Editar").'" class="btn btn-link btn-sm edit" data-toggle="modal" data-target="#formModal" data="'.$coment->getId().'"><span class="fa fa-pencil"></span></a>';
                          }
                          if ($archivos){
                                $html .= '<a href="#" title="'.$this->get('translator')->trans("Archivos").'" class="btn btn-link btn-sm fileList" data="'.$coment->getUsuario()->getId().'" data-foro="'.$foro_id.'"><span class="fa fa-archive"></span></a>';
                            }
                          
                           $html .='<a href="#" title="'.$this->get('translator')->trans("Eliminar").'" class="btn btn-link btn-sm '.$delete.' '.$delete_disabled.'" data="'.$coment->getId().'"><span class="fa fa-trash"></span></a>';
                           $html .='
                        </td>
                    </tr>';
        }
        $html .= '</tbody>
                </table>';

        $panel = '<div class="card">
                    <div class="card-header gradiente">
                        <h5 class="card-title">'.$this->get('translator')->trans("Tema seleccionado").'</h5>
                    </div>
                    <div class="card-block">'.$foro->getTema().'</div>
                </div>';

        $return = array('html' => $html,
                        'panel' => $panel);

        $return = json_encode($return);
        return new Response($return, 200, array('Content-Type' => 'application/json'));
        
    }

    public function ajaxFilesForoListAction(Request $request)
    {

       $em = $this->getDoctrine()->getManager();
       $f = $this->get('funciones');
       $foro_id = $request->request->get('foro_id');
       $usuario_id = $request->request->get('usuario_id');
       $html ='';
       
       $foro = $this->getDoctrine()->getRepository('LinkComunBundle:CertiForo')->find($foro_id);
       
       $query = $em->createQuery("SELECT fa FROM LinkComunBundle:CertiForoArchivo fa
                                   WHERE fa.foro = :foro_id
                                   AND fa.usuario = :usuario_id
                                   ORDER BY fa.id ASC")
                     ->setParameters(array('foro_id'=>$foro_id,'usuario_id'=>$usuario_id));
       $archivos = $query->getResult();
       
       if ($archivos)
       {
          
            $total = count($archivos);
            $mod = $total%2;
            $filas = ($mod==0)? ($total/2):(($total-1)/2)+1;
            $e = 0;

            foreach ($archivos as $archivo) 
            {

                $ruta = $this->container->getParameter('folders')['uploads'].$archivo->getArchivo();
                $extension = explode('.',$archivo->getArchivo());
                $iconoExtension = $f->getWebDirectory().'/front/assets/img/'.$extension[1].'.svg';

                //$html .= '<div class="row" style="margin-top: 15px;  border-bottom: 2px solid #EEE8E7;">';

                $html .= '<div class="row" style="margin-top: 15px;  border-bottom: 2px solid #EEE8E7;">
                          <div class ="col-md-1" style="margin-bottom:5px"> <img src="'.$iconoExtension.'" width=35  height=35 > </div>
                          <div class ="col-md-7" style="margin-bottom:5px"><a href ="'.$ruta.'"  class="btn btn-link btn-sm " download>'.$archivo->getDescripcion().' - '.$archivo->getUsuario()->getNombre().' '.$archivo->getUsuario()->getApellido().'</a></div>
                          </div>';

                $e++;

            }
         
            //$html .= '</div>';

       }
       
       $return = ['html'=>$html,
                  'tema' => $foro->getTema()];

       $return = json_encode($return);
       return new Response($return, 200, array('Content-Type' => 'application/json'));

    }

    public function ajaxUpdateRespuestaForoAction(Request $request)
    {

        $em = $this->getDoctrine()->getManager();
        $f = $this->get('funciones');
        $session = new Session();

        $comentario_id = $request->request->get('comentario_id');
        $foro_id = $request->request->get('foro_id');
        $respuesta = $request->request->get('respuesta');

        $usuario = $this->getDoctrine()->getRepository('LinkComunBundle:AdminUsuario')->find($session->get('usuario')['id']);

        $fecha_actual = date('Y/m/d H:m:s');

        if($comentario_id != ''){
            $new_respuesta = $this->getDoctrine()->getRepository('LinkComunBundle:CertiForo')->find($comentario_id);
            $foro_padre = $new_respuesta->getForo();
        }else{
            $foro_padre = $this->getDoctrine()->getRepository('LinkComunBundle:CertiForo')->find($foro_id);
            $new_respuesta = new CertiForo();
        }
        
        $new_respuesta->setMensaje($respuesta);
        $new_respuesta->setFechaRegistro(new \DateTime($fecha_actual));
        $new_respuesta->setPagina($foro_padre->getPagina());
        $new_respuesta->setUsuario($usuario);
        $new_respuesta->setForo($foro_padre);
        $new_respuesta->setEmpresa($foro_padre->getEmpresa());
        
        $em->persist($new_respuesta);
        $em->flush();

        $return = array('id' => $new_respuesta->getId(),
                        'delete_disabled' =>$f->linkEliminar($new_respuesta->getId(),'CertiForo'));

        $return = json_encode($return);
        return new Response($return, 200, array('Content-Type' => 'application/json'));

    }
}
