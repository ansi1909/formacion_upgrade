<?php

namespace Link\ComunBundle\Services;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Translation\TranslatorInterface;


class Reportes
{	
	
	protected $em;
	protected $container;
	protected $mailer;
	private $templating;
	private $translator;

    public function __construct(\Doctrine\ORM\EntityManager $em, ContainerInterface $container)
	{

		$this->em = $em;
		$this->container = $container;
		$this->mailer = $container->get('mailer');
        $this->templating = $container->get('templating');
        $this->translator = $container->get('translator');
	}

	// Cálculo del reporte Horas de Conexión por Empresa en un período determinado
	public function horasConexion($empresa_id, $desde, $hasta)
	{

		$em = $this->em;
		
		// Acumuladores
        $mayor = 0;
        $celda_mayor = array();
        $total = 0;

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
        $conexiones[0][0] = $this->translator->trans('Día/Hora');

        // Etiquetas de horas
        $c = 1;
        for ($h=0; $h<24; $h++)
        {
            $hora = $h<=9 ? '0'.$h : $h;
            $conexiones[0][$c] = $hora.':00';
            $c++;
        }

        $conexiones[0][25] = 'Total';

        // Columna de Total con valor cero
        for ($f=1; $f<=8; $f++)
        {
            $conexiones[$f][25] = 0;
        }

        for ($c=0; $c<=24; $c++)
        {
            if ($c==0)
            {
                for ($f=1; $f<=8; $f++)
                {
                    switch ($f)
                    {
                        case 1:
                            $conexiones[$f][$c] = $this->translator->trans('Domingo');
                            break;
                        case 2:
                            $conexiones[$f][$c] = $this->translator->trans('Lunes');
                            break;
                        case 3:
                            $conexiones[$f][$c] = $this->translator->trans('Martes');
                            break;
                        case 4:
                            $conexiones[$f][$c] = $this->translator->trans('Miércoles');
                            break;
                        case 5:
                            $conexiones[$f][$c] = $this->translator->trans('Jueves');
                            break;
                        case 6:
                            $conexiones[$f][$c] = $this->translator->trans('Viernes');
                            break;
                        case 7:
                            $conexiones[$f][$c] = $this->translator->trans('Sábado');
                            break;
                        case 8:
                            $conexiones[$f][$c] = 'Total';
                            break;
                    }
                }
            }
            else {

                $h = $c-1;
                $hora1 = $h<=9 ? '0'.$h.':00:00' : $h.':00:00';
                $hora2 = $h<=9 ? '0'.$h.':59:59' : $h.':59:59';

                // Cálculos desde la función de BD
                $query = $em->getConnection()->prepare('SELECT
                                                        fnhoras_conexion(:pempresa_id, :pdesde, :phasta, :phora1, :phora2) as
                                                        resultado;');
                $query->bindValue(':pempresa_id', $empresa_id, \PDO::PARAM_INT);
                $query->bindValue(':pdesde', $desde, \PDO::PARAM_STR);
                $query->bindValue(':phasta', $hasta, \PDO::PARAM_STR);
                $query->bindValue(':phora1', $hora1, \PDO::PARAM_STR);
                $query->bindValue(':phora2', $hora2, \PDO::PARAM_STR);
                $query->execute();
                $r = $query->fetchAll();

                // La respuesta viene formada por las cantidades de registros por día de semana separado por __
                $r_arr = explode("__", $r[0]['resultado']);
                $f = 0;
                $total_hora = 0;
                
                foreach ($r_arr as $r)
                {

                    $f++;
                    $conexiones[$f][$c] = $r;
                    $total_hora += $r;
                    $total += $r;
                    $conexiones[$f][25] = $conexiones[$f][25] + $r;

                    if ($r >= $mayor)
                    {
                        if ($r == $mayor && $mayor > 0)
                        {
                            // Varias celdas mayor
                            $celda_mayor[] = $f.'_'.$c;
                        }
                        else {
                            // Nueva celda mayor
                            $celda_mayor = array($f.'_'.$c);
                        }
                        $mayor = $r;
                    }

                }
                $conexiones[8][$c] = $total_hora;

            }
        }

        // Total de totales
        $conexiones[8][25] = $total;

        return array('conexiones' => $conexiones,
        			 'celda_mayor' => $celda_mayor);

	}

	// Cálculo del reporte Evaluaciones por Módulo de una Empresa en un período determinado
	public function evaluacionesModulo($empresa_id, $pagina_id, $desde, $hasta)
	{

		$em = $this->em;

		/*
			SELECT prl.id AS prl_id, u.codigo AS codigo, u.login AS login, u.nombre AS nombre, u.apellido AS apellido, u.correo_personal AS correo_personal, u.correo_corporativo AS correo_corporativo, u.campo1 AS campo1, u.campo2 AS campo2, u.campo3 AS campo3, u.campo4 AS campo4, 
	(SELECT TO_CHAR(pl.fecha_inicio, 'DD/MM/YYYY') AS fecha_inicio_programa FROM certi_pagina_log pl WHERE pl.usuario_id = u.id AND pl.pagina_id = pr.pagina_id), 
	(SELECT TO_CHAR(pl.fecha_inicio, 'HH:MI AM') AS hora_inicio_programa FROM certi_pagina_log pl WHERE pl.usuario_id = u.id AND pl.pagina_id = pr.pagina_id), 
	pr.nombre AS evaluacion, 
	prl.estado AS estado, prl.nota AS nota, TO_CHAR(prl.fecha_inicio, 'DD/MM/YYYY') AS fecha_inicio_prueba, TO_CHAR(prl.fecha_inicio, 'HH:MI AM') AS hora_inicio_prueba 
FROM certi_prueba_log prl INNER JOIN admin_usuario u ON prl.usuario_id = u.id 
	INNER JOIN certi_prueba pr ON prl.prueba_id = pr.id 
WHERE u.empresa_id = 2 AND pr.pagina_id = 14 AND prl.fecha_inicio BETWEEN '2018-05-04 00:00:00' AND '2018-05-17 14:40:59' 
ORDER BY prl.id ASC;
		*/
		
		// Acumuladores
        $mayor = 0;
        $celda_mayor = array();
        $total = 0;

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
        $conexiones[0][0] = $this->translator->trans('Día/Hora');

        // Etiquetas de horas
        $c = 1;
        for ($h=0; $h<24; $h++)
        {
            $hora = $h<=9 ? '0'.$h : $h;
            $conexiones[0][$c] = $hora.':00';
            $c++;
        }

        $conexiones[0][25] = 'Total';

        // Columna de Total con valor cero
        for ($f=1; $f<=8; $f++)
        {
            $conexiones[$f][25] = 0;
        }

        for ($c=0; $c<=24; $c++)
        {
            if ($c==0)
            {
                for ($f=1; $f<=8; $f++)
                {
                    switch ($f)
                    {
                        case 1:
                            $conexiones[$f][$c] = $this->translator->trans('Domingo');
                            break;
                        case 2:
                            $conexiones[$f][$c] = $this->translator->trans('Lunes');
                            break;
                        case 3:
                            $conexiones[$f][$c] = $this->translator->trans('Martes');
                            break;
                        case 4:
                            $conexiones[$f][$c] = $this->translator->trans('Miércoles');
                            break;
                        case 5:
                            $conexiones[$f][$c] = $this->translator->trans('Jueves');
                            break;
                        case 6:
                            $conexiones[$f][$c] = $this->translator->trans('Viernes');
                            break;
                        case 7:
                            $conexiones[$f][$c] = $this->translator->trans('Sábado');
                            break;
                        case 8:
                            $conexiones[$f][$c] = 'Total';
                            break;
                    }
                }
            }
            else {

                $h = $c-1;
                $hora1 = $h<=9 ? '0'.$h.':00:00' : $h.':00:00';
                $hora2 = $h<=9 ? '0'.$h.':59:59' : $h.':59:59';

                // Cálculos desde la función de BD
                $query = $em->getConnection()->prepare('SELECT
                                                        fnhoras_conexion(:pempresa_id, :pdesde, :phasta, :phora1, :phora2) as
                                                        resultado;');
                $query->bindValue(':pempresa_id', $empresa_id, \PDO::PARAM_INT);
                $query->bindValue(':pdesde', $desde, \PDO::PARAM_STR);
                $query->bindValue(':phasta', $hasta, \PDO::PARAM_STR);
                $query->bindValue(':phora1', $hora1, \PDO::PARAM_STR);
                $query->bindValue(':phora2', $hora2, \PDO::PARAM_STR);
                $query->execute();
                $r = $query->fetchAll();

                // La respuesta viene formada por las cantidades de registros por día de semana separado por __
                $r_arr = explode("__", $r[0]['resultado']);
                $f = 0;
                $total_hora = 0;
                
                foreach ($r_arr as $r)
                {

                    $f++;
                    $conexiones[$f][$c] = $r;
                    $total_hora += $r;
                    $total += $r;
                    $conexiones[$f][25] = $conexiones[$f][25] + $r;

                    if ($r >= $mayor)
                    {
                        if ($r == $mayor && $mayor > 0)
                        {
                            // Varias celdas mayor
                            $celda_mayor[] = $f.'_'.$c;
                        }
                        else {
                            // Nueva celda mayor
                            $celda_mayor = array($f.'_'.$c);
                        }
                        $mayor = $r;
                    }

                }
                $conexiones[8][$c] = $total_hora;

            }
        }

        // Total de totales
        $conexiones[8][25] = $total;

        return array('conexiones' => $conexiones,
        			 'celda_mayor' => $celda_mayor);

	}
}
