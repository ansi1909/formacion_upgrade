<?php

namespace Link\FrontendBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Validator\Constraints\DateTime;
use Symfony\Component\Yaml\Yaml;

class BibliotecaController extends Controller {

	public function indexAction(Request $request, $filtro) {

		$em = $this->getDoctrine()->getManager();
		$f = $this->get('funciones');
		$session = new Session();

		if (!$session->get('iniFront') || $f->sesionBloqueda($session->get('sesion_id'))) {
			return $this->redirectToRoute('_authExceptionEmpresa', array('tipo' => 'sesion'));
		}
		$f->setRequest($session->get('sesion_id'));

		$usuario_id = $session->get('usuario')['id'];
		$empresa_id = $session->get('empresa')['id'];
		$todos = array();
		$videos = array();
		$podcast = array();
		$articulos = array();
		$libros = array();
		$hoy = new \DateTime();
		$now = strtotime($hoy->format('Y-m-d'));
		$yml = Yaml::parse(file_get_contents($this->get('kernel')->getRootDir() . '/config/parametros.yml'));
		$filter = mb_strtolower(trim($filtro));
		//print_r($filter);die();
		if ($filter == "") {
			$query = $em->createQuery('SELECT n FROM LinkComunBundle:AdminNoticia n
                                        WHERE n.empresa = :empresa_id
                                        AND n.tipoNoticia = :tipo
                                        AND n.fechaPublicacion <= :hoy
                                        AND n.fechaVencimiento >= :hoy
                                        AND n.pdf IS NOT NULL')
				->setParameters(array(
					'empresa_id' => $empresa_id,
					'tipo' => $yml['parameters']['tipo_noticias']['biblioteca_virtual'],
					'hoy' => date('Y-m-d'),
				));
			$noticias_db = $query->getResult();
		} else {

			$pattern = "/^([\¿\¡a-zA-Z0-9-ñÑáéíóúÁÉÍÓÚ])+((\s*)+([a-zA-Z0-9-ZñÑáéíóúÁÉÍÓÚ\?\!]*)*)+$/";
			if (preg_match($pattern, $filter)) {

				$query = $em->createQuery('SELECT n FROM LinkComunBundle:AdminNoticia n
                                        WHERE n.empresa = :empresa_id
                                        AND n.tipoNoticia = :tipo
                                        AND n.fechaPublicacion <= :hoy
                                        AND n.fechaVencimiento >= :hoy
                                        AND ( LOWER(n.titulo) LIKE :filtro OR LOWER(n.autor) LIKE :filtro)
                                        AND n.pdf IS NOT NULL')
					->setParameters(array(
						'empresa_id' => $empresa_id,
						'tipo' => $yml['parameters']['tipo_noticias']['biblioteca_virtual'],
						'hoy' => date('Y-m-d'),
						'filtro' => '%'.$filter.'%',
					));
				$noticias_db = $query->getResult();
			} else {
				$noticias_db = [];
			}
		}

		foreach ($noticias_db as $noticia) {
			$todos[] = array('id' => $noticia->getId(),
				'titulo' => $noticia->getTitulo(),
				'tipo' => $noticia->getTipoBiblioteca()->getNombre(),
				'tid' => $noticia->getTipoBiblioteca()->getId());

			if ($noticia->getTipoBiblioteca()->getId() == $yml['parameters']['tipo_biblioteca']['video']) {
				$videos[] = array('id' => $noticia->getId(),
					'titulo' => $noticia->getTitulo(),
					'tipo' => $noticia->getTipoBiblioteca()->getNombre(),
					'tid' => $noticia->getTipoBiblioteca()->getId());
			} else if ($noticia->getTipoBiblioteca()->getId() == $yml['parameters']['tipo_biblioteca']['podcast']) {
				$podcast[] = array('id' => $noticia->getId(),
					'titulo' => $noticia->getTitulo(),
					'tipo' => $noticia->getTipoBiblioteca()->getNombre(),
					'tid' => $noticia->getTipoBiblioteca()->getId());
			} else if ($noticia->getTipoBiblioteca()->getId() == $yml['parameters']['tipo_biblioteca']['articulo']) {
				$articulos[] = array('id' => $noticia->getId(),
					'titulo' => $noticia->getTitulo(),
					'tipo' => $noticia->getTipoBiblioteca()->getNombre(),
					'tid' => $noticia->getTipoBiblioteca()->getId());
			} else {
				$libros[] = array('id' => $noticia->getId(),
					'titulo' => $noticia->getTitulo(),
					'tipo' => $noticia->getTipoBiblioteca()->getNombre(),
					'tid' => $noticia->getTipoBiblioteca()->getId());
			}
		}

		return $this->render('LinkFrontendBundle:Biblioteca:index.html.twig', array(
			'usuario_id' => $usuario_id,
			'todos' => $todos,
			'videos' => $videos,
			'podcasts' => $podcast,
			'articulos' => $articulos,
			'libros' => $libros,
			'filtro' => $filtro,
			'recursos_disponibles' => $this->get('translator')->trans('Recursos disponibles') . ': ' . count($todos),
		));

	}

	public function detalleAction($biblioteca_id) {

		$em = $this->getDoctrine()->getManager();
		$f = $this->get('funciones');
		$session = new Session();

		if (!$session->get('iniFront') || $f->sesionBloqueda($session->get('sesion_id'))) {
			return $this->redirectToRoute('_authExceptionEmpresa', array('tipo' => 'sesion'));
		}
		$f->setRequest($session->get('sesion_id'));

		$biblioteca = $this->getDoctrine()->getRepository('LinkComunBundle:AdminNoticia')->find($biblioteca_id);
		$anuncios = array();
		$hoy = new \DateTime();
		$now = strtotime($hoy->format('d-m-Y'));
		$videos = array();
		$audios = array();
		$pdfs = array();
		$pdfs = array();
		$yml = Yaml::parse(file_get_contents($this->get('kernel')->getRootDir() . '/config/parametros.yml'));

		if ($biblioteca->getTipoBiblioteca()->getId() == $yml['parameters']['tipo_biblioteca']['video']) {
			$query = $em->createQuery('SELECT n FROM LinkComunBundle:AdminNoticia n
                                       WHERE n.tipoBiblioteca = :biblioteca
                                       AND n.tipoNoticia = :noticia_id
                                       AND n.id != :id
                                       AND n.pdf IS NOT NULL
                                       AND n.empresa = :empresa_id')
				->setMaxResults(3)
				->setParameters(array('biblioteca' => $yml['parameters']['tipo_biblioteca']['video'],
					'noticia_id' => $yml['parameters']['tipo_noticias']['biblioteca_virtual'],
					'id' => $biblioteca_id,
					'empresa_id' => $biblioteca->getEmpresa()->getId()));
			$anuncios = $query->getResult();

			foreach ($anuncios as $anuncio) {
				$fecha_i = strtotime($anuncio->getFechaPublicacion()->format('d-m-Y'));
				$fecha_f = strtotime($anuncio->getFechaVencimiento()->format('d-m-Y'));
				if ($now >= $fecha_i && $now < $fecha_f) {
					$videos[] = array('id' => $anuncio->getId(),
						'titulo' => $anuncio->getTitulo(),
						'tipo' => $anuncio->getTipoBiblioteca()->getNombre(),
						'tid' => $anuncio->getTipoBiblioteca()->getId());
				}
			}

		} elseif ($biblioteca->getTipoBiblioteca()->getId() == $yml['parameters']['tipo_biblioteca']['podcast']) {
			$query = $em->createQuery('SELECT n FROM LinkComunBundle:AdminNoticia n
                                       WHERE n.tipoBiblioteca = :biblioteca
                                       AND n.tipoNoticia = :noticia_id
                                       AND n.id != :id
                                       AND n.pdf IS NOT NULL
                                       AND n.empresa = :empresa_id')
				->setMaxResults(3)
				->setParameters(array('biblioteca' => $yml['parameters']['tipo_biblioteca']['podcast'],
					'noticia_id' => $yml['parameters']['tipo_noticias']['biblioteca_virtual'],
					'id' => $biblioteca_id,
					'empresa_id' => $biblioteca->getEmpresa()->getId()));
			$anuncios = $query->getResult();

			foreach ($anuncios as $anuncio) {
				$fecha_i = strtotime($anuncio->getFechaPublicacion()->format('d-m-Y'));
				$fecha_f = strtotime($anuncio->getFechaVencimiento()->format('d-m-Y'));
				if ($now >= $fecha_i && $now < $fecha_f) {
					$audios[] = array('id' => $anuncio->getId(),
						'titulo' => $anuncio->getTitulo(),
						'tipo' => $anuncio->getTipoBiblioteca()->getNombre(),
						'tid' => $anuncio->getTipoBiblioteca()->getId());
				}
			}
		} elseif ($biblioteca->getTipoBiblioteca()->getId() == $yml['parameters']['tipo_biblioteca']['articulo'] || $biblioteca->getTipoBiblioteca()->getId() == $yml['parameters']['tipo_biblioteca']['libro']) {
			$query = $em->createQuery('SELECT n FROM LinkComunBundle:AdminNoticia n
                                       WHERE n.tipoBiblioteca IN (:biblioteca)
                                       AND n.tipoNoticia = :noticia_id
                                       AND n.id != :id
                                       AND n.pdf IS NOT NULL
                                       AND n.empresa = :empresa_id')
				->setMaxResults(3)
				->setParameters(array('biblioteca' => array($yml['parameters']['tipo_biblioteca']['articulo'], $yml['parameters']['tipo_biblioteca']['libro']),
					'noticia_id' => $yml['parameters']['tipo_noticias']['biblioteca_virtual'],
					'id' => $biblioteca_id,
					'empresa_id' => $biblioteca->getEmpresa()->getId()));
			$anuncios = $query->getResult();

			foreach ($anuncios as $anuncio) {
				$fecha_i = strtotime($anuncio->getFechaPublicacion()->format('d-m-Y'));
				$fecha_f = strtotime($anuncio->getFechaVencimiento()->format('d-m-Y'));
				if ($now >= $fecha_i && $now < $fecha_f) {
					$pdfs[] = array('id' => $anuncio->getId(),
						'titulo' => $anuncio->getTitulo(),
						'tipo' => $anuncio->getTipoBiblioteca()->getNombre(),
						'tid' => $anuncio->getTipoBiblioteca()->getId());
				}
			}
		}

		return $this->render('LinkFrontendBundle:Biblioteca:detalle.html.twig', array('biblioteca' => $biblioteca,
			'videos' => $videos,
			'audios' => $audios,
			'pdfs' => $pdfs));
	}

	public function ajaxSearchBibliotecaAction(Request $request) {
		$session = new Session();
		$em = $this->getDoctrine()->getManager();
		$yml = Yaml::parse(file_get_contents($this->get('kernel')->getRootDir() . '/config/parametros.yml'));
		$empresa_id = $session->get('empresa')['id'];
		$term = mb_strtolower(trim($request->get('term')));
		$bibliotecas = array();
		$pattern = "/^([\¿\¡a-zA-Z0-9-ñÑáéíóúÁÉÍÓÚ])+((\s*)+([a-zA-Z0-9-ZñÑáéíóúÁÉÍÓÚ\?\!]*)*)+$/";
		if (preg_match($pattern, $term)) {
			$query = $em->createQuery('SELECT n FROM LinkComunBundle:AdminNoticia n
										WHERE n.tipoNoticia = :biblioteca
										AND n.empresa = :empresa_id
										AND ( LOWER(n.titulo) LIKE :term OR LOWER(n.autor) LIKE :term)
										AND n.fechaPublicacion <= :hoy
										AND n.fechaVencimiento >= :hoy
										AND n.pdf IS NOT NULL')
				->setParameters(array(
					'biblioteca' => $yml['parameters']['tipo_noticias']['biblioteca_virtual'],
					'term' => '%'.$term.'%',
					'hoy' => date('Y-m-d'),
					'empresa_id' => $empresa_id,
				));
			$bibliotecas_bd = $query->getResult();
		} else {
			$bibliotecas_bd = [];
		}

		foreach ($bibliotecas_bd as $biblioteca) {
			$bibliotecas[] = array('id' => $biblioteca->getId(),
				'label' => $biblioteca->getTitulo(),
				'value' => $biblioteca->getTitulo());
		}

		$return = json_encode($bibliotecas);
		return new Response($return, 200, array('Content-Type' => 'application/json'));

	}

}