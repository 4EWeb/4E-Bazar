<?php
// admin/admin_functions.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require '../db.php';

// ==================================================================
// MANEJADORES DE PETICIONES (FORMULARIOS)
// ==================================================================

/**
 * Procesa los formularios de la página de gestión de catálogos.
 */
function handle_catalog_requests($pdo) {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;

    // Agregar/Editar Categoría
    if (isset($_POST['save_categoria'])) {
        if (empty($_POST['id_categoria'])) {
            $stmt = $pdo->prepare("INSERT INTO categorias (nombre_categoria) VALUES (?)");
            $stmt->execute([$_POST['nombre_categoria']]);
        } else {
            $stmt = $pdo->prepare("UPDATE categorias SET nombre_categoria = ? WHERE id_categoria = ?");
            $stmt->execute([$_POST['nombre_categoria'], $_POST['id_categoria']]);
        }
        header("Location: gestionar_catalogos.php"); exit();
    }

    // Agregar/Editar Atributo
    if (isset($_POST['save_atributo'])) {
        if (empty($_POST['id_atributo'])) {
            $stmt = $pdo->prepare("INSERT INTO atributos (nombre) VALUES (?)");
            $stmt->execute([$_POST['nombre']]);
        } else {
            $stmt = $pdo->prepare("UPDATE atributos SET nombre = ? WHERE id_atributo = ?");
            $stmt->execute([$_POST['nombre'], $_POST['id_atributo']]);
        }
        header("Location: gestionar_catalogos.php"); exit();
    }

    // Agregar Opción a un Atributo
    if (isset($_POST['add_opcion'])) {
        $stmt = $pdo->prepare("INSERT INTO opciones (id_atributo, valor) VALUES (?, ?)");
        $stmt->execute([$_POST['id_atributo_para_opcion'], $_POST['valor']]);
        header("Location: gestionar_catalogos.php"); exit();
    }
}

/**
 * Procesa los formularios de la página de gestión de productos.
 */
function handle_product_requests($pdo) {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['action'])) return;

    switch ($_POST['action']) {
        case 'toggle_status':
            $id_producto_toggle = $_POST['id_producto'];
            $nuevo_estado = $_POST['current_status'] == 1 ? 0 : 1;
            $stmt = $pdo->prepare("UPDATE productos SET activo = ? WHERE id = ?");
            $stmt->execute([$nuevo_estado, $id_producto_toggle]);
            $_SESSION['message'] = "Estado del producto actualizado.";
            header("Location: gestionar_productos.php?id_producto=" . $id_producto_toggle . "#heading-" . $id_producto_toggle);
            exit();

        case 'save_product':
            $id_producto = $_POST['id_producto'];
            $nombre_archivo_imagen = $_POST['imagen_actual'] ?? '';
            if (isset($_FILES['imagen_principal']) && $_FILES['imagen_principal']['error'] == UPLOAD_ERR_OK) {
                $directorio_destino = '../imagenes/productos/';
                if (!file_exists($directorio_destino)) mkdir($directorio_destino, 0777, true);
                $nombre_archivo_imagen = uniqid('prod_') . '_' . basename($_FILES['imagen_principal']['name']);
                if (!move_uploaded_file($_FILES['imagen_principal']['tmp_name'], $directorio_destino . $nombre_archivo_imagen)) {
                    $_SESSION['error_message'] = "Hubo un error al subir la imagen.";
                    $nombre_archivo_imagen = $_POST['imagen_actual'] ?? '';
                }
            }
            if (empty($id_producto)) {
                $sql = "INSERT INTO productos (nombre, descripcion, categoriaID, activo, imagen_principal) VALUES (?, ?, ?, ?, ?)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$_POST['nombre'], $_POST['descripcion'], $_POST['categoriaID'], $_POST['activo'], $nombre_archivo_imagen]);
                $id_producto = $pdo->lastInsertId();
                $_SESSION['message'] = "Producto base creado exitosamente.";
            } else {
                $sql = "UPDATE productos SET nombre = ?, descripcion = ?, categoriaID = ?, activo = ?, imagen_principal = ? WHERE id = ?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$_POST['nombre'], $_POST['descripcion'], $_POST['categoriaID'], $_POST['activo'], $nombre_archivo_imagen, $id_producto]);
                $_SESSION['message'] = "Producto base actualizado exitosamente.";
            }
            header("Location: gestionar_productos.php?edit_product_id=" . $id_producto . "#product-editor");
            exit();

        case 'save_variant':
            $id_variante = $_POST['id_variante'];
            $id_producto_var = $_POST['id_producto'];
            $opciones = $_POST['opciones'] ?? [];
            $pdo->beginTransaction();
            try {
                if (empty($id_variante)) {
                    $sql = "INSERT INTO variantes_producto (id_producto, sku, precio, stock, descuento, destacado) VALUES (?, ?, ?, ?, ?, ?)";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([$id_producto_var, $_POST['sku'], $_POST['precio'], $_POST['stock'], $_POST['descuento'], $_POST['destacado']]);
                    $id_variante = $pdo->lastInsertId();
                    $_SESSION['message'] = 'Variante creada exitosamente.';
                } else {
                    $sql = "UPDATE variantes_producto SET sku = ?, precio = ?, stock = ?, descuento = ?, destacado = ? WHERE id_variante = ?";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([$_POST['sku'], $_POST['precio'], $_POST['stock'], $_POST['descuento'], $_POST['destacado'], $id_variante]);
                    $_SESSION['message'] = 'Variante actualizada exitosamente.';
                }
                $pdo->prepare("DELETE FROM variante_opcion WHERE id_variante = ?")->execute([$id_variante]);
                if (!empty($opciones)) {
                    $stmt_opcion = $pdo->prepare("INSERT INTO variante_opcion (id_variante, id_opcion) VALUES (?, ?)");
                    foreach ($opciones as $id_opcion) {
                        if (!empty($id_opcion)) $stmt_opcion->execute([$id_variante, $id_opcion]);
                    }
                }
                $pdo->commit();
            } catch (Exception $e) {
                $pdo->rollBack();
                $_SESSION['error_message'] = 'Error al guardar la variante: ' . $e->getMessage();
            }
            header("Location: gestionar_productos.php?id_producto=" . $id_producto_var . "#heading-" . $id_producto_var);
            exit();

        case 'delete_product':
            $pdo->prepare("DELETE FROM productos WHERE id = ?")->execute([$_POST['id_producto']]);
            $_SESSION['message'] = 'Producto y sus variantes eliminados.';
            header("Location: gestionar_productos.php");
            exit();
    }
}

