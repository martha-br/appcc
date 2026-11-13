USE appcc;

INSERT INTO equipos (nombre, ubicacion, temp_min, temp_max) VALUES
('Cámara frigorífica 1', 'Almacén', 0.00, 4.00),
('Congelador 1', 'Almacén', -25.00, -18.00),
('Nevera de producción', 'Cocina', 0.00, 5.00);

INSERT INTO registros_temperatura (equipo_id, temperatura, fecha_hora, operario, observaciones) VALUES
(1,   2.50, '2026-11-09 08:00:00', 'Ana',   NULL),
(1,   3.10, '2026-11-09 16:00:00', 'Luis',  NULL),
(1,   6.80, '2026-11-10 08:15:00', 'Ana',   'Puerta abierta durante la recepción de mercancía. Se cierra y se repite la medición.'),
(1,   3.40, '2026-11-10 08:50:00', 'Ana',   'Temperatura recuperada tras cerrar la puerta.'),
(2, -20.50, '2026-11-09 08:05:00', 'Ana',   NULL),
(2, -19.80, '2026-11-10 08:10:00', 'Luis',  NULL),
(2, -15.20, '2026-11-11 08:00:00', 'Luis',  'Fallo en el compresor. Se avisa a mantenimiento y se traslada el producto.'),
(2, -21.00, '2026-11-12 08:00:00', 'Ana',   'Equipo reparado por mantenimiento.'),
(3,   4.20, '2026-11-09 09:00:00', 'Marta', NULL),
(3,   5.90, '2026-11-11 09:10:00', 'Marta', 'Producto recién introducido. Se vigila la evolución.'),
(3,   4.00, '2026-11-12 09:00:00', 'Luis',  NULL),
(3,   3.80, '2026-11-13 09:00:00', 'Marta', NULL);