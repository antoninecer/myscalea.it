<?php
require_once '../inc/connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $id = $_POST['id'];
    $name = $_POST['name'];
    $category_id = $_POST['category_id'];
    $description = $_POST['description'];
    $latitude = $_POST['latitude'];
    $longitude = $_POST['longitude'];
    $website = $_POST['website'];
    $phone = $_POST['phone'];
    $email = $_POST['email'];
    $address = $_POST['address'] ?? '';
    $opening_hours = $_POST['opening_hours'];

    // Příprava SQL
    $sql = "UPDATE places SET 
                name = :name,
                category_id = :category_id,
                description = :description,
                latitude = :latitude,
                longitude = :longitude,
                website = :website,
                phone = :phone,
                email = :email,
                address = :address,
                opening_hours = :opening_hours
            WHERE id = :id";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':id' => $id,
        ':name' => $name,
        ':category_id' => $category_id,
        ':description' => $description,
        ':latitude' => $latitude,
        ':longitude' => $longitude,
        ':website' => $website,
        ':phone' => $phone,
        ':email' => $email,
        ':address' => $address,
        ':opening_hours' => $opening_hours,
    ]);

    // Uložení obrázku, pokud byl přiložen
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
        $photoTmp = $_FILES['photo']['tmp_name'];
        $photoName = basename($_FILES['photo']['name']);
        $photoPath = 'uploads/' . time() . '_' . $photoName;
        if (!is_dir('uploads')) mkdir('uploads');
        move_uploaded_file($photoTmp, $photoPath);

        // Uložit cestu k obrázku (pokud máš ve struktuře sloupec např. `image`)
        $pdo->prepare("UPDATE places SET image = :image WHERE id = :id")
            ->execute([':image' => $photoPath, ':id' => $id]);
    }

    header("Location: ./");
    exit;
}
?>