/**
 * Procesa los formularios de la página de gestión de pedidos.
 */
function handle_pedidos_requests($pdo) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['actualizar_estado'])) {
        $pedido_id = $_POST['pedido_id'];
        $nuevo_estado = $_POST['estado'];
        $estados_descuento = ['En preparación', 'Enviado', 'Completado'];

        $pdo->beginTransaction();
        try {
            // Obtener el estado actual del pedido ANTES de actualizarlo
            $stmt_estado_actual = $pdo->prepare("SELECT estado FROM pedidos WHERE id_pedido = ?");
            $stmt_estado_actual->execute([$pedido_id]);
            $estado_actual = $stmt_estado_actual->fetchColumn();

            // Descontar stock solo si el estado NUEVO está en la lista de descuento
            // Y el estado ANTIGUO NO lo estaba. Esto evita dobles descuentos.
            if (in_array($nuevo_estado, $estados_descuento) && !in_array($estado_actual, $estados_descuento)) {
                
                // Obtener todos los items (productos) del pedido
                $stmt_items = $pdo->prepare("SELECT variante_id, cantidad FROM pedidos_items WHERE pedido_id = ?");
                $stmt_items->execute([$pedido_id]);
                $items_del_pedido = $stmt_items->fetchAll(PDO::FETCH_ASSOC);

                // Preparar la consulta para actualizar el stock
                $sql_update_stock = "UPDATE variantes_producto SET stock = stock - ? WHERE id_variante = ?";
                $stmt_update_stock = $pdo->prepare($sql_update_stock);

                foreach ($items_del_pedido as $item) {
                    // Asegurarse de que es un producto válido antes de descontar
                    if ($item['variante_id'] && $item['cantidad'] > 0) {
                        $stmt_update_stock->execute([$item['cantidad'], $item['variante_id']]);
                    }
                }
                $_SESSION['message'] = "Estado actualizado y stock de los productos descontado.";
            } else {
                 $_SESSION['message'] = "Estado del pedido actualizado (el stock no se modificó).";
            }
            
            // Actualizar el estado del pedido
            $stmt_update_pedido = $pdo->prepare("UPDATE pedidos SET estado = ? WHERE id_pedido = ?");
            $stmt_update_pedido->execute([$nuevo_estado, $pedido_id]);
            
            $pdo->commit();

        } catch (Exception $e) {
            $pdo->rollBack();
            $_SESSION['error_message'] = "Error al actualizar el pedido: " . $e->getMessage();
        }
        
        // Redirigir de vuelta a la página de pedidos
        header("Location: gestionar_pedidos.php");
        exit();
    }
}


