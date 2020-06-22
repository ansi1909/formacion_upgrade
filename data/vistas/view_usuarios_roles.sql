CREATE VIEW view_usuarios_roles as(
  SELECT
    u.id as id,
    u.nombre as nombre,
    u.apellido as apellido,
    u.login as login,
    u.activo as activo,
    e.id as empresa_id,
    e.nombre as empresa,
    n.id as nivel_id,
    n.nombre as nivel,
      (SELECT COUNT(ru.id) FROM admin_rol_usuario ru WHERE ru.rol_id = 1 AND ru.usuario_id = u.id ) as rol_administrador,
      (SELECT COUNT(ru.id) FROM admin_rol_usuario ru WHERE ru.rol_id = 2 AND ru.usuario_id = u.id ) as rol_participante,
      (SELECT COUNT(ru.id) FROM admin_rol_usuario ru WHERE ru.rol_id = 3 AND ru.usuario_id = u.id ) as rol_tutor,
      (SELECT COUNT(ru.id) FROM admin_rol_usuario ru WHERE ru.rol_id = 4 AND ru.usuario_id = u.id ) as rol_reporte,
      (SELECT COUNT(ru.id) FROM admin_rol_usuario ru WHERE ru.rol_id = 5 AND ru.usuario_id = u.id ) as rol_empresa
    FROM admin_usuario u
    LEFT JOIN admin_nivel n ON n.id = u.nivel_id
    LEFT JOIN admin_empresa e ON e.id = u.empresa_id
);