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
SELECT 'USU-000001','USU-000001','Admin','Default','admin@gmail.com','600000000','ROL-ADM'
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `usuarios` WHERE `correo` = 'admin@gmail.com');

-- daniel (PROFESOR)
INSERT INTO `usuarios` (`id_usuario`, `codigo_usuario`, `nombre`, `apellido`, `correo`, `telefono`, `rol_codigo`)
SELECT 'USU-000002','USU-000002','Daniel','Apellido','daniel@academiapintura.com','600000001','ROL-PRO'
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `usuarios` WHERE `correo` = 'daniel@academiapintura.com');

-- lucas (PROFESOR)
INSERT INTO `usuarios` (`id_usuario`, `codigo_usuario`, `nombre`, `apellido`, `correo`, `telefono`, `rol_codigo`)
SELECT 'USU-000003','USU-000003','Lucas','Apellido','lucas@academiapintura.com','600000002','ROL-PRO'
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `usuarios` WHERE `correo` = 'lucas@academiapintura.com');

-- pool (PROFESOR)
INSERT INTO `usuarios` (`id_usuario`, `codigo_usuario`, `nombre`, `apellido`, `correo`, `telefono`, `rol_codigo`)
SELECT 'USU-000004','USU-000004','Pool','Apellido','pool@academiapintura.com','600000003','ROL-PRO'
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `usuarios` WHERE `correo` = 'pool@academiapintura.com');

-- hugo (PROFESOR)
INSERT INTO `usuarios` (`id_usuario`, `codigo_usuario`, `nombre`, `apellido`, `correo`, `telefono`, `rol_codigo`)
SELECT 'USU-000005','USU-000005','Hugo','Apellido','hugo@academiapintura.com','600000004','ROL-PRO'
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `usuarios` WHERE `correo` = 'hugo@academiapintura.com');

-- ana (PROFESOR)
INSERT INTO `usuarios` (`id_usuario`, `codigo_usuario`, `nombre`, `apellido`, `correo`, `telefono`, `rol_codigo`)
SELECT 'USU-000006','USU-000006','Ana','García','ana@academiapintura.com','600000006','ROL-PRO'
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `usuarios` WHERE `correo` = 'ana@academiapintura.com');

-- pedro (PROFESOR)
INSERT INTO `usuarios` (`id_usuario`, `codigo_usuario`, `nombre`, `apellido`, `correo`, `telefono`, `rol_codigo`)
SELECT 'USU-000007','USU-000007','Pedro','López','pedro@academiapintura.com','600000007','ROL-PRO'
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `usuarios` WHERE `correo` = 'pedro@academiapintura.com');

-- diego (ALUMNO) - ID CORREGIDO
INSERT INTO `usuarios` (`id_usuario`, `codigo_usuario`, `nombre`, `apellido`, `correo`, `telefono`, `rol_codigo`)
SELECT 'USU-000008','USU-000008','Diego','Apellido','diego@gmail.com','600000005','ROL-ALU'
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `usuarios` WHERE `correo` = 'diego@gmail.com');

-- laura (ALUMNA)
INSERT INTO `usuarios` (`id_usuario`, `codigo_usuario`, `nombre`, `apellido`, `correo`, `telefono`, `rol_codigo`)
SELECT 'USU-000009','USU-000009','Laura','Martínez','laura@academiapintura.com','600000008','ROL-ALU'
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `usuarios` WHERE `correo` = 'laura@academiapintura.com');

-- carlos (ALUMNO)
INSERT INTO `usuarios` (`id_usuario`, `codigo_usuario`, `nombre`, `apellido`, `correo`, `telefono`, `rol_codigo`)
SELECT 'USU-000010','USU-000010','Carlos','Ruiz','carlos@academiapintura.com','600000009','ROL-ALU'
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `usuarios` WHERE `correo` = 'carlos@academiapintura.com');

-- 3) Credenciales (tabla `login`) - contraseñas almacenadas como MD5
-- TODOS con contraseña 'grupo05'
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
INSERT INTO `login` (`id_usuario`, `usuario`, `contrasena_hash`)
SELECT 'USU-000006','ana', MD5('grupo05') FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `login` WHERE `usuario` = 'ana');
INSERT INTO `login` (`id_usuario`, `usuario`, `contrasena_hash`)
SELECT 'USU-000007','pedro', MD5('grupo05') FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `login` WHERE `usuario` = 'pedro');
INSERT INTO `login` (`id_usuario`, `usuario`, `contrasena_hash`)
SELECT 'USU-000008','diego', MD5('grupo05') FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `login` WHERE `usuario` = 'diego');
INSERT INTO `login` (`id_usuario`, `usuario`, `contrasena_hash`)
SELECT 'USU-000009','laura', MD5('grupo05') FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `login` WHERE `usuario` = 'laura');
INSERT INTO `login` (`id_usuario`, `usuario`, `contrasena_hash`)
SELECT 'USU-000010','carlos', MD5('grupo05') FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `login` WHERE `usuario` = 'carlos');

-- 4) Insertar en `profesor` para usuarios con rol PROFESOR
INSERT INTO `profesor` (`id_usuario`, `fecha_contratacion`)
SELECT 'USU-000002', CURDATE() FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `profesor` WHERE `id_usuario` = 'USU-000002');
INSERT INTO `profesor` (`id_usuario`, `fecha_contratacion`)
SELECT 'USU-000003', CURDATE() FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `profesor` WHERE `id_usuario` = 'USU-000003');
INSERT INTO `profesor` (`id_usuario`, `fecha_contratacion`)
SELECT 'USU-000004', CURDATE() FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `profesor` WHERE `id_usuario` = 'USU-000004');
INSERT INTO `profesor` (`id_usuario`, `fecha_contratacion`)
SELECT 'USU-000005', CURDATE() FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `profesor` WHERE `id_usuario` = 'USU-000005');
INSERT INTO `profesor` (`id_usuario`, `fecha_contratacion`)
SELECT 'USU-000006', CURDATE() FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `profesor` WHERE `id_usuario` = 'USU-000006');
INSERT INTO `profesor` (`id_usuario`, `fecha_contratacion`)
SELECT 'USU-000007', CURDATE() FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `profesor` WHERE `id_usuario` = 'USU-000007');