/**
 * Procesa los formularios de la página de gestión de boxes.
 */
function handle_box_requests($pdo) {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;

    if (isset($_POST['save_box'])) {
        $id_promo = $_POST['id_promo'] ?? null;
        $items = isset($_POST['items']) ? $_POST['items'] : [];
        $valor_total_items = 0.00;
        
        // --- INICIO: LÓGICA PARA SUBIR IMAGEN DEL KIT ---
        $imagen_path = $_POST['imagen_actual'] ?? '';
        if (isset($_FILES['imagen_promo']) && $_FILES['imagen_promo']['error'] == UPLOAD_ERR_OK) {
            $directorio_destino = '../imagenes/promos/';
            if (!file_exists($directorio_destino)) {
                mkdir($directorio_destino, 0777, true);
            }
            // Crear un nombre de archivo único para evitar sobreescribir
            $nombre_archivo = uniqid('promo_') . '_' . basename($_FILES['imagen_promo']['name']);
            $ruta_completa = $directorio_destino . $nombre_archivo;
            
            if (move_uploaded_file($_FILES['imagen_promo']['tmp_name'], $ruta_completa)) {
                // Guardar la ruta relativa en la BD
                $imagen_path = 'imagenes/promos/' . $nombre_archivo;
            } else {
                $_SESSION['error_message'] = "Hubo un error al subir la imagen de la promoción.";
            }
        }
        // --- FIN: LÓGICA PARA SUBIR IMAGEN DEL KIT ---

        if (!empty($items)) {
            $placeholders = implode(',', array_fill(0, count(array_keys($items)), '?'));
            $stmt_precios = $pdo->prepare("SELECT id_variante, precio FROM variantes_producto WHERE id_variante IN ($placeholders)");
            $stmt_precios->execute(array_keys($items));
            $precios_variantes = $stmt_precios->fetchAll(PDO::FETCH_KEY_PAIR);
            foreach ($items as $id_variante => $cantidad) {
                if (isset($precios_variantes[$id_variante])) {
                    $valor_total_items += $precios_variantes[$id_variante] * $cantidad;
                }
            }
        }
        $pdo->beginTransaction();
        try {
            if (empty($id_promo)) {
                $sql = "INSERT INTO promociones (nombre_promo, descripcion_promo, imagen_promo, precio_promo, valor_total_items, fecha_termino, activa) VALUES (?, ?, ?, ?, ?, ?, ?)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$_POST['nombre_promo'], $_POST['descripcion_promo'], $imagen_path, $_POST['precio_promo'], $valor_total_items, !empty($_POST['fecha_termino']) ? $_POST['fecha_termino'] : null, $_POST['activa']]);
                $id_promo = $pdo->lastInsertId();
            } else {
                $sql = "UPDATE promociones SET nombre_promo = ?, descripcion_promo = ?, imagen_promo = ?, precio_promo = ?, valor_total_items = ?, fecha_termino = ?, activa = ? WHERE id_promo = ?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$_POST['nombre_promo'], $_POST['descripcion_promo'], $imagen_path, $_POST['precio_promo'], $valor_total_items, !empty($_POST['fecha_termino']) ? $_POST['fecha_termino'] : null, $_POST['activa'], $id_promo]);
                $pdo->prepare("DELETE FROM promocion_items WHERE id_promo = ?")->execute([$id_promo]);
            }

            if (!empty($items)) {
                $stmt_item = $pdo->prepare("INSERT INTO promocion_items (id_promo, id_variante, cantidad) VALUES (?, ?, ?)");
                foreach ($items as $id_variante => $cantidad) {
                    if (!empty($cantidad) && (int)$cantidad > 0) {
                        $stmt_item->execute([$id_promo, $id_variante, (int)$cantidad]);
                    }
                }
            }
            $pdo->commit();
            $_SESSION['message'] = "Box de promoción guardado exitosamente.";
        } catch (Exception $e) {
            $pdo->rollBack();
            $_SESSION['error_message'] = "Error al guardar el box: " . $e->getMessage();
        }
        header("Location: gestionar_box.php");
        exit();
    }

    if (isset($_POST['delete_box'])) {
        $pdo->prepare("DELETE FROM promociones WHERE id_promo = ?")->execute([$_POST['id_promo']]);
        $_SESSION['message'] = "Box de promoción #" . htmlspecialchars($_POST['id_promo']) . " ha sido eliminado.";
        header("Location: gestionar_box.php");
        exit();
    }
}


