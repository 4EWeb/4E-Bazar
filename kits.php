<?php
// Este archivo se encargará de obtener y procesar los kits desde la BD.
// No necesita `require __DIR__ . '/db.php';` aquí si ya lo tienes en index.php antes de llamar este archivo.

$kits_procesados = [];

try {
    // 1. Obtenemos todas las promociones activas
    $sql_promos = "SELECT * FROM promociones WHERE activa = 1 AND (fecha_termino IS NULL OR fecha_termino > NOW())";
    $stmt_promos = $pdo->query($sql_promos);
    $promociones = $stmt_promos->fetchAll(PDO::FETCH_ASSOC);

    // 2. Para cada promoción, obtenemos sus items
    $sql_items = "
        SELECT 
            pi.id_promo,
            pi.cantidad, 
            p.nombre AS nombre_producto,
            v.sku
        FROM promocion_items pi
        JOIN variantes_producto v ON pi.id_variante = v.id_variante
        JOIN productos p ON v.id_producto = p.id
        WHERE pi.id_promo = ?
    ";
    $stmt_items = $pdo->prepare($sql_items);

    foreach ($promociones as $promo) {
        $stmt_items->execute([$promo['id_promo']]);
        $items_del_kit = $stmt_items->fetchAll(PDO::FETCH_ASSOC);
        
        // Añadimos la promoción y sus items al array final
        $kits_procesados[] = [
            'id_promo' => $promo['id_promo'],
            'nombre' => $promo['nombre_promo'],
            'imagen_promo' => $promo['imagen_promo'], // <--- CAMBIO AQUÍ
            'precio_total' => $promo['precio_promo'],
            'valor_real' => $promo['valor_total_items'],
            'nombres_productos' => $items_del_kit
        ];
    }

} catch (Exception $e) {
    // Si hay un error, el array de kits simplemente quedará vacío y no se mostrará nada.
    // Puedes agregar un mensaje de error si lo deseas.
    // echo "Error al cargar los kits de ahorro: " . $e->getMessage();
}