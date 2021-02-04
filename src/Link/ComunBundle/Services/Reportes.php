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
    

    public function historicoAprobados(){
        $em = $this->em;
        $query = $em->getConnection()->prepare('SELECT * FROM view_historico_aprobados');
        $query->execute();
        return $query->fetchAll();
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

    public function usuariosConectados($pempresa_id,$pusuario_id)
    {
        $em = $this->em;

        $query = $em->getConnection()->prepare('SELECT
                                                fnusuarios_conectados(:re, :pempresa_id, :pusuario_id) as
                                                resultado; fetch all from re;');
        $re = 're';
        $query->bindValue(':re', $re, \PDO::PARAM_STR);
        $query->bindValue(':pempresa_id', $pempresa_id, \PDO::PARAM_INT);
         $query->bindValue(':pusuario_id', $pusuario_id, \PDO::PARAM_INT);
        $query->execute();
        $rs = $query->fetchAll();

        return $rs;
    }

	// Cálculo del reporte Horas de Conexión por Empresa en un período determinado
	public function horasConexion($empresa_id, $desde, $hasta, $yml)
	{

		$em = $this->em;
        $empresa = $em->getRepository('LinkComunBundle:AdminEmpresa')->find($empresa_id);
		$timeZoneEmpresa = ($empresa->getZonaHoraria())? $empresa->getZonaHoraria()->getNombre():$yml['parameters']['time_zone']['default'];

        // Acumuladores
        $columna_mayor = 0;
        $fila_mayor = 0;
        $columnas_mayores = array();
        $filas_mayores = array();
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
                //echo "Hr1: ".$hora1." Hr2: ".$hora2,"\n";
                //Objeto DateTime con la hora llevada al uso horario de la empresa
                $hora1 = new \DateTime(date('H:i:s',strtotime($hora1)),new \DateTimeZone($timeZoneEmpresa));
                $hora2 = new \DateTime(date('H:i:s',strtotime($hora2)),new \DateTimeZone($timeZoneEmpresa));
                //Transformar hora1 y hora2 al time zone por defecto (UTC)
                $hora1->setTimeZone(new \DateTimeZone($yml['parameters']['time_zone']['default']));
                $hora2->setTimeZone(new \DateTimeZone($yml['parameters']['time_zone']['default']));
                //preparamos la variable que se pasara a la funcion de base de datos
                $hora1 = $hora1->format('H:i:s');
                $hora2 = $hora2->format('H:i:s');
                //echo "Hr1: ".$hora1." Hr2: ".$hora2."\n";
                //exit();


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

                }
                $conexiones[8][$c] = $total_hora;

                if ($total_hora >= $columna_mayor)
                {
                	if ($total_hora == $columna_mayor && $columna_mayor > 0)
                	{
                		// Varias columnas mayores
                		$columnas_mayores[] = $c;
                	}
                	else {
                		// Nueva columna mayor
                		$columnas_mayores = array($c);
                	}
                	$columna_mayor = $total_hora;
                }

            }
        }

        // Determinar las filas mayores
        for ($f=1; $f<=7; $f++)
        {
        	if ($conexiones[$f][25] >= $fila_mayor)
            {
            	if ($conexiones[$f][25] == $fila_mayor && $fila_mayor > 0)
            	{
            		// Varias filas mayores
            		$filas_mayores[] = $f;
            	}
            	else {
            		// Nueva fila mayor
            		$filas_mayores = array($f);
            	}
            	$fila_mayor = $conexiones[$f][25];
            }
        }

        // Total de totales
        $conexiones[8][25] = $total;

        ///dispositivos
        $query = $em->getConnection()->prepare('SELECT
                                                        fnconexiones_dispositivos(:pdesde,:phasta)');
        $query->bindValue(':pdesde', $desde, \PDO::PARAM_STR);
        $query->bindValue(':phasta', $hasta, \PDO::PARAM_STR);
        $query->execute();
        $r = $query->fetchAll();
        $dispositivos = json_decode($r[0]['fnconexiones_dispositivos'],true);
        return array('conexiones' => $conexiones,
        			 'columnas_mayores' => $columnas_mayores,
        			 'filas_mayores' => $filas_mayores,
                     'reporte_dispositivos'=>$dispositivos);

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
                                      'activo' => $r['activo']? 'Sí':'No',
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
	        							  'activo' => $r['activo']? 'Sí':'No',
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
    public function interaccionMuro($empresa_id, $pagina_id){


        $em = $this->em;

        // Cálculos desde la función de BD
        $query = $em->getConnection()->prepare('SELECT
                                                fninteraccion_muro(:re, :pempresa_id, :ppagina_id) as
                                                resultado; fetch all from re;');
        $re = 're';
        $query->bindValue(':re', $re, \PDO::PARAM_STR);
        $query->bindValue(':pempresa_id', $empresa_id, \PDO::PARAM_INT);
        $query->bindValue(':ppagina_id', $pagina_id, \PDO::PARAM_STR);
        $query->execute();
        $rs = $query->fetchAll();

        return $rs;

    }

    // Mensajes de espacio colaborativo por participantes en una pagina
    public function interaccionColaborativo($empresa_id, $pagina_id, $tema_id){


        $em = $this->em;

        // Cálculos desde la función de BD
        $query = $em->getConnection()->prepare('SELECT
                                                fninteraccion_espacio_colaborativo(:re, :pempresa_id, :ppagina_id, :pforo_id) as
                                                resultado; fetch all from re;');
        $re = 're';
        $query->bindValue(':re', $re, \PDO::PARAM_STR);
        $query->bindValue(':pempresa_id', $empresa_id, \PDO::PARAM_INT);
        $query->bindValue(':ppagina_id', $pagina_id, \PDO::PARAM_INT);
        $query->bindValue(':pforo_id', $tema_id, \PDO::PARAM_INT);
        $query->execute();
        $rs = $query->fetchAll();



        return $rs;

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
        $resultados['desde_activos'] = (int) $r_arr[5];
        $resultados['desde_inactivos'] = (int) $r_arr[6];
        $resultados['week_before_activos'] = (int) $r_arr[0];
        $resultados['week_before_inactivos'] = (int) $r_arr[1];
        $resultados['week_before_no_iniciados'] = (int) $r_arr[2];
        $resultados['week_before_en_curso'] = (int) $r_arr[3];
        $resultados['week_before_aprobados'] = (int) $r_arr[4];
        $resultados['desde_total'] = $resultados['desde_activos'] + $resultados['desde_inactivos'];
        $resultados['week_before_total1'] = $resultados['week_before_activos'] + $resultados['week_before_inactivos'];
        $resultados['week_before_total2'] = $resultados['week_before_no_iniciados'] + $resultados['week_before_en_curso'] + $resultados['week_before_aprobados'];
        $resultados['week_before_total3'] = $resultados['week_before_inactivos'] + $resultados['week_before_no_iniciados'] + $resultados['week_before_en_curso'] + $resultados['week_before_aprobados'];

        $desde_inactivos_pct = $resultados['desde_total'] != 0 ? ($resultados['desde_inactivos']/$resultados['desde_total'])*100 : '-';
        $resultados['desde_inactivos_pct'] = $desde_inactivos_pct != '-' ? number_format($desde_inactivos_pct, 0) : $desde_inactivos_pct;

        $desde_activos_pct = $resultados['desde_total'] != 0 ? ($resultados['desde_activos']/$resultados['desde_total'])*100 : '-';
        $resultados['desde_activos_pct'] = $desde_activos_pct != '-' ? number_format($desde_activos_pct, 0) : $desde_activos_pct;

        $resultados['desde_total_pct'] = $resultados['desde_total'] != 0 ? 100 : '-';

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
        $resultados['hasta_activos'] = (int) $r_arr[5];
        $resultados['hasta_inactivos'] = (int) $r_arr[6];
        $resultados['now_activos'] = (int) $r_arr[0];
        $resultados['now_inactivos'] = (int) $r_arr[1];
        $resultados['now_no_iniciados'] = (int) $r_arr[2];
        $resultados['now_en_curso'] = (int) $r_arr[3];
        $resultados['now_aprobados'] = (int) $r_arr[4];
        $resultados['hasta_total'] = $resultados['hasta_activos'] + $resultados['hasta_inactivos'];
        $resultados['now_total1'] = $resultados['now_activos'] + $resultados['now_inactivos'];
        $resultados['now_total2'] = $resultados['now_no_iniciados'] + $resultados['now_en_curso'] + $resultados['now_aprobados'];
        $resultados['now_total3'] = $resultados['now_inactivos'] + $resultados['now_no_iniciados'] + $resultados['now_en_curso'] + $resultados['now_aprobados'];

        $hasta_inactivos_pct = $resultados['hasta_total'] != 0 ? ($resultados['hasta_inactivos']/$resultados['hasta_total'])*100 : '-';
        $resultados['hasta_inactivos_pct'] = $hasta_inactivos_pct != '-' ? number_format($hasta_inactivos_pct, 0) : $hasta_inactivos_pct;

        $hasta_activos_pct = $resultados['hasta_total'] != 0 ? ($resultados['hasta_activos']/$resultados['hasta_total'])*100 : '-';
        $resultados['hasta_activos_pct'] = $hasta_activos_pct != '-' ? number_format($hasta_activos_pct, 0) : $hasta_activos_pct;

        $resultados['hasta_total_pct'] = $resultados['hasta_total'] != 0 ? 100 : '-';

        $now_inactivos_pct = $resultados['now_total1'] != 0 ? ($resultados['now_inactivos']/$resultados['now_total1'])*100 : '-';
        $resultados['now_inactivos_pct'] = $now_inactivos_pct != '-' ? number_format($now_inactivos_pct, 0) : $now_inactivos_pct;

        $now_activos_pct = $resultados['now_total1'] != 0 ? ($resultados['now_activos']/$resultados['now_total1'])*100 : '-';
        $resultados['now_activos_pct'] = $now_activos_pct != '-' ? number_format($now_activos_pct, 0) : $now_activos_pct;

        $resultados['now_total1_pct'] = $resultados['now_total1'] != 0 ? 100 : '-';

		return $resultados;

	}

    // Cálculo del reporte Evaluaciones por Módulo de una Empresa en un período determinado
    public function participantesAprobados($empresa_id, $paginas_id,$desde,$hasta)
    {

        $em = $this->em;
        $listado = array();
        $paginas = array();
        $participantes = array();
        $bgColors = array('c5d9f1', 'f2dcdb', 'd8e4bc', 'ccc0da', '92cddc', 'fcd5b4', 'ddd9c4', 'd9d9d9', 'b8cce4', 'e6b8b7');

        $colorIndex = 0;

        foreach ($paginas_id as $pagina_id)
        {

            $query = $em->getConnection()->prepare('SELECT
                                                    fnlistado_aprobados(:re, :ppagina_id, :pempresa_id, :pfecha_inicio, :pfecha_fin) as
                                                    resultado; fetch all from re;');
            $re = 're';
            $query->bindValue(':re', $re, \PDO::PARAM_STR);
            $query->bindValue(':ppagina_id', $pagina_id, \PDO::PARAM_INT);
            $query->bindValue(':pempresa_id', $empresa_id, \PDO::PARAM_INT);
            $query->bindValue(':pfecha_inicio', $desde, \PDO::PARAM_STR);
            $query->bindValue(':pfecha_fin', $hasta, \PDO::PARAM_STR);
            $query->execute();
            $rs = $query->fetchAll();

            $pagina = $em->getRepository('LinkComunBundle:CertiPagina')->find($pagina_id);
            $paginas[$pagina_id] = array('nombre' => $pagina->getNombre(),
                                         'bgcolor' => $bgColors[$colorIndex],
                                         'total' => count($rs));

            $colorIndex++;
            $mod = $colorIndex % 10; // Décimo color
            $colorIndex = $mod == 0 ? 0 : $colorIndex;


            $i = 0;

            foreach ($rs as $r)
            {

                $i++;

                if (!array_key_exists($r['id'], $participantes))
                {
                    $participantes[$r['id']] = array('id' => $r['id'],
                                                     'codigo' => $r['codigo'],
                                                     'login' => $r['login'],
                                                     'nombre' => $r['nombre'],
                                                     'apellido' => $r['apellido'],
                                                     'fecha_registro' => $r['fecha_registro'],
                                                     'correo' => trim($r['correo']) ? trim($r['correo']) : trim($r['correo2']),
                                                     'activo' => $r['activo'] ? 'Sí' : 'No',
                                                     'logueado' => $r['logueado']>0 ? 'Sí' : 'No',
                                                     'pais' => $r['pais'],
                                                     'nivel' => $r['nivel'],
                                                     'campo1' => $r['campo1'],
                                                     'campo2' => $r['campo2'],
                                                     'campo3' => $r['campo3'],
                                                     'campo4' => $r['campo4'],
                                                     'paginas' => array($pagina_id => array('id' => $pagina_id,
                                                                                            'promedio' => $r['promedio'],
                                                                                            'fecha_inicio' => $r['fecha_inicio_programa'],
                                                                                            'hora_inicio' => $r['hora_inicio_programa'],
                                                                                            'fecha_fin' => $r['fecha_fin_programa'],
                                                                                            'hora_fin' => $r['hora_fin_programa'])));
                }
                else {
                    $participantes[$r['id']]['paginas'][$pagina_id] = array('id' => $pagina_id,
                                                                            'promedio' => $r['promedio'],
                                                                            'fecha_inicio' => $r['fecha_inicio_programa'],
                                                                            'hora_inicio' => $r['hora_inicio_programa'],
                                                                            'fecha_fin' => $r['fecha_fin_programa'],
                                                                            'hora_fin' => $r['hora_fin_programa']);
                }

            }

        }

        $listado = array('participantes' => $participantes,
                         'paginas' => $paginas);

        return $listado;

    }

    // Interacciones del usuario con los programas asignados
    public function detalleParticipanteProgramas($usuario_id, $empresa_id, $nivel_id, $yml)
    {

        $em = $this->em;
        $resultados = array();

        // INGRESOS AL SISTEMA
        $query = $em->getConnection()->prepare('SELECT
                                                fningresos_sistema(:pusuario_id) as
                                                resultado;');
        $query->bindValue(':pusuario_id', $usuario_id, \PDO::PARAM_INT);
        $query->execute();
        $r = $query->fetchAll();

        // La respuesta viene separada por __
        $r_arr = explode("__", $r[0]['resultado']);
        $resultados['ingresos']['primeraConexion'] = $r_arr[0];
        $resultados['ingresos']['ultimaConexion'] = $r_arr[1];
        $resultados['ingresos']['cantidadConexiones'] = (int) $r_arr[2];
        $resultados['ingresos']['promedioConexion'] = $r_arr[3];
        $resultados['ingresos']['noIniciados'] = (int) $r_arr[4];
        $resultados['ingresos']['enCurso'] = (int) $r_arr[5];
        $resultados['ingresos']['finalizados'] = (int) $r_arr[6];

        //obtenemos los programas asociados al usuario
        $query =  $em->createQuery('SELECT p FROM LinkComunBundle:CertiPagina p
                                    INNER JOIN LinkComunBundle:CertiPaginaEmpresa pe WITH p.id = pe.pagina
                                    INNER JOIN LinkComunBundle:CertiNivelPagina np WITH pe.id = np.paginaEmpresa
                                    WHERE np.nivel = :nivel_id
                                        AND pe.empresa = :empresa_id
                                        AND p.pagina IS NULL
                                    ORDER BY pe.orden ASC')
                     ->setParameters(array('nivel_id' => $nivel_id,
                                           'empresa_id' => $empresa_id));
        $programas = $query->getResult();

        $programas_arr = array();

        foreach ($programas as $programa)
        {

            $programa_log = $em->getRepository('LinkComunBundle:CertiPaginaLog')->findOneBy(array('pagina' => $programa->getId(),
                                                                                                  'usuario' => $usuario_id));

            // Módulos
            $query =  $em->createQuery('SELECT pe FROM LinkComunBundle:CertiPaginaEmpresa pe
                                        JOIN pe.pagina p
                                        WHERE pe.empresa = :empresa_id
                                            AND p.pagina = :pagina_id
                                        ORDER BY pe.orden ASC')
                         ->setParameters(array('empresa_id' => $empresa_id,
                                               'pagina_id' => $programa->getId()));
            $modulos = $query->getResult();

            $modulos_arr = array();

            foreach ($modulos as $modulo)
            {

                $modulo_log = $em->getRepository('LinkComunBundle:CertiPaginaLog')->findOneBy(array('pagina' => $modulo->getPagina()->getId(),
                                                                                                    'usuario' => $usuario_id));

                // Materias
                $query =  $em->createQuery('SELECT pe FROM LinkComunBundle:CertiPaginaEmpresa pe
                                            JOIN pe.pagina p
                                            WHERE pe.empresa = :empresa_id
                                                AND p.pagina = :pagina_id
                                            ORDER BY pe.orden ASC')
                             ->setParameters(array('empresa_id' => $empresa_id,
                                                   'pagina_id' => $modulo->getPagina()->getId()));
                $materias = $query->getResult();

                $materias_asignadas = count($materias);
                $materias_vistas = 0;
                $lecciones_asignadas = 0;
                $lecciones_vistas = 0;
                $evaluaciones_materias = array();
                foreach ($materias as $materia)
                {

                    $materia_log = $em->getRepository('LinkComunBundle:CertiPaginaLog')->findOneBy(array('pagina' => $materia->getPagina()->getId(),
                                                                                                         'usuario' => $usuario_id,
                                                                                                         'estatusPagina' => $yml['parameters']['estatus_pagina']['completada']));
                    if ($materia_log)
                    {
                        $materias_vistas++;
                    }

                    // Lecciones
                    $query =  $em->createQuery('SELECT pe FROM LinkComunBundle:CertiPaginaEmpresa pe
                                                JOIN pe.pagina p
                                                WHERE pe.empresa = :empresa_id
                                                    AND p.pagina = :pagina_id
                                                ORDER BY pe.orden ASC')
                                 ->setParameters(array('empresa_id' => $empresa_id,
                                                       'pagina_id' => $materia->getPagina()->getId()));
                    $lecciones = $query->getResult();

                    $lecciones_asignadas += count($lecciones);
                    foreach ($lecciones as $leccion)
                    {
                        $leccion_log = $em->getRepository('LinkComunBundle:CertiPaginaLog')->findOneBy(array('pagina' => $leccion->getPagina()->getId(),
                                                                                                             'usuario' => $usuario_id,
                                                                                                             'estatusPagina' => $yml['parameters']['estatus_pagina']['completada']));
                        if ($leccion_log)
                        {
                            $lecciones_vistas++;
                        }
                    }

                    // Evaluación de la materia
                    $evaluaciones_materias[] = $this->statusEvaluacion($materia->getPagina()->getId(), $usuario_id);

                }

                // Evaluación del módulo
                $evaluacion = $this->statusEvaluacion($modulo->getPagina()->getId(), $usuario_id);

                $modulos_arr[] = array('id' => $modulo->getPagina()->getId(),
                                       'nombre' => $modulo->getPagina()->getNombre(),
                                       'avance' => $modulo_log ? number_format($modulo_log->getPorcentajeAvance(), 0) : 0,
                                       'materias' => $materias_vistas.'/'.$materias_asignadas,
                                       'lecciones' => $lecciones_vistas.'/'.$lecciones_asignadas,
                                       'evaluacion' => $evaluacion,
                                       'evaluaciones_materias' => $evaluaciones_materias);

            }
            $status_programa = ($programa_log)? $em->getRepository('LinkComunBundle:CertiEstatusPagina')->find($programa_log->getEstatusPagina()->getId()):null;
            $fecha_inicio = ($programa_log)? $this->translator->trans('Inicio').': '.$programa_log->getFechaInicio()->format('d-m-Y').' ':'';
            $fecha_fin = ($programa_log)? ($programa_log->getFechaFin())? ', '.$this->translator->trans('Fin').': '.$programa_log->getFechaFin()->format('d-m-Y'):'':'';
            $status_programa = (is_null($status_programa))? $this->translator->trans('No iniciado'):$status_programa->getNombre().', ';
            $programas_arr[] = array('id'        => $programa->getId(),
                                     'nombre'    => $programa->getNombre().', '.$this->translator->trans('Estatus').': '.$status_programa.$fecha_inicio.$fecha_fin,
                                     'avance'    => $programa_log ? number_format($programa_log->getPorcentajeAvance(), 0) : 0,
                                     'modulos'   => $modulos_arr);

        }

        $resultados['programas'] = $programas_arr;

        return $resultados;

    }

    public function statusEvaluacion($pagina_id, $usuario_id)
    {

        $em = $this->em;
        $evaluacion = array();

        $prueba = $em->getRepository('LinkComunBundle:CertiPrueba')->findOneByPagina($pagina_id);

        if (!$prueba)
        {
            $evaluacion = array('tiene' => 0,
                                'nombre' => '',
                                'status' => $this->translator->trans('No tiene evaluación'),
                                'class' => '',
                                'clase' => '',
                                'nota' => '');
        }
        else {
            $prueba_log = $em->getRepository('LinkComunBundle:CertiPruebaLog')->findOneBy(array('prueba' => $prueba->getId(),
                                                                                                'usuario' => $usuario_id),
                                                                                          array('id' => 'DESC'));
            if (!$prueba_log)
            {
                $evaluacion = array('tiene' => 1,
                                    'nombre' => $prueba->getNombre(),
                                    'status' => $this->translator->trans('Aún sin presentar la evaluación'),
                                    'class' => 'warning',
                                    'clase' => 'naranja',
                                    'nota' => 0);
            }
            else {
                $evaluacion = array('tiene' => 1,
                                    'nombre' => $prueba->getNombre(),
                                    'status' => $prueba_log->getEstado() ? $prueba_log->getEstado() : $this->translator->trans('En curso'),
                                    'class' => $prueba_log->getEstado() ? $prueba_log->getEstado()=='APROBADO' ? 'success' : 'danger' : 'warning',
                                    'clase' => $prueba_log->getEstado() ? $prueba_log->getEstado()=='APROBADO' ? 'verde' : 'rojo' : 'naranja',
                                    'nota' => $prueba_log->getEstado() ? number_format($prueba_log->getNota(), 0) : 0);
            }
        }

        return $evaluacion;

    }

}