-- 5) Insertar en `alumno` para usuarios con rol ALUMNO
INSERT INTO `alumno` (`id_usuario`, `fecha_ingreso`, `beca`)
SELECT 'USU-000008', CURDATE(), 'No' FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `alumno` WHERE `id_usuario` = 'USU-000008');
INSERT INTO `alumno` (`id_usuario`, `fecha_ingreso`, `beca`)
SELECT 'USU-000009', CURDATE(), 'No' FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `alumno` WHERE `id_usuario` = 'USU-000009');
INSERT INTO `alumno` (`id_usuario`, `fecha_ingreso`, `beca`)
SELECT 'USU-000010', CURDATE(), 'No' FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `alumno` WHERE `id_usuario` = 'USU-000010');

-- 6) Aulas (crear aulas para las asignaturas)
-- Aula para Cerámica - Lucas (AULA-A-001)
INSERT INTO `aulas` (`codigo_aula`, `capacidad`, `piso`, `equipamiento`, `estado`)
SELECT 'AULA-A-001', 25, 1, 'Mesas de trabajo, Estanterías para materiales, Hornos de cerámica', 'Activa' FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `aulas` WHERE `codigo_aula` = 'AULA-A-001');

-- Aula para Dibujo Técnico - Pool (AULA-A-002)
INSERT INTO `aulas` (`codigo_aula`, `capacidad`, `piso`, `equipamiento`, `estado`)
SELECT 'AULA-A-002', 25, 1, 'Mesas de dibujo, Iluminación natural, Reglas y escuadras', 'Activa' FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `aulas` WHERE `codigo_aula` = 'AULA-A-002');

-- Aula para Realismo - Hugo (AULA-A-003)
INSERT INTO `aulas` (`codigo_aula`, `capacidad`, `piso`, `equipamiento`, `estado`)
SELECT 'AULA-A-003', 25, 2, 'Easels, Pizarras, Materiales de pintura, Modelos para dibujo', 'Activa' FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `aulas` WHERE `codigo_aula` = 'AULA-A-003');

-- Aula para Diseño - Daniel (AULA-A-004)
INSERT INTO `aulas` (`codigo_aula`, `capacidad`, `piso`, `equipamiento`, `estado`)
SELECT 'AULA-A-004', 25, 2, 'Computadoras, Software de diseño (Adobe Creative Suite)', 'Activa' FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `aulas` WHERE `codigo_aula` = 'AULA-A-004');

-- 7) Asignaturas (asociadas con profesores)
-- Cerámica - Lucas
INSERT INTO `asignaturas` (`codigo_asignatura`, `nombre_asignatura`, `horas_semanales`, `descripcion`, `id_profesor`, `id_aula`, `estado`)
SELECT 'ASIG-001', 'Cerámica', 4, 'Técnicas de cerámica y modelado', 
  (SELECT `id_profesor` FROM `profesor` WHERE `id_usuario` = 'USU-000003' LIMIT 1),
  (SELECT `id_aula` FROM `aulas` WHERE `codigo_aula` = 'AULA-A-001' LIMIT 1),
  'Activa' FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `asignaturas` WHERE `nombre_asignatura` = 'Cerámica');

-- Dibujo Técnico - Pool
INSERT INTO `asignaturas` (`codigo_asignatura`, `nombre_asignatura`, `horas_semanales`, `descripcion`, `id_profesor`, `id_aula`, `estado`)
SELECT 'ASIG-002', 'Dibujo Técnico', 3, 'Fundamentos de dibujo técnico e industrial', 
  (SELECT `id_profesor` FROM `profesor` WHERE `id_usuario` = 'USU-000004' LIMIT 1),
  (SELECT `id_aula` FROM `aulas` WHERE `codigo_aula` = 'AULA-A-002' LIMIT 1),
  'Activa' FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `asignaturas` WHERE `nombre_asignatura` = 'Dibujo Técnico');

-- Realismo - Hugo
INSERT INTO `asignaturas` (`codigo_asignatura`, `nombre_asignatura`, `horas_semanales`, `descripcion`, `id_profesor`, `id_aula`, `estado`)
SELECT 'ASIG-003', 'Realismo', 4, 'Técnicas de pintura realista y figurativa', 
  (SELECT `id_profesor` FROM `profesor` WHERE `id_usuario` = 'USU-000005' LIMIT 1),
  (SELECT `id_aula` FROM `aulas` WHERE `codigo_aula` = 'AULA-A-003' LIMIT 1),
  'Activa' FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `asignaturas` WHERE `nombre_asignatura` = 'Realismo');

-- Diseño - Daniel
INSERT INTO `asignaturas` (`codigo_asignatura`, `nombre_asignatura`, `horas_semanales`, `descripcion`, `id_profesor`, `id_aula`, `estado`)
SELECT 'ASIG-004', 'Diseño', 3, 'Principios de diseño gráfico y digital', 
  (SELECT `id_profesor` FROM `profesor` WHERE `id_usuario` = 'USU-000002' LIMIT 1),
  (SELECT `id_aula` FROM `aulas` WHERE `codigo_aula` = 'AULA-A-004' LIMIT 1),
  'Activa' FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `asignaturas` WHERE `nombre_asignatura` = 'Diseño');