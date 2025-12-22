-- Inserciones iniciales para la Academia de Pintura
-- Añade roles y usuarios (admin, daniel, lucas, pool, hugo)
-- Contraseña para todos los usuarios: 'grupo05' (almacenada como MD5)

-- 1) Roles
INSERT INTO `rol` (`codigo_rol`, `nombre_rol`)
SELECT 'ROL-ADM', 'ADMIN' FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `rol` WHERE `codigo_rol` = 'ROL-ADM');
INSERT INTO `rol` (`codigo_rol`, `nombre_rol`)
SELECT 'ROL-ALU', 'ALUMNO' FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `rol` WHERE `codigo_rol` = 'ROL-ALU');
INSERT INTO `rol` (`codigo_rol`, `nombre_rol`)
SELECT 'ROL-PRO', 'PROFESOR' FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `rol` WHERE `codigo_rol` = 'ROL-PRO');

-- 2) Usuarios (tabla `usuarios`)
-- admin
INSERT INTO `usuarios` (`id_usuario`, `codigo_usuario`, `nombre`, `apellido`, `correo`, `telefono`, `rol_codigo`)
SELECT 'USU-000001','USU-000001','Admin','Default','admin@example.com','600000000','ROL-ADM'
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `usuarios` WHERE `correo` = 'admin@example.com');

-- daniel
INSERT INTO `usuarios` (`id_usuario`, `codigo_usuario`, `nombre`, `apellido`, `correo`, `telefono`, `rol_codigo`)
SELECT 'USU-000002','USU-000002','Daniel','Apellido','daniel@example.com','600000001','ROL-PRO'
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `usuarios` WHERE `correo` = 'daniel@example.com');

-- lucas
INSERT INTO `usuarios` (`id_usuario`, `codigo_usuario`, `nombre`, `apellido`, `correo`, `telefono`, `rol_codigo`)
SELECT 'USU-000003','USU-000003','Lucas','Apellido','lucas@example.com','600000002','ROL-PRO'
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `usuarios` WHERE `correo` = 'lucas@example.com');

-- pool
INSERT INTO `usuarios` (`id_usuario`, `codigo_usuario`, `nombre`, `apellido`, `correo`, `telefono`, `rol_codigo`)
SELECT 'USU-000004','USU-000004','Pool','Apellido','pool@example.com','600000003','ROL-PRO'
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `usuarios` WHERE `correo` = 'pool@example.com');

-- hugo
INSERT INTO `usuarios` (`id_usuario`, `codigo_usuario`, `nombre`, `apellido`, `correo`, `telefono`, `rol_codigo`)
SELECT 'USU-000005','USU-000005','Hugo','Apellido','hugo@example.com','600000004','ROL-PRO'
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `usuarios` WHERE `correo` = 'hugo@example.com');

-- 3) Credenciales (tabla `login`) - contraseñas almacenadas como MD5
INSERT INTO `login` (`id_usuario`, `usuario`, `contrasena_hash`)
SELECT 'USU-000001','admin', MD5('grupo05') FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `login` WHERE `usuario` = 'admin');
INSERT INTO `login` (`id_usuario`, `usuario`, `contrasena_hash`)
SELECT 'USU-000002','daniel', MD5('grupo05') FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `login` WHERE `usuario` = 'daniel');
INSERT INTO `login` (`id_usuario`, `usuario`, `contrasena_hash`)
SELECT 'USU-000003','lucas', MD5('grupo05') FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `login` WHERE `usuario` = 'lucas');
INSERT INTO `login` (`id_usuario`, `usuario`, `contrasena_hash`)
SELECT 'USU-000004','pool', MD5('grupo05') FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `login` WHERE `usuario` = 'pool');
INSERT INTO `login` (`id_usuario`, `usuario`, `contrasena_hash`)
SELECT 'USU-000005','hugo', MD5('grupo05') FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `login` WHERE `usuario` = 'hugo');

-- Nuevo alumno: Diego (rol ALUMNO)
INSERT INTO `usuarios` (`id_usuario`, `codigo_usuario`, `nombre`, `apellido`, `correo`, `telefono`, `rol_codigo`)
SELECT 'USU-000006','USU-000006','Diego','Apellido','diego@example.com','600000005','ROL-ALU'
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `usuarios` WHERE `correo` = 'diego@example.com');

INSERT INTO `login` (`id_usuario`, `usuario`, `contrasena_hash`)
SELECT 'USU-000006','diego', MD5('diego123') FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `login` WHERE `usuario` = 'diego');

INSERT INTO `alumno` (`id_usuario`, `fecha_ingreso`, `beca`)
SELECT 'USU-000006', CURDATE(), 'No' FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `alumno` WHERE `id_usuario` = 'USU-000006');

-- 4) Insertar en `profesor` para usuarios con rol PROFESOR (si no existen)
INSERT INTO `profesor` (`id_usuario`, `fecha_contratacion`)
SELECT 'USU-000002', CURDATE() FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `profesor` WHERE `id_usuario` = 'USU-000002');
INSERT INTO `profesor` (`id_usuario`, `fecha_contratacion`)
SELECT 'USU-000003', CURDATE() FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `profesor` WHERE `id_usuario` = 'USU-000003');
INSERT INTO `profesor` (`id_usuario`, `fecha_contratacion`)
SELECT 'USU-000004', CURDATE() FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `profesor` WHERE `id_usuario` = 'USU-000004');
INSERT INTO `profesor` (`id_usuario`, `fecha_contratacion`)
SELECT 'USU-000005', CURDATE() FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `profesor` WHERE `id_usuario` = 'USU-000005');

-- Fin de script