// ==================================================================
// FUNCIONES DE OBTENCIÓN DE DATOS (PARA VISTAS)
// ==================================================================

function get_dashboard_stats($pdo) {
    return [
        'ingresos_totales' => $pdo->query("SELECT SUM(monto_total) FROM pedidos")->fetchColumn(),
        'total_pedidos'    => $pdo->query("SELECT COUNT(id_pedido) FROM pedidos")->fetchColumn(),
        'total_usuarios'   => $pdo->query("SELECT COUNT(id) FROM usuarios")->fetchColumn(),
        'ticket_promedio'  => $pdo->query("SELECT AVG(monto_total) FROM pedidos")->fetchColumn(),
        'ultimo_usuario'   => $pdo->query("SELECT nombre_usuario FROM usuarios ORDER BY fecha_registro DESC LIMIT 1")->fetchColumn()
    ];
}

function get_all_products_with_category($pdo) {
    return $pdo->query("SELECT p.*, c.nombre_categoria FROM productos p LEFT JOIN categorias c ON p.categoriaID = c.id_categoria ORDER BY p.nombre")->fetchAll(PDO::FETCH_ASSOC);
}

function get_all_categories($pdo) {
    return $pdo->query("SELECT * FROM categorias ORDER BY nombre_categoria")->fetchAll(PDO::FETCH_ASSOC);
}

function get_attributes_with_options($pdo) {
    $atributos_query = $pdo->query("SELECT a.id_atributo, a.nombre, o.id_opcion, o.valor FROM atributos a JOIN opciones o ON a.id_atributo = o.id_atributo ORDER BY a.nombre, o.valor");
    $atributos_con_opciones = [];
    foreach ($atributos_query as $row) {
        $atributos_con_opciones[$row['id_atributo']]['nombre'] = $row['nombre'];
        $atributos_con_opciones[$row['id_atributo']]['opciones'][] = ['id_opcion' => $row['id_opcion'], 'valor' => $row['valor']];
    }
    return $atributos_con_opciones;
}

function get_product_to_edit($pdo, $product_id) {
    if (!$product_id) return null;
    $stmt_edit = $pdo->prepare("SELECT * FROM productos WHERE id = ?");
    $stmt_edit->execute([$product_id]);
    return $stmt_edit->fetch(PDO::FETCH_ASSOC);
}

function get_all_pedidos_with_details($pdo) {
    $pedidos = $pdo->query("SELECT p.id_pedido, p.monto_total, p.estado, p.fecha_pedido, u.nombre_usuario, u.direccion_usuario FROM pedidos p JOIN usuarios u ON p.usuario_id = u.id ORDER BY p.fecha_pedido DESC")->fetchAll(PDO::FETCH_ASSOC);
    foreach ($pedidos as $key => $pedido) {
        $stmt_items = $pdo->prepare("SELECT pi.cantidad, pi.precio_unitario, v.sku, prod.nombre as nombre_producto FROM pedidos_items pi JOIN variantes_producto v ON pi.variante_id = v.id_variante JOIN productos prod ON v.id_producto = prod.id WHERE pi.pedido_id = ?");
        $stmt_items->execute([$pedido['id_pedido']]);
        $pedidos[$key]['items'] = $stmt_items->fetchAll(PDO::FETCH_ASSOC);
    }
    return $pedidos;
}

function get_all_boxes_with_items($pdo) {
    $boxes = $pdo->query("SELECT * FROM promociones ORDER BY id_promo DESC")->fetchAll(PDO::FETCH_ASSOC);
    foreach ($boxes as $key => $box) {
        $stmt_items = $pdo->prepare("SELECT pi.cantidad, p.nombre, v.sku FROM promocion_items pi JOIN variantes_producto v ON pi.id_variante = v.id_variante JOIN productos p ON v.id_producto = p.id WHERE pi.id_promo = ?");
        $stmt_items->execute([$box['id_promo']]);
        $boxes[$key]['items'] = $stmt_items->fetchAll(PDO::FETCH_ASSOC);
    }
    return $boxes;
}

