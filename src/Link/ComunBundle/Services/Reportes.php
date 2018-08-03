<?php

namespace Link\ComunBundle\Services;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
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

    public function conexionesUsuario($pempresa_id,$pdesde,$phasta)
    {
        $em = $this->em;

        $query = $em->getConnection()->prepare('SELECT
                                                fnconexion_usuario(:re, :pempresa_id, :pdesde, :phasta) as
                                                resultado; fetch all from re;');
        $re = 're';
        $query->bindValue(':re', $re, \PDO::PARAM_STR);
        $query->bindValue(':pempresa_id', $pempresa_id, \PDO::PARAM_INT);
        $query->bindValue(':pdesde', $pdesde, \PDO::PARAM_STR);
        $query->bindValue(':phasta', $phasta, \PDO::PARAM_STR);
        $query->execute();
        $rs = $query->fetchAll();
        
        return $rs;
    }

    public function avanceProgramas($pempresa_id, $ppagina_id, $pdesde, $phasta)
    {
        $em = $this->em;

        $query = $em->getConnection()->prepare('SELECT
                                                fnavance_programa(:re, :pempresa_id,:ppagina_id ,:pdesde, :phasta) as
                                                resultado; fetch all from re;');
        $re = 're';
        $query->bindValue(':re', $re, \PDO::PARAM_STR);
        $query->bindValue(':pempresa_id', $pempresa_id, \PDO::PARAM_INT);
        $query->bindValue(':ppagina_id', $ppagina_id, \PDO::PARAM_INT);
        $query->bindValue(':pdesde', $pdesde, \PDO::PARAM_STR);
        $query->bindValue(':phasta', $phasta, \PDO::PARAM_STR);
        $query->execute();
        $rs = $query->fetchAll();
        
        return $rs;
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
		
        // Cálculos desde la función de BD
        $query = $em->getConnection()->prepare('SELECT
                                                fnevaluaciones_modulo(:re, :pempresa_id, :ppagina_id, :pdesde, :phasta) as
                                                resultado; fetch all from re;');
        $re = 're';
        $query->bindValue(':re', $re, \PDO::PARAM_STR);
        $query->bindValue(':pempresa_id', $empresa_id, \PDO::PARAM_INT);
        $query->bindValue(':ppagina_id', $pagina_id, \PDO::PARAM_INT);
        $query->bindValue(':pdesde', $desde, \PDO::PARAM_STR);
        $query->bindValue(':phasta', $hasta, \PDO::PARAM_STR);
        $query->execute();
        $rs = $query->fetchAll();

        $listado = array();
        $login = '';
        $i = 0;

        foreach ($rs as $r)
        {

        	$i++;

        	if ($i == 1)
        	{
        		$participante = array('codigo' => $r['codigo'],
        							  'login' => $r['login'],
        							  'nombre' => $r['nombre'],
        							  'apellido' => $r['apellido'],
        							  'correo' => trim($r['correo_personal']) ? trim($r['correo_personal']) : trim($r['correo_corporativo']),
        							  'empresa' => $r['empresa'],
        							  'pais' => $r['pais'],
        							  'nivel' => $r['nivel'],
        							  'fecha_registro' => $r['fecha_registro'],
        							  'campo1' => $r['campo1'],
        							  'campo2' => $r['campo2'],
        							  'campo3' => $r['campo3'],
        							  'campo4' => $r['campo4'],
        							  'fecha_inicio_programa' => $r['fecha_inicio_programa'],
        							  'hora_inicio_programa' => $r['hora_inicio_programa']);
        		$evaluaciones = array();
        		$evaluaciones[] = array('evaluacion' => $r['evaluacion'],
        								'estado' => $r['estado'],
        								'nota' => $r['nota'],
        								'fecha_inicio_prueba' => $r['fecha_inicio_prueba'],
        								'hora_inicio_prueba' => $r['hora_inicio_prueba']);
        	}

        	if ($r['login'] != $login)
        	{

        		$login = $r['login'];

        		if ($i > 1)
        		{
        			$participante['evaluaciones'] = $evaluaciones;
        			$listado[] = $participante;
        			$participante = array('codigo' => $r['codigo'],
	        							  'login' => $r['login'],
	        							  'nombre' => $r['nombre'],
	        							  'apellido' => $r['apellido'],
	        							  'correo' => trim($r['correo_personal']) ? trim($r['correo_personal']) : trim($r['correo_corporativo']),
	        							  'empresa' => $r['empresa'],
        							  	  'pais' => $r['pais'],
        							  	  'nivel' => $r['nivel'],
        							  	  'fecha_registro' => $r['fecha_registro'],
	        							  'campo1' => $r['campo1'],
	        							  'campo2' => $r['campo2'],
	        							  'campo3' => $r['campo3'],
	        							  'campo4' => $r['campo4'],
	        							  'fecha_inicio_programa' => $r['fecha_inicio_programa'],
	        							  'hora_inicio_programa' => $r['hora_inicio_programa']);
        			$evaluaciones = array();
	        		$evaluaciones[] = array('evaluacion' => $r['evaluacion'],
	        								'estado' => $r['estado'],
	        								'nota' => $r['nota'],
	        								'fecha_inicio_prueba' => $r['fecha_inicio_prueba'],
	        								'hora_inicio_prueba' => $r['hora_inicio_prueba']);
        		}

        	}
        	else {
        		$evaluaciones[] = array('evaluacion' => $r['evaluacion'],
        								'estado' => $r['estado'],
        								'nota' => $r['nota'],
        								'fecha_inicio_prueba' => $r['fecha_inicio_prueba'],
        								'hora_inicio_prueba' => $r['hora_inicio_prueba']);
        	}

        	if ($i == count($rs))
        	{
        		$participante['evaluaciones'] = $evaluaciones;
        		$listado[] = $participante;
        	}

        }

        return $listado;

	}

    // Mensajes de muro por participantes en una pagina
    public function interaccionMuro($empresa_id, $pagina_id, $desde, $hasta){


        $em = $this->em;
        
        // Cálculos desde la función de BD
        $query = $em->getConnection()->prepare('SELECT
                                                fninteraccion_muro(:re, :pempresa_id, :ppagina_id, :pdesde, :phasta) as
                                                resultado; fetch all from re;');
        $re = 're';
        $query->bindValue(':re', $re, \PDO::PARAM_STR);
        $query->bindValue(':pempresa_id', $empresa_id, \PDO::PARAM_INT);
        $query->bindValue(':ppagina_id', $pagina_id, \PDO::PARAM_INT);
        $query->bindValue(':pdesde', $desde, \PDO::PARAM_STR);
        $query->bindValue(':phasta', $hasta, \PDO::PARAM_STR);
        $query->execute();
        $rs = $query->fetchAll();

        $listado = array();
        $login = '';
        $i = 0;

        foreach ($rs as $r)
        {

            $i++;

            if ($i == 1)
            {
                $participante = array('codigo' => $r['codigo'],
                                      'login' => $r['login'],
                                      'nombre' => $r['nombre'],
                                      'apellido' => $r['apellido'],
                                      'correo' => trim($r['correo_personal']) ? trim($r['correo_personal']) : trim($r['correo_corporativo']),
                                      'empresa' => $r['empresa'],
                                      'pais' => $r['pais'],
                                      'nivel' => $r['nivel'],
                                      'fecha_registro' => $r['fecha_registro'],
                                      'campo1' => $r['campo1'],
                                      'campo2' => $r['campo2'],
                                      'campo3' => $r['campo3'],
                                      'campo4' => $r['campo4']);
                $muro = array();
                $muro[] = array('mensaje' => $r['mensaje'],
                                'fecha_mensaje' => $r['fecha_mensaje']);
            }

            if ($r['login'] != $login)
            {

                $login = $r['login'];

                if ($i > 1)
                {
                    $participante['muros'] = $muro;
                    $listado[] = $participante;
                    $participante = array('codigo' => $r['codigo'],
                                          'login' => $r['login'],
                                          'nombre' => $r['nombre'],
                                          'apellido' => $r['apellido'],
                                          'correo' => trim($r['correo_personal']) ? trim($r['correo_personal']) : trim($r['correo_corporativo']),
                                          'empresa' => $r['empresa'],
                                          'pais' => $r['pais'],
                                          'nivel' => $r['nivel'],
                                          'fecha_registro' => $r['fecha_registro'],
                                          'campo1' => $r['campo1'],
                                          'campo2' => $r['campo2'],
                                          'campo3' => $r['campo3'],
                                          'campo4' => $r['campo4'],
                                          'fecha_inicio_programa' => $r['fecha_inicio_programa'],
                                          'hora_inicio_programa' => $r['hora_inicio_programa']);
                    $muro = array();
                    $muro[] = array('mensaje' => $r['mensaje'],
                                    'fecha_mensaje' => $r['fecha_mensaje']);
                }

            }
            else {
                $muro[] = array('mensaje' => $r['mensaje'],
                                    'fecha_mensaje' => $r['fecha_mensaje']);
            }

            if ($i == count($rs))
            {
                $participante['muros'] = $muro;
                $listado[] = $participante;
            }

        }

        return $listado;

    }

    // Mensajes de espacio colaborativo por participantes en una pagina
    public function interaccionColaborativo($empresa_id, $pagina_id, $tema_id, $desde, $hasta){


        $em = $this->em;
        
        // Cálculos desde la función de BD
        $query = $em->getConnection()->prepare('SELECT
                                                fninteraccion_espacio_colaborativo(:re, :pempresa_id, :ppagina_id, :pforo_id, :pdesde, :phasta) as
                                                resultado; fetch all from re;');
        $re = 're';
        $query->bindValue(':re', $re, \PDO::PARAM_STR);
        $query->bindValue(':pempresa_id', $empresa_id, \PDO::PARAM_INT);
        $query->bindValue(':ppagina_id', $pagina_id, \PDO::PARAM_INT);
        $query->bindValue(':pforo_id', $tema_id, \PDO::PARAM_INT);
        $query->bindValue(':pdesde', $desde, \PDO::PARAM_STR);
        $query->bindValue(':phasta', $hasta, \PDO::PARAM_STR);
        $query->execute();
        $rs = $query->fetchAll();

        $listado = array();
        $login = '';
        $i = 0;

        foreach ($rs as $r)
        {

            $i++;

            if ($i == 1)
            {
                $participante = array('codigo' => $r['codigo'],
                                      'login' => $r['login'],
                                      'nombre' => $r['nombre'],
                                      'apellido' => $r['apellido'],
                                      'correo' => trim($r['correo_personal']) ? trim($r['correo_personal']) : trim($r['correo_corporativo']),
                                      'empresa' => $r['empresa'],
                                      'pais' => $r['pais'],
                                      'nivel' => $r['nivel'],
                                      'fecha_registro' => $r['fecha_registro'],
                                      'campo1' => $r['campo1'],
                                      'campo2' => $r['campo2'],
                                      'campo3' => $r['campo3'],
                                      'campo4' => $r['campo4']);
                $foro = array();
                $foro[] = array('mensaje' => $r['mensaje'],
                                'fecha_mensaje' => $r['fecha_mensaje']);
            }

            if ($r['login'] != $login)
            {

                $login = $r['login'];

                if ($i > 1)
                {
                    $participante['foro'] = $foro;
                    $listado[] = $participante;
                    $participante = array('codigo' => $r['codigo'],
                                          'login' => $r['login'],
                                          'nombre' => $r['nombre'],
                                          'apellido' => $r['apellido'],
                                          'correo' => trim($r['correo_personal']) ? trim($r['correo_personal']) : trim($r['correo_corporativo']),
                                          'empresa' => $r['empresa'],
                                          'pais' => $r['pais'],
                                          'nivel' => $r['nivel'],
                                          'fecha_registro' => $r['fecha_registro'],
                                          'campo1' => $r['campo1'],
                                          'campo2' => $r['campo2'],
                                          'campo3' => $r['campo3'],
                                          'campo4' => $r['campo4']);
                    $foro = array();
                    $foro[] = array('mensaje' => $r['mensaje'],
                                    'fecha_mensaje' => $r['fecha_mensaje']);
                }

            }
            else {
                $foro[] = array('mensaje' => $r['mensaje'],
                                'fecha_mensaje' => $r['fecha_mensaje']);
            }

            if ($i == count($rs))
            {
                $participante['foro'] = $foro;
                $listado[] = $participante;
            }

        }

        return $listado;

    }

    // Cálculo del reporte Resumen General de Registros
	public function resumenRegistros($empresa_id, $pagina_id, $week_before, $now)
	{

		$em = $this->em;
		$resultados = array();

		// CÁLCULOS DE LOS VALORES DE LA SEMANA ANTERIOR
        $query = $em->getConnection()->prepare('SELECT
                                                fnresumen_general(:pempresa_id, :ppagina_id, :pweek_before) as
                                                resultado;');
        $query->bindValue(':pempresa_id', $empresa_id, \PDO::PARAM_INT);
        $query->bindValue(':ppagina_id', $pagina_id, \PDO::PARAM_INT);
        $query->bindValue(':pweek_before', $week_before, \PDO::PARAM_STR);
        $query->execute();
        $r = $query->fetchAll();

        // La respuesta viene formada por las cantidades de registros por día de semana separado por __
        $r_arr = explode("__", $r[0]['resultado']);
        $resultados['week_before_activos'] = (int) $r_arr[0];
        $resultados['week_before_inactivos'] = (int) $r_arr[1];
        $resultados['week_before_no_iniciados'] = (int) $r_arr[2];
        $resultados['week_before_en_curso'] = (int) $r_arr[3];
        $resultados['week_before_aprobados'] = (int) $r_arr[4];
        $resultados['week_before_total1'] = $resultados['week_before_activos'] + $resultados['week_before_inactivos'];
        $resultados['week_before_total2'] = $resultados['week_before_no_iniciados'] + $resultados['week_before_en_curso'] + $resultados['week_before_aprobados'];
        $resultados['week_before_total3'] = $resultados['week_before_inactivos'] + $resultados['week_before_no_iniciados'] + $resultados['week_before_en_curso'] + $resultados['week_before_aprobados'];
        
        $week_before_inactivos_pct = $resultados['week_before_total1'] != 0 ? ($resultados['week_before_inactivos']/$resultados['week_before_total1'])*100 : '-';
        $resultados['week_before_inactivos_pct'] = $week_before_inactivos_pct != '-' ? number_format($week_before_inactivos_pct, 0) : $week_before_inactivos_pct;
        
        $week_before_activos_pct = $resultados['week_before_total1'] != 0 ? ($resultados['week_before_activos']/$resultados['week_before_total1'])*100 : '-';
        $resultados['week_before_activos_pct'] = $week_before_activos_pct != '-' ? number_format($week_before_activos_pct, 0) : $week_before_activos_pct;

        $resultados['week_before_total1_pct'] = $resultados['week_before_total1'] != 0 ? 100 : '-';

        // CÁLCULOS DE LOS VALORES DE LA FECHA SELECCIONADA
        $query = $em->getConnection()->prepare('SELECT
                                                fnresumen_general(:pempresa_id, :ppagina_id, :pnow) as
                                                resultado;');
        $query->bindValue(':pempresa_id', $empresa_id, \PDO::PARAM_INT);
        $query->bindValue(':ppagina_id', $pagina_id, \PDO::PARAM_INT);
        $query->bindValue(':pnow', $now, \PDO::PARAM_STR);
        $query->execute();
        $r = $query->fetchAll();

        // La respuesta viene formada por las cantidades de registros por día de semana separado por __
        $r_arr = explode("__", $r[0]['resultado']);
        $resultados['now_activos'] = (int) $r_arr[0];
        $resultados['now_inactivos'] = (int) $r_arr[1];
        $resultados['now_no_iniciados'] = (int) $r_arr[2];
        $resultados['now_en_curso'] = (int) $r_arr[3];
        $resultados['now_aprobados'] = (int) $r_arr[4];
        $resultados['now_total1'] = $resultados['now_activos'] + $resultados['now_inactivos'];
        $resultados['now_total2'] = $resultados['now_no_iniciados'] + $resultados['now_en_curso'] + $resultados['now_aprobados'];
        $resultados['now_total3'] = $resultados['now_inactivos'] + $resultados['now_no_iniciados'] + $resultados['now_en_curso'] + $resultados['now_aprobados'];
        
        $now_inactivos_pct = $resultados['now_total1'] != 0 ? ($resultados['now_inactivos']/$resultados['now_total1'])*100 : '-';
        $resultados['now_inactivos_pct'] = $now_inactivos_pct != '-' ? number_format($now_inactivos_pct, 0) : $now_inactivos_pct;
        
        $now_activos_pct = $resultados['now_total1'] != 0 ? ($resultados['now_activos']/$resultados['now_total1'])*100 : '-';
        $resultados['now_activos_pct'] = $now_activos_pct != '-' ? number_format($now_activos_pct, 0) : $now_activos_pct;

        $resultados['now_total1_pct'] = $resultados['now_total1'] != 0 ? 100 : '-';

		return $resultados;

	}
}