function get_box_to_edit($pdo, $box_id) {
    if (!$box_id) return null;
    $stmt = $pdo->prepare("SELECT * FROM promociones WHERE id_promo = ?");
    $stmt->execute([$box_id]);
    $box_a_editar = $stmt->fetch();
    if ($box_a_editar) {
        $stmt_items = $pdo->prepare("SELECT id_variante, cantidad FROM promocion_items WHERE id_promo = ?");
        $stmt_items->execute([$box_id]);
        $box_a_editar['items'] = $stmt_items->fetchAll(PDO::FETCH_KEY_PAIR);
    }
    return $box_a_editar;
}

function get_available_variants($pdo) {
     return $pdo->query("SELECT v.id_variante, v.precio, p.nombre, v.sku FROM variantes_producto v JOIN productos p ON v.id_producto = p.id WHERE v.stock > 0 ORDER BY p.nombre")->fetchAll(PDO::FETCH_ASSOC);
}
function get_best_selling_products($pdo) {
    $sql = "
        SELECT 
            p.nombre,
            SUM(pi.cantidad) as total_vendido
        FROM pedidos_items pi
        JOIN variantes_producto v ON pi.variante_id = v.id_variante
        JOIN productos p ON v.id_producto = p.id
        JOIN pedidos ped ON pi.pedido_id = ped.id_pedido
        WHERE ped.estado IN ('En preparación', 'Enviado', 'Completado')
        GROUP BY p.id, p.nombre
        ORDER BY total_vendido DESC
    ";
    return $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Obtiene el cliente con más pedidos realizados.
 */
function get_top_customer($pdo) {
    $sql = "
        SELECT 
            u.nombre_usuario,
            COUNT(p.id_pedido) as total_pedidos
        FROM pedidos p
        JOIN usuarios u ON p.usuario_id = u.id
        GROUP BY u.id, u.nombre_usuario
        ORDER BY total_pedidos DESC
        LIMIT 1
    ";
    return $pdo->query($sql)->fetch(PDO::FETCH_ASSOC);
}

/**
 * Obtiene los pares de productos comprados juntos con más frecuencia.
 */
function get_frequently_bought_together($pdo) {
    $sql = "
        SELECT
            p1.nombre AS producto1,
            p2.nombre AS producto2,
            COUNT(*) AS veces_juntos
        FROM pedidos_items pi1
        JOIN pedidos_items pi2 ON pi1.pedido_id = pi2.pedido_id AND pi1.variante_id < pi2.variante_id
        JOIN variantes_producto v1 ON pi1.variante_id = v1.id_variante
        JOIN productos p1 ON v1.id_producto = p1.id
        JOIN variantes_producto v2 ON pi2.variante_id = v2.id_variante
        JOIN productos p2 ON v2.id_producto = p2.id
        GROUP BY producto1, producto2
        ORDER BY veces_juntos DESC
    ";
    return $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
}
function get_top_earning_categories($pdo) {
    $sql = "
        SELECT 
            c.nombre_categoria,
            SUM(pi.cantidad * pi.precio_unitario) as total_ingresos
        FROM pedidos_items pi
        JOIN variantes_producto v ON pi.variante_id = v.id_variante
        JOIN productos p ON v.id_producto = p.id
        JOIN categorias c ON p.categoriaID = c.id_categoria
        JOIN pedidos ped ON pi.pedido_id = ped.id_pedido
        WHERE ped.estado IN ('En preparación', 'Enviado', 'Completado')
        GROUP BY c.id_categoria, c.nombre_categoria
        ORDER BY total_ingresos DESC
    ";
    return $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
}
function get_low_stock_products($pdo, $threshold = 10) {
    $sql = "
        SELECT 
            p.nombre,
            v.sku,
            v.stock
        FROM variantes_producto v
        JOIN productos p ON v.id_producto = p.id
        WHERE v.stock <= ? AND v.stock > 0
        ORDER BY v.stock ASC
        
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$threshold]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Obtiene todos los proveedores.
 */
function get_all_suppliers($pdo) {
    // Usamos un try-catch por si la tabla aún no existe
    try {
        return $pdo->query("SELECT * FROM proveedores ORDER BY nombre ASC")->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        return []; // Devuelve un array vacío si la tabla no existe
    }
}
?>